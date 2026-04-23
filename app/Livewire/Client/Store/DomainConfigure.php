<?php

namespace App\Livewire\Client\Store;

use App\Models\Tld;
use App\Models\TldPrice;
use App\Services\Checkout\CartService;
use App\Facades\Currency;
use Billmora;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DomainConfigure extends Component
{
    public string $domainName = '';
    public string $type = 'register';
    
    public ?Tld $tld = null;
    public ?TldPrice $tldPrice = null;
    public string $currencyCode = '';
    
    public ?string $selectedYears = null;
    public array $yearOptions = [];
    
    public ?string $eppCode = null;
    public array $nameservers = [];

    protected CartService $cartService;

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
        $this->currencyCode = Session::get('currency', 'USD');
    }

    public function mount(string $domainName, string $type)
    {
        $this->domainName = strtolower(trim($domainName));
        $this->type = in_array($type, ['register', 'transfer']) ? $type : 'register';
        
        $dotPos = strpos($this->domainName, '.');
        if ($dotPos === false) {
            abort(404);
        }
        
        $tldString = ltrim(substr($this->domainName, $dotPos), '.');
        $this->tld = Tld::where('tld', $tldString)->where('status', 'visible')->firstOrFail();
        
        $this->tldPrice = TldPrice::where('tld_id', $this->tld->id)
            ->where('currency', $this->currencyCode)
            ->firstOrFail();
            
        $this->buildYearOptions();
        
        // Load default nameservers from general settings
        for ($i = 1; $i <= 5; $i++) {
            $this->nameservers[$i - 1] = Billmora::getGeneral("domain_nameserver_$i") ?? '';
        }
    }

    protected function buildYearOptions()
    {
        $this->yearOptions = [];
        $min = $this->tld->min_years;
        $max = $this->tld->max_years;
        $price = $this->type === 'register' ? $this->tldPrice->register_price : $this->tldPrice->transfer_price;
        
        for ($i = $min; $i <= $max; $i++) {
            $this->yearOptions[(string)$i] = [
                'years' => (string)$i,
                'price' => $price * $i,
                'label' => $i . ' Year' . ($i > 1 ? 's' : '') . ' - ' . Currency::format($price * $i, $this->currencyCode),
            ];
        }
        
        if ($this->selectedYears === null || !array_key_exists($this->selectedYears, $this->yearOptions)) {
            $this->selectedYears = (string)$min;
        }
    }

    public function addToCart()
    {
        $this->validate([
            'selectedYears' => 'required|integer|min:' . $this->tld->min_years . '|max:' . $this->tld->max_years,
            'eppCode' => 'nullable|string|required_if:type,transfer',
            'nameservers' => 'array',
            'nameservers.*' => 'nullable|string|max:255',
        ]);
        
        $price = $this->type === 'register' ? $this->tldPrice->register_price : $this->tldPrice->transfer_price;
        $totalPrice = $price * $this->selectedYears;

        // Filter out empty nameservers
        $filteredNameservers = array_values(array_filter($this->nameservers, function($ns) {
            return !empty(trim($ns));
        }));

        $this->cartService->addDomain(
            $this->tld,
            $this->domainName,
            $this->type,
            $this->selectedYears,
            $totalPrice,
            $this->eppCode,
            $filteredNameservers
        );

        return redirect()->route('client.checkout.cart')
            ->with('success', __('client/checkout.cart.item_added'));
    }

    public function render()
    {
        return view('client::livewire.store.domain-configure');
    }
}
