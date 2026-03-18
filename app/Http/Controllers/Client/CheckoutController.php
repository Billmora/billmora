<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\CaptchaService;
use App\Services\Checkout\CartService;
use App\Services\Package\Client\OrderRedirectService;
use App\Services\Package\Client\OrderService;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(protected  CartService  $cartService)
    {
        // 
    }

    /**
     * Validate the checkout form, process the order, and redirect based on payment flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Package\Client\OrderService  $orderService
     * @param  \App\Services\Package\Client\OrderRedirectService  $redirectService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request, OrderService $orderService, OrderRedirectService $redirectService)
    {
        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return redirect()
                ->route('client.store')
                ->with('error', __('client/checkout.cart.empty'));
        }

        $rules = [
            'payment_method' => [
                'required', 
                Rule::exists('plugins', 'id')
                    ->where('type', 'gateway')
                    ->where('is_active', true)
            ],
        ];
        
        if (Billmora::getGeneral('ordering_notes')) {
            $rules['notes'] = ['nullable', 'string', 'max:1000'];
        }
        if (Billmora::getGeneral('ordering_tos')) {
            $rules['terms_accepted'] = ['required', 'accepted'];
        }

        $validated = $request->validate($rules);

        CaptchaService::verifyOrFail('checkout_form', $request);

        if (!Auth::check()) {
            session()->put('url.intended', route('client.checkout.cart'));
            return redirect()->route('client.login');
        }

        try {
            $country = Auth::check() ? Auth::user()->billing?->country : null;
            $totals = $this->cartService->getTotals($country);
            $appliedCoupon = Session::get('applied_coupon');

            $result = $orderService->createOrder(
                Auth::id(),
                $cartItems,
                $totals,
                $appliedCoupon,
                $validated
            );

            $this->cartService->clear();

            return $redirectService->handle($result['order'], $result['invoice']);
            
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the order completion page after a successful checkout.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function complete()
    {
        if (!Session::has('completed_order_data')) {
            return redirect()->route('client.store')->with('error', __('client/checkout.session.missing_data'));
        }

        $sessionData = Session::pull('completed_order_data');

        $order = Order::with('items')->findOrfail($sessionData['order_id']);
        $invoice = Invoice::findOrfail($sessionData['invoice_id']);

        return view('client::checkout.complete', compact('order', 'invoice'));
    }
}