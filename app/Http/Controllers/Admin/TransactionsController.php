<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Applies permission-based middleware for accessing transactions management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:transactions.view')->only(['index']);
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

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('plugin', function ($pluginQuery) use ($search) {
                    $pluginQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $transactions = $query->latest()->paginate(25);

        return view('admin::transactions.index', compact('transactions'));
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

        return redirect()->back()->with('success', __('common.delete_success', ['attribute' => $transaction->transaction_id]));
    }
}
