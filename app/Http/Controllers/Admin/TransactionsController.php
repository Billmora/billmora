<?php

namespace App\Http\Controllers\Admin;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing transactions management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:transactions.view')->only(['index']);
        $this->middleware('permission:transactions.create')->only(['create', 'store']);
        $this->middleware('permission:transactions.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of all transactions.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'invoice', 'plugin']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('plugin', function ($pluginQuery) use ($search) {
                    $pluginQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $transactions = $query->latest()->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new manual transaction.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $userOptions = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => $user->fullname,
                'subtitle' => $user->email,
            ])
            ->values()
            ->toArray();

        $invoiceOptions = Invoice::select('id', 'invoice_number', 'currency')
            ->get()
            ->map(fn ($invoice) => [
                'value' => $invoice->id,
                'title' => $invoice->invoice_number,
                'subtitle' => $invoice->currency,
            ])
            ->values()
            ->toArray();

        $pluginOptions = Plugin::select('id', 'name', 'type', 'provider')
            ->where('type', 'gateway')
            ->where('is_active', true)
            ->get()
            ->map(fn ($plugin) => [
                'value' => $plugin->id,
                'title' => $plugin->name,
                'subtitle' => $plugin->provider,
            ])
            ->values()
            ->toArray();


        return view('admin::transactions.create', compact('userOptions', 'invoiceOptions', 'pluginOptions'));
    }

    /**
     * Store a newly created manual transaction record in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_user' => ['required', Rule::exists('users', 'id')],
            'transaction_invoice' => ['required', Rule::exists('invoices', 'id')],
            'transaction_gateway' => ['nullable', Rule::exists('plugins', 'id')->where(fn ($query) => $query->where('type', 'gateway'))],
            'transaction_reference' => ['nullable', 'string'],
            'transaction_amount' => ['required', 'numeric'],
            'transaction_fee' => ['required', 'numeric'],
            'transaction_description' => ['required', 'string', 'max:1000'],
        ]);

        $invoice = Invoice::where('id', $validated['transaction_invoice'])->first();

        $transaction = Transaction::create([
            'user_id' => $validated['transaction_user'],
            'invoice_id' => $validated['transaction_invoice'],
            'plugin_id' => $validated['transaction_gateway'],
            'reference' => $validated['transaction_reference'],
            'currency' => $invoice->currency,
            'amount' => $validated['transaction_amount'],
            'fee' => $validated['transaction_fee'],
            'description' => $validated['transaction_description'],
        ]);

        $this->recordCreate('transaction.create', $transaction->toArray());

        return redirect()->route('admin.transactions')->with('success', __('common.create_success', ['attribute' => __('admin/navigation.transactions')]));
    }

    /**
     * Remove the specified transaction from database.
     *
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        $this->recordDelete('transaction.delete', $transaction->toArray());

        return redirect()->route('admin.transactions')->with('success', __('common.delete_success', ['attribute' => $transaction->reference]));
    }
}
