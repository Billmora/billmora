<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PersonalAccessToken;

class ApiController extends Controller
{
    use AuditsSystem;

    /**
     * Available API permissions for tokens.
     *
     * @var array<string, string>
     */
    protected array $apiPermissions = [
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'services.view',
        'services.update',
        'services.delete',
        'registrants.view',
        'registrants.update',
        'registrants.delete',
        'invoices.view',
        'invoices.create',
        'invoices.update',
        'invoices.delete',
        'orders.view',
        'orders.delete',
        'tickets.view',
        'tickets.create',
        'tickets.update',
        'tickets.delete',
        'packages.view',
        'packages.create',
        'packages.update',
        'packages.delete',
        'catalogs.view',
        'catalogs.create',
        'catalogs.update',
        'catalogs.delete',
        'variants.view',
        'variants.create',
        'variants.update',
        'variants.delete',
        'tlds.view',
        'tlds.create',
        'tlds.update',
        'tlds.delete',
    ];

    /**
     * Applies permission-based middleware for accessing API settings.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.api.view')->only(['index']);
        $this->middleware('permission:settings.api.create')->only(['create', 'store']);
        $this->middleware('permission:settings.api.update')->only(['regenerate']);
        $this->middleware('permission:settings.api.delete')->only(['destroy']);
    }

    /**
     * Display the API settings page with a list of tokens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->input('searchToken');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $tokens = PersonalAccessToken::where('name', 'like', 'billmora-api:%')
            ->when($search, fn ($query) => $this->searchToken($query, $search))
            ->tap(fn ($query) => $this->sortToken($query, $sort, $direction))
            ->paginate(Billmora::getGeneral('misc_admin_pagination'))
            ->withQueryString();

        return view('admin::settings.api.index', compact('tokens', 'search', 'sort', 'direction'));
    }

    /**
     * Search for tokens by name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function searchToken($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Apply sorting to the token query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $sort
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function sortToken($query, string $sort, string $direction)
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, ['name', 'last_used_at', 'created_at', 'expires_at', 'rate_limit'])) {
            return $query->orderBy($sort, $direction);
        }

        return $query->latest();
    }

    /**
     * Show the form to create a new API token.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = $this->apiPermissions;

        return view('admin::settings.api.create', compact('permissions'));
    }

    /**
     * Store a newly created API token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token_name' => ['required', 'string', 'max:255'],
            'token_rate_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'token_whitelist_ips' => ['nullable', 'string', 'max:2000'],
            'token_expires_at' => ['nullable', 'date', 'after:now'],
            'token_permissions' => ['nullable', 'array'],
            'token_permissions.*' => ['string', 'in:' . implode(',', $this->apiPermissions)],
        ]);

        $user = Auth::user();

        $abilities = $validated['token_permissions'] ?? ['*'];
        $expiresAt = $validated['token_expires_at'] ? now()->parse($validated['token_expires_at']) : null;

        $token = $user->createToken(
            'billmora-api:' . $validated['token_name'],
            $abilities,
            $expiresAt,
        );

        // Update per-token settings
        $token->accessToken->update([
            'rate_limit' => $validated['token_rate_limit'],
            'whitelist_ips' => $validated['token_whitelist_ips'] ?: null,
        ]);

        $this->recordCreate('settings.api.token.created', [
            'token_id' => $token->accessToken->id,
            'name' => $validated['token_name'],
            'abilities' => $abilities,
        ]);

        return redirect()
            ->route('admin.settings.api')
            ->with('api_token', $token->plainTextToken)
            ->with('success', __('admin/settings/api.token_created_success'));
    }

    /**
     * Regenerate an API token (revoke old, create new with same settings).
     *
     * @param  int  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate($token)
    {
        $oldToken = PersonalAccessToken::findOrFail($token);
        $user = Auth::user();

        // Preserve settings from old token
        $name = $oldToken->name;
        $abilities = $oldToken->abilities;
        $expiresAt = $oldToken->expires_at;
        $rateLimit = $oldToken->rate_limit;
        $whitelistIps = $oldToken->whitelist_ips;

        // Delete old token
        $oldToken->delete();

        // Create new token with same settings
        $newToken = $user->createToken($name, $abilities, $expiresAt);

        $newToken->accessToken->update([
            'rate_limit' => $rateLimit,
            'whitelist_ips' => $whitelistIps,
        ]);

        $this->recordCreate('settings.api.token.regenerated', [
            'token_id' => $newToken->accessToken->id,
            'name' => $name,
        ]);

        return redirect()
            ->route('admin.settings.api')
            ->with('api_token', $newToken->plainTextToken)
            ->with('success', __('admin/settings/api.token_regenerated_success'));
    }

    /**
     * Delete an API token.
     *
     * @param  int  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($token)
    {
        $token = PersonalAccessToken::findOrFail($token);

        $this->recordDelete('settings.api.token.deleted', [
            'token_id' => $token->id,
            'name' => $token->name,
        ]);

        $token->delete();

        return redirect()
            ->route('admin.settings.api')
            ->with('success', __('common.delete_success', ['attribute' => 'API Token']));
    }
}
