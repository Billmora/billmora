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
    public bool $searched = false;
    public bool $available = false;
    public ?float $checkPrice = null;
    
    public ?Tld $tld = null;
    public ?TldPrice $tldPrice = null;
    public string $domainName = '';
    public string $currencyCode = '';
    public array $suggestions = [];
    public array $alternativeNames = [];
    public bool $loadingSuggestions = false;

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
        ]);

        $this->searched = true;
        
        $this->domainName = strtolower(trim($this->domain));
        $dotPos = strpos($this->domainName, '.');
        $tldString = ltrim(substr($this->domainName, $dotPos), '.');
        
        $this->tld = Tld::where('tld', $tldString)->where('status', 'visible')->first();
        $this->suggestions = [];
        
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
                // Do not add error to 'domain' so the result box can be shown
                $this->resetErrorBag('domain');
            }
        } else {
            $this->available = true;
        }

        $this->resetErrorBag('domain');
        $this->loadingSuggestions = true;
    }

    public function loadSuggestions()
    {
        if (!$this->loadingSuggestions) return;
        $this->generateSuggestions();
        $this->loadingSuggestions = false;
    }

    protected function generateSuggestions()
    {
        $this->suggestions = [];
        $this->alternativeNames = [];
        
        if ($this->type !== 'register') return;

        $dotPos = strpos($this->domainName, '.');
        if ($dotPos === false) return;
        
        $sld = substr($this->domainName, 0, $dotPos);
        $currentTld = ltrim(substr($this->domainName, $dotPos), '.');

        // 1. Alternative Names (same TLD)
        $suffixes = ['online', 'app', 'hq', 'store', 'shop', 'site', 'web', 'tech', 'digital', 'hub'];
        $prefixes = ['get', 'the', 'my', 'go', 'we', 'hello', 'try'];
        
        $variations = [];
        $selectedPrefixes = (array) array_rand(array_flip($prefixes), 2);
        foreach($selectedPrefixes as $prefix) {
            $variations[] = $prefix . $sld;
        }
        $selectedSuffixes = (array) array_rand(array_flip($suffixes), 3);
        foreach($selectedSuffixes as $suffix) {
            $variations[] = $sld . $suffix;
        }

        try {
            [$plugin, $config] = $this->registrarService->bootPluginForTld($this->tld);
            foreach ($variations as $varSld) {
                $altDomain = $varSld . '.' . $currentTld;
                $result = $plugin->checkAvailability($altDomain);
                if (isset($result['available']) && $result['available']) {
                    $this->alternativeNames[] = [
                        'domain' => $altDomain,
                        'price' => $result['price'] ?? ($this->tldPrice->register_price * $this->tld->min_years),
                        'min_years' => $this->tld->min_years,
                        'premium' => isset($result['price']),
                    ];
                }
            }
        } catch (Exception $e) {
            // Ignore if plugin fails
        }

        // 2. Alternative TLDs
        $alternativeTlds = Tld::where('status', 'visible')
            ->where('tld', '!=', $currentTld)
            ->with(['prices' => function($q) {
                $q->where('currency', $this->currencyCode);
            }])
            ->inRandomOrder()
            ->take(5)
            ->get();

        foreach ($alternativeTlds as $altTld) {
            $priceModel = $altTld->prices->first();
            if (!$priceModel) continue;

            $altDomain = $sld . '.' . $altTld->tld;
            
            try {
                [$altPlugin, $altConfig] = $this->registrarService->bootPluginForTld($altTld);
                $result = $altPlugin->checkAvailability($altDomain);
                
                if (isset($result['available']) && $result['available']) {
                    $this->suggestions[] = [
                        'domain' => $altDomain,
                        'price' => $result['price'] ?? ($priceModel->register_price * $altTld->min_years),
                        'min_years' => $altTld->min_years,
                        'premium' => isset($result['price']),
                    ];
                }
            } catch (Exception $e) {
                // Ignore if plugin fails for this TLD
            }
        }
    }

    public function searchSuggestion(string $domain)
    {
        $this->domain = $domain;
        $this->search();
    }



    public function render()
    {
        return view('client::livewire.store.domain-search');
    }
}
