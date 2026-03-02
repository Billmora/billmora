<?php

namespace App\Http\Controllers\Client\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProvisioningController extends Controller
{
    /**
     * Display the specified client action page or form for the active service.
     *
     * @param \App\Models\Service $service
     * @param string $slug
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function show(Service $service, string $slug, PluginManager $manager)
    {
        if ($service->user_id !== Auth::id() || $service->status !== 'active') {
            abort(404);
        }

        if ($service->provisioning && !$service->provisioning->is_active) {
            return redirect()->route('client.services.show', $service->id)
                ->with('error', __('validation.provisioning_disabled', ['name' => $service->provisioning->name]));
        }

        $plugin = $service->provisioning ? $manager->bootInstance($service->provisioning) : null;

        if (!$plugin || !method_exists($plugin, 'getClientAction')) {
            return redirect()->route('client.services.show', $service->id)
                ->with('error', __('client/services.provisioning.not_found'));
        }

        $actions = $plugin->getClientAction($service);
        $actionConfig = $actions[$slug] ?? null;

        if (!$actionConfig) {
            return redirect()->route('client.services.show', $service->id)
                ->with('error', __('client/services.action.unavailable'));
        }

        if ($actionConfig['type'] === 'page') {
            try {
                $result = $plugin->handleClientAction($service, $slug, request()->all());

                if ($result instanceof \Illuminate\View\View) {
                    $result->with('clientActions', $actions);
                }

                return $result;
            } catch (\Exception $e) {
                return redirect()->route('client.services.show', $service->id)
                    ->with('error', $e->getMessage());
            }
        }

        if ($actionConfig['type'] === 'form') {
            return view('client::services.workspaces.provisioning', [
                'service' => $service,
                'slug' => $slug,
                'pageSchema' => $actionConfig['schema'] ?? [],
                'clientActions' => $actions,
            ]);
        }

        return redirect()->route('client.services.show', $service->id)
            ->with('error', __('client/services.action.invalid_type'));
    }

    /**
     * Process and execute a client action with validation and error handling.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Service $service
     * @param string $slug
     * @param \App\Services\PluginManager $manager
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function handle(Request $request, Service $service, string $slug, PluginManager $manager)
    {
        if ($service->user_id !== Auth::id() || $service->status !== 'active') {
            abort(404);
        }

        if ($service->provisioning && !$service->provisioning->is_active) {
            return back()->with('error', __('validation.provisioning_disabled', ['name' => $service->provisioning->name]));
        }

        $plugin = $service->provisioning ? $manager->bootInstance($service->provisioning) : null;

        if (!$plugin) {
            return back()->with('error', __('client/services.provisioning.unavailable'));
        }

        $actions = $plugin->getClientAction($service);
        $actionConfig = $actions[$slug] ?? null;
        $actionData = $request->all();

        if ($actionConfig && $actionConfig['type'] === 'form' && $request->isMethod('post')) {
            $rules = $this->extractValidationRules($actionConfig['schema'] ?? []);

            if (!empty($rules)) {
                $request->validate($rules);
            }
        }

        try {
            $result = $plugin->handleClientAction($service, $slug, $actionData);

            if ($result instanceof \Illuminate\Http\RedirectResponse || $result instanceof \Illuminate\Http\Response || $result instanceof \Illuminate\View\View) {
                return $result;
            }

            if (is_string($result)) {
                if (filter_var($result, FILTER_VALIDATE_URL)) {
                    return redirect()->away($result);
                }
                return redirect()->route('client.services.show', $service->id)->with('success', $result);
            }

            return redirect()->route('client.services.show', $service->id)
                ->with('success', __('client/services.action.success'));

        } catch (\Exception $e) {
            return redirect()->route('client.services.show', $service->id)
                ->with('error', __('client/services.action.failed', ['message' => $e->getMessage()]));
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
        $schemaArray = isset($schema['fields']) && is_array($schema['fields'])
            ? $schema['fields']
            : $schema;

        $rules = [];

        foreach ($schemaArray as $fieldKey => $fieldConfig) {
            if (isset($fieldConfig['rules'])) {
                $rules[$fieldKey] = $fieldConfig['rules'];
            }
        }

        return $rules;
    }
}
