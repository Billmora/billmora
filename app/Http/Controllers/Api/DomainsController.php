<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tld;
use App\Services\RegistrarService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DomainsController extends Controller
{
    /**
     * Check domain availability.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\RegistrarService  $registrarService
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request, RegistrarService $registrarService)
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $fullDomain = strtolower($request->input('domain'));


        if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]\.[a-z]+(\.[a-z]+)?$/', $fullDomain)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid domain name format.',
            ], 422);
        }


        $dotPos = strpos($fullDomain, '.');
        $tldString = substr($fullDomain, $dotPos);


        $tld = Tld::where('tld', $tldString)->where('status', 'visible')->first();

        if (!$tld) {
            return response()->json([
                'success' => false,
                'message' => 'TLD is not supported.',
            ], 422);
        }

        $response = [
            'domain' => $fullDomain,
            'available' => false,
            'premium' => false,
            'price' => null,
            'message' => '',
            'tld_id' => $tld->id,
        ];

        try {

            [$plugin, $config] = $registrarService->bootPluginForTld($tld);

            $result = $plugin->checkAvailability($fullDomain);

            $response['available'] = $result['available'] ?? false;
            $response['premium'] = $result['premium'] ?? false;

            $response['price'] = $result['price'] ?? null;
            $response['success'] = true;

        } catch (Exception $e) {

            $response['success'] = false;
            $response['message'] = 'Failed to check domain availability: ' . $e->getMessage();
        }

        return response()->json($response);
    }
}
