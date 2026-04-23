<?php

namespace App\Http\Controllers\Client\Registrants;

use App\Http\Controllers\Controller;
use App\Models\Registrant;
use App\Services\RegistrarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrarController extends Controller
{
    /**
     * Display the specified client action page or form for the active registrant.
     *
     * @param \App\Models\Registrant $registrant
     * @param string $slug
     * @param \App\Services\RegistrarService $registrarService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function show(Registrant $registrant, string $slug, RegistrarService $registrarService)
    {
        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        if ($registrant->status !== 'active' || !$registrant->plugin_id) {
            return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                ->with('error', __('client/registrants.action.unavailable'));
        }

        try {
            [$plugin] = $registrarService->bootPluginFor($registrant);
            
            if (!$plugin || !method_exists($plugin, 'getClientAction')) {
                return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                    ->with('error', __('client/registrants.action.unavailable'));
            }

            $actions = $plugin->getClientAction($registrant);
            $actionConfig = $actions[$slug] ?? null;

            if (!$actionConfig) {
                return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                    ->with('error', __('client/registrants.action.unavailable'));
            }

            if ($actionConfig['type'] === 'page') {
                $result = $plugin->handleClientAction($registrant, $slug, request()->all());

                if ($result instanceof \Illuminate\View\View) {
                    $result->with('clientActions', $actions);
                }

                return $result;
            }

            if ($actionConfig['type'] === 'form') {
                return view('client::registrants.workspaces.registrar', [
                    'registrant' => $registrant,
                    'slug' => $slug,
                    'pageSchema' => $actionConfig['schema'] ?? [],
                    'clientActions' => $actions,
                ]);
            }

            return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                ->with('error', __('client/registrants.action.invalid_type'));

        } catch (\Exception $e) {
            return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process and execute a client action with validation and error handling.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Registrant $registrant
     * @param string $slug
     * @param \App\Services\RegistrarService $registrarService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function handle(Request $request, Registrant $registrant, string $slug, RegistrarService $registrarService)
    {
        if ($registrant->user_id !== Auth::id()) {
            abort(404);
        }

        if ($registrant->status !== 'active' || !$registrant->plugin_id) {
            return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                ->with('error', __('client/registrants.action.unavailable'));
        }

        try {
            [$plugin] = $registrarService->bootPluginFor($registrant);
            
            if (!$plugin) {
                return back()->with('error', __('client/registrants.action.unavailable'));
            }

            $actions = $plugin->getClientAction($registrant);
            $actionConfig = $actions[$slug] ?? null;
            $actionData = $request->all();

            if ($actionConfig && $actionConfig['type'] === 'form' && $request->isMethod('post')) {
                $rules = $this->extractValidationRules($actionConfig['schema'] ?? []);

                if (!empty($rules)) {
                    $request->validate($rules);
                }
            }

            $result = $plugin->handleClientAction($registrant, $slug, $actionData);

            if ($result instanceof \Illuminate\Http\RedirectResponse || $result instanceof \Illuminate\Http\Response || $result instanceof \Illuminate\View\View) {
                return $result;
            }

            if (is_string($result)) {
                if (filter_var($result, FILTER_VALIDATE_URL)) {
                    return redirect()->away($result);
                }
                return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])->with('success', $result);
            }

            return redirect()->route('client.registrants.show', ['registrant' => $registrant->registrant_number])
                ->with('success', __('common.saved'));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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
