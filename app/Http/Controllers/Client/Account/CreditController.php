<?php

namespace App\Http\Controllers\Client\Account;

use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Plugin;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreditController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing client credits.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ((bool) Billmora::getGeneral('credit_use') === false) {
                return abort(403);
            }

            return $next($request);
        })->only(['index', 'deposit']);
    }

    /**
     * Display the credit wallet page with available balances and active payment gateways.
     *
     * @param  \App\Services\PluginManager  $pluginManager
     * @return \Illuminate\View\View
     */
    public function index(PluginManager $pluginManager)
    {
        $user = Auth::user();

        $credits = $user->credits;

        $activeGateways = Plugin::where('type', 'gateway')->where('is_active', true)->get();
        $gateways = collect();

        foreach ($activeGateways as $gatewayRecord) {
            $instance = $pluginManager->bootInstance($gatewayRecord);
            
            if ($instance instanceof GatewayInterface) {
                $gateways->push($gatewayRecord);
            }
        }

        return view('client::account.credit', compact('credits', 'gateways'));
    }

    /**
     * Validate and create a top-up invoice for adding funds to the user's credit wallet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deposit(Request $request)
    {
        $currencyCode = $request->credit_currency;
        $currency = Currency::where('code', $currencyCode)->first();
        $baseRate = $currency ? (float) $currency->base_rate : 1;

        $minDeposit = (float) Billmora::getGeneral('credit_min_deposit') * $baseRate;
        $maxDeposit = (float) Billmora::getGeneral('credit_max_deposit') * $baseRate;
        $maxBalance = (float) Billmora::getGeneral('credit_max') * $baseRate;

        $validated = $request->validate([
            'credit_currency' => ['required', Rule::exists('currencies', 'code')],
            'credit_amount' => ['required', 'numeric', "min:{$minDeposit}", "max:{$maxDeposit}"],
            'credit_payment_method' => [
                'required', 
                Rule::exists('plugins', 'id')->where('type', 'gateway')->where('is_active', true)
            ],
        ]);

        $user = Auth::user();
        $wallet = $user->getCreditWallet($currencyCode);

        if (($wallet->balance + $validated['credit_amount']) > $maxBalance) {
            return back()
                ->withInput()
                ->with('error', __('client/account.credits.deposit_exceeds_max_balance', [
                    'max_balance' => \Currency::format($maxBalance, $currencyCode),
                ]));
        }

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'order_id' => null,
            'plugin_id' => $validated['credit_payment_method'],
            'status' => 'unpaid',
            'currency' => $validated['credit_currency'],
            'subtotal' => $validated['credit_amount'],
            'discount' => 0,
            'setup_fee' => 0,
            'total' => $validated['credit_amount'],
            'due_date' => now(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_id' => null,
            'description' => "Credit Deposit - {$currencyCode}", // identifier for credit topup, don't change it!
            'quantity' => 1,
            'unit_price' => $validated['credit_amount'],
            'amount' => $validated['credit_amount'],
        ]);

        $this->recordCreate('invoice.created', $invoice->toArray());

        return redirect()->route('client.invoices.pay', ['invoice' => $invoice->invoice_number]);
    }
}
