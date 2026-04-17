<?php

namespace App\Livewire\Client\Store;

use App\Models\Tld;
use App\Models\TldPrice;
use App\Services\Checkout\CartService;
use App\Services\RegistrarService;
use App\Facades\Currency;
use Billmora;
use Exception;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DomainSearch extends Component
{
    public string $domain = '';
    public string $type = 'register'; 
    public ?string $eppCode = null;
    
    public bool $searched = false;
    public bool $available = false;
    public ?float $checkPrice = null;
    public ?int $selectedYears = 1;
    
    public ?Tld $tld = null;
    public ?TldPrice $tldPrice = null;
    public string $domainName = '';
    public string $currencyCode = '';
    
    public array $yearOptions = [];

    protected RegistrarService $registrarService;
    protected CartService $cartService;

    public function boot(RegistrarService $registrarService, CartService $cartService)
    {
        $this->registrarService = $registrarService;
        $this->cartService = $cartService;
        $this->currencyCode = Session::get('currency', 'USD');
    }

    public function mount()
    {
        $registrationEnabled = (bool) Billmora::getGeneral('domain_registration_enabled');
        $transferEnabled = (bool) Billmora::getGeneral('domain_transfer_enabled');

        if (!$registrationEnabled && !$transferEnabled) {
            return;
        }

        if (!$registrationEnabled && $transferEnabled) {
            $this->type = 'transfer';
        } else {
            $this->type = 'register';
        }
    }

    public function setType($type)
    {
        $registrationEnabled = (bool) Billmora::getGeneral('domain_registration_enabled');
        $transferEnabled = (bool) Billmora::getGeneral('domain_transfer_enabled');

        if ($type === 'register' && !$registrationEnabled) return;
        if ($type === 'transfer' && !$transferEnabled) return;

        $this->type = $type;
        $this->searched = false;
    }

    public function search()
    {
        $this->validate([
            'domain' => 'required|string|regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]\.[a-z]+(\.[a-z]+)?$/i',
            'eppCode' => 'nullable|string|required_if:type,transfer',
        ]);

        $this->searched = true;
        
        $this->domainName = strtolower(trim($this->domain));
        $dotPos = strpos($this->domainName, '.');
        $tldString = ltrim(substr($this->domainName, $dotPos), '.');
        
        $this->tld = Tld::where('tld', $tldString)->where('status', 'visible')->first();
        
        if (!$this->tld) {
            $this->addError('domain', __('client/store.domain_unavailable'));
            $this->available = false;
            return;
        }

        $this->tldPrice = TldPrice::where('tld_id', $this->tld->id)
            ->where('currency', $this->currencyCode)
            ->first();
            
        if (!$this->tldPrice) {
            $this->addError('domain', __('client/store.unavailable_currency'));
            $this->available = false;
            return;
        }

        if ($this->type === 'register') {
            try {
                [$plugin, $config] = $this->registrarService->bootPluginForTld($this->tld);
                $result = $plugin->checkAvailability($this->domainName);
                
                $this->available = $result['available'] ?? false;
                $this->checkPrice = $result['price'] ?? null;
            } catch (Exception $e) {
                $this->available = false;
                $this->addError('domain', $e->getMessage());
            }
            
            if (!$this->available) {
                $this->addError('domain', __('client/store.domain_unavailable'));
            }
        } else {
            $this->available = true;
        }

        if ($this->available) {
            $this->buildYearOptions();
            $this->resetErrorBag('domain');
        }
    }

    protected function buildYearOptions()
    {
        $this->yearOptions = [];
        $min = $this->tld->min_years;
        $max = $this->tld->max_years;
        $price = $this->type === 'register' ? $this->tldPrice->register_price : $this->tldPrice->transfer_price;
        
        for ($i = $min; $i <= $max; $i++) {
            $this->yearOptions[$i] = [
                'years' => $i,
                'price' => $price * $i,
                'label' => $i . ' Year' . ($i > 1 ? 's' : '') . ' - ' . Currency::format($price * $i, $this->currencyCode),
            ];
        }
        $this->selectedYears = $min;
    }

    public function addToCart()
    {
        if (!$this->available || !$this->tld || !$this->tldPrice) {
            return;
        }
        
        if ($this->type === 'register') {
            try {
                [$plugin, $config] = $this->registrarService->bootPluginForTld($this->tld);
                $result = $plugin->checkAvailability($this->domainName);
                if (!isset($result['available']) || !$result['available']) {
                    $this->available = false;
                    $this->addError('domain', __('client/store.domain_unavailable'));
                    return;
                }
            } catch (Exception $e) {
                $this->available = false;
                $this->addError('domain', $e->getMessage());
                return;
            }
        }
        
        $price = $this->type === 'register' ? $this->tldPrice->register_price : $this->tldPrice->transfer_price;
        $totalPrice = $price * $this->selectedYears;

        $this->cartService->addDomain(
            $this->tld,
            $this->domainName,
            $this->type,
            $this->selectedYears,
            $totalPrice,
            $this->eppCode
        );

        return redirect()->route('client.checkout.cart')
            ->with('success', __('client/checkout.cart.item_added'));
    }

    public function render()
    {
        return view('client::livewire.store.domain-search');
    }
}
