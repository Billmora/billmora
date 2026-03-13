<?php

namespace App\Http\Controllers\Client;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\VariantOption;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicesController extends Controller
{
    /**
     * Display a paginated list of user's services.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $services = Service::where('user_id', Auth::id())
            ->with([
                'package.catalog',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(Billmora::getGeneral('misc_client_pagination'));

        return view('client::services.index', compact('services'));
    }

    /**
     * Display the specified service details with variant options and available client actions.
     *
     * @param \App\Models\Service $service
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(Service $service, PluginManager $manager)
    {
        if ($service->user_id !== Auth::id()) {
            abort(404);
        }

        $service->load([
            'package.catalog', 
            'packagePrice', 
            'provisioning',
            'order',
            'unpaidInvoice',
        ]);

        $unpaidInvoice = $service->unpaidInvoice->first();

        $variantOptions = collect();
        
        if (!empty($service->variant_selections)) {
            $optionIds = collect($service->variant_selections)->flatten()->filter();
            
            if ($optionIds->isNotEmpty()) {
                $variantOptions = VariantOption::with('variant')
                    ->whereIn('id', $optionIds)
                    ->get();
            }
        }

        $clientActions = [];
        $checkoutData = [];

        if ($service->provisioning) {
            $plugin = $manager->bootInstance($service->provisioning);
            
            if ($plugin) {
                if ($service->status === 'active' && method_exists($plugin, 'getClientAction')) {
                    $clientActions = $plugin->getClientAction($service);
                }

                if (method_exists($plugin, 'getCheckoutSchema')) {
                    $schema = $plugin->getCheckoutSchema();
                    
                    $schemaFields = isset($schema['fields']) && is_array($schema['fields']) 
                        ? $schema['fields'] 
                        : $schema;
                        
                    $config = $service->configuration ?? [];

                    foreach ($schemaFields as $key => $field) {
                        if (array_key_exists($key, $config)) {
                            $checkoutData[] = [
                                'key' => $key,
                                'label' => $field['label'] ?? $key,
                                'value' => $config[$key],
                                'type' => $field['type'],
                            ];
                        }
                    }
                }
            }
        }

        return view('client::services.workspaces.overview', compact('service', 'variantOptions', 'clientActions', 'checkoutData', 'unpaidInvoice'));
    }
}