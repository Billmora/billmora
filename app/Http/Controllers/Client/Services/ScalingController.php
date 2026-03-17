<?php

namespace App\Http\Controllers\Client\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Service\ScalingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ScalingController extends Controller
{
    /**
     * Inject the ScalingService dependency into the scaling controller.
     *
     * @param  \App\Services\Service\ScalingService  $scalingService
     */
    public function __construct(private ScalingService $scalingService)
    {
        // 
    }

    /**
     * Display the Livewire multi-step scaling wizard for the specified client service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);

        return view('client::services.workspaces.scaling', compact('service'));
    }

    /**
     * Handle each step of the scaling wizard form submission for the specified client service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);

        $request->validate(['package_id' => 'required', 'integer']);

        $targetPackage = $this->scalingService->getStrictTargetPackage($service, $request->package_id);

        if (!$targetPackage) {
            return back()->withInput()->with('error', __('client/services.scaling.invalid_package'));
        }

        $rules = ['variants' => 'nullable|array'];
        $customAttributes = [];

        foreach ($targetPackage->variants as $variant) {
            $field = "variants.{$variant->id}";
            $fieldRules = [];

            if (in_array(strtolower($variant->type), ['select', 'radio', 'slider'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = Rule::exists('variant_options', 'id')
                ->where('variant_id', $variant->id);

            $rules[$field] = $fieldRules;
            $customAttributes[$field] = $variant->name; 
        }

        $validated = $request->validate($rules, [], $customAttributes);
        $cleanVariants = $validated['variants'] ?? [];

        $isSamePackage = $targetPackage->id === $service->package_id;
        
        if ($isSamePackage) {
            $currentVariants = $service->variant_selections ?? [];
            ksort($currentVariants);
            ksort($cleanVariants);
            
            if (json_encode($currentVariants) === json_encode($cleanVariants)) {
                return back()->withInput()->with('error', __('client/services.scaling.no_variant_changes'));
            }
        }

        try {
            $calculation = $this->scalingService->calculateProrata($service, $targetPackage, $cleanVariants);
            
            $invoice = $this->scalingService->executeOrder($service, $targetPackage, $calculation, $cleanVariants);

            $msg = $calculation['is_downgrade']
                ? __('client/services.scaling.downgrade_success')
                : __('client/services.scaling.upgrade_success');

            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])->with('success', $msg);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}