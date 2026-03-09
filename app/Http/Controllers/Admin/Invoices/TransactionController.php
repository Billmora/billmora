<?php

namespace App\Http\Controllers\Admin\Invoices;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing invoice transaction.
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
     * Display a paginated list of transactions for the specified invoice.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, Invoice $invoice)
    {
        $query = Transaction::with(['user', 'invoice', 'plugin'])->where('invoice_id', $invoice->id);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('plugin', function ($pluginQuery) use ($search) {
                    $pluginQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $transactions = $query->latest()->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::invoices.transaction.index', compact('invoice', 'transactions'));
    }

    /**
     * Show the form for creating a new manual transaction for the specified invoice.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Invoice $invoice)
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();
        $plugins = Plugin::select('id', 'name', 'type', 'provider')->where('type', 'gateway')->where('is_active', true)->get();

        return view('admin::invoices.transaction.create', compact('invoice', 'users', 'plugins'));
    }

    /**
     * Store a newly created manual transaction for the specified invoice in database.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'transaction_user' => ['required', Rule::exists('users', 'id')],
            'transaction_gateway' => ['nullable', Rule::exists('plugins', 'id')->where(fn ($query) => $query->where('type', 'gateway'))],
            'transaction_reference' => ['nullable', 'string'],
            'transaction_amount' => ['required', 'numeric'],
            'transaction_fee' => ['required', 'numeric'],
            'transaction_description' => ['required', 'string', 'max:1000'],
        ]);

        $transaction = Transaction::create([
            'user_id' => $validated['transaction_user'],
            'invoice_id' => $invoice->id,
            'plugin_id' => $validated['transaction_gateway'],
            'reference' => $validated['transaction_reference'],
            'currency' => $invoice->currency,
            'amount' => $validated['transaction_amount'],
            'fee' => $validated['transaction_fee'],
            'description' => $validated['transaction_description'],
        ]);

        $this->recordCreate('invoice.transaction.create', $transaction->toArray());

        return redirect()->route('admin.invoices.transaction', ['invoice' => $invoice->invoice_number])
            ->with('success', __('common.create_success', ['attribute' => __('admin/navigation.transactions')]));
    }

    /**
     * Remove the specified transaction from the invoice in database.
     *
     * @param \App\Models\Invoice $invoice
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Invoice $invoice, Transaction $transaction)
    {
        $transaction->delete();

        $this->recordDelete('invoice.transaction.delete', $transaction->toArray());

        return redirect()->route('admin.invoices.edit', ['invoice' => $invoice->invoice_number])
            ->with('success', __('common.delete_success', ['attribute' => $transaction->reference ?? $transaction->id]));
    }
}
