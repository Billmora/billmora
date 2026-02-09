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

        return view('client::services.workspaces.variant', compact('service', 'variantOptions'));
    }

    /**
     * Display the form for a specific client action on the service.
     *
     * @param \App\Models\Service $service
     * @param string $slug
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showActionForm(Service $service, string $slug)
    {
        if ($service->user_id !== Auth::id()) {
            abort(404);
        }

        $driver = $service->provisioning?->getPluginInstance();

        if (!$driver) {
            return redirect()->route('client.services.show', $service->id)
                ->with('error', 'Provisioning driver not found.');
        }

        $pageSchema = $driver->getClientActionForm($service, $slug);

        if (empty($pageSchema)) {
            abort(404, 'Page not found or action does not require input.');
        }

        return view('client::services.workspaces.provisioning', compact('service', 'slug', 'pageSchema'));
    }

    /**
     * Process and execute a client action with validation and error handling.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Service $service
     * @param string $slug
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function processAction(Request $request, Service $service, string $slug)
    {
        if ($service->user_id !== Auth::id()) {
            abort(404);
        }

        $driver = $service->provisioning?->getPluginInstance();
        
        if (!$driver) {
            return back()->with('error', 'Provisioning driver unavailable.');
        }

        $schema = $driver->getClientActionForm($service, $slug);

        if ($schema && $request->isMethod('post')) {
            $rules = $this->extractValidationRules($schema);
            $validated = $request->validate($rules);
        }

        try {
            $result = $driver->processClientAction($service, $slug, $request->all());

            if ($result instanceof \Illuminate\Http\RedirectResponse || $result instanceof \Illuminate\Http\Response) {
                return $result;
            }

            if (is_string($result) && filter_var($result, FILTER_VALIDATE_URL)) {
                return redirect()->away($result);
            }


            return redirect()->route('client.services.show', $service->id)
                ->with('success', 'Action processed successfully.');

        } catch (\Exception $e) {
            return redirect()->route('client.services.show', $service->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Extract validation rules from the schema fields configuration.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function extractValidationRules(array $schema): array
    {
        $rules = [];
        if (isset($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $fieldKey => $fieldConfig) {
                if (isset($fieldConfig['rules'])) {
                    $rules[$fieldKey] = $fieldConfig['rules'];
                }
            }
        }
        return $rules;
    }
}