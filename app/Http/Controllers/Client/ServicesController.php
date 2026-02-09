<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\VariantOption;
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
            ->paginate(15);

        return view('client::services.index', compact('services'));
    }

    /**
     * Display the specified service details with variant options for the authenticated user.
     *
     * @param \App\Models\Service $service
     * @return \Illuminate\Contracts\View\View
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(Service $service)
    {
        if ($service->user_id !== Auth::id()) {
            abort(404);
        }

        $service->load([
            'package.catalog', 
            'packagePrice', 
            'provisioning',
            'order'
        ]);

        $variantOptions = collect();
        
        if (!empty($service->variant_selections)) {
            $optionIds = collect($service->variant_selections)->flatten()->filter();
            
            if ($optionIds->isNotEmpty()) {
                $variantOptions = VariantOption::with('variant')
                    ->whereIn('id', $optionIds)
                    ->get();
            }
        }

        return view('client::services.show', compact('service', 'variantOptions'));
    }
}
