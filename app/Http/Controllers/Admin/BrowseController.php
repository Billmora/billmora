<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\BrowseInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class BrowseController extends Controller
{
    protected const CACHE_KEY = 'admin.browse.static_items';
    protected const CACHE_TTL = 3600; // 1 hour — static items never change at runtime

    /**
     * Return a static list of predefined admin navigation items for quick search browsing.
     *
     * @return array
     */
    protected function staticItems(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'category' => 'admin',
                'url' => route('admin.dashboard')
            ],
            [
                'title' => 'Users',
                'category' => 'management',
                'url' => route('admin.users')
            ],
            [
                'title' => 'Orders',
                'category' => 'management',
                'url' => route('admin.orders')
            ],
            [
                'title' => 'Services',
                'category' => 'management',
                'url' => route('admin.services')
            ],
            [
                'title' => 'Registrants',
                'category' => 'management',
                'url' => route('admin.registrants')
            ],
            [
                'title' => 'Invoices',
                'category' => 'management',
                'url' => route('admin.invoices')
            ],
            [
                'title' => 'Transactions',
                'category' => 'management',
                'url' => route('admin.transactions')
            ],
            [
                'title' => 'Broadcasts',
                'category' => 'management',
                'url' => route('admin.broadcasts')
            ],
            [
                'title' => 'Tickets',
                'category' => 'management',
                'url' => route('admin.tickets')
            ],
            [
                'title' => 'Catalogs',
                'category' => 'product',
                'url' => route('admin.catalogs')
            ],
            [
                'title' => 'Packages',
                'category' => 'product',
                'url' => route('admin.packages')
            ],
            [
                'title' => 'Variants',
                'category' => 'product',
                'url' => route('admin.variants')
            ],
            [
                'title' => 'TLDs',
                'category' => 'product',
                'url' => route('admin.tlds')
            ],
            [
                'title' => 'Coupons',
                'category' => 'product',
                'url' => route('admin.coupons')
            ],
            [
                'title' => 'Settings',
                'category' => 'system',
                'url' => route('admin.settings')
            ],
            [
                'title' => 'Plugins',
                'category' => 'system',
                'url' => route('admin.plugins')
            ],
            [
                'title' => 'Themes',
                'category' => 'system',
                'url' => route('admin.themes')
            ],
            [
                'title' => 'Automations',
                'category' => 'system',
                'url' => route('admin.automations')
            ],
            [
                'title' => 'Tasks',
                'category' => 'system',
                'url' => route('admin.tasks')
            ],
            [
                'title' => 'Audits',
                'category' => 'system',
                'url' => route('admin.audits')
            ],
            [
                'title' => 'Health',
                'category' => 'system',
                'url' => route('admin.health')
            ],
            [
                'title' => 'Update',
                'category' => 'system',
                'url' => route('admin.update')
            ],
            [
                'title' => 'General Settings',
                'category' => 'settings',
                'url' => route('admin.settings.general.company')
            ],
            [
                'title' => 'Mail Settings',
                'category' => 'settings',
                'url' => route('admin.settings.mail.mailer')
            ],
            [
                'title' => 'Authentication Settings',
                'category' => 'settings',
                'url' => route('admin.settings.auth.user')
            ],
            [
                'title' => 'Captcha Settings',
                'category' => 'settings',
                'url' => route('admin.settings.captcha.provider')
            ],
            [
                'title' => 'Role Settings',
                'category' => 'settings',
                'url' => route('admin.settings.roles')
            ],
            [
                'title' => 'Currency Settings',
                'category' => 'settings',
                'url' => route('admin.settings.currencies')
            ],
            [
                'title' => 'Taxes Settings',
                'category' => 'settings',
                'url' => route('admin.settings.taxes')
            ],
            [
                'title' => 'Punishments Settings',
                'category' => 'settings',
                'url' => route('admin.settings.punishments')
            ],
            [
                'title' => 'Ticket Settings',
                'category' => 'settings',
                'url' => route('admin.settings.ticket.ticketing')
            ],
            [
                'title' => 'Automation Settings',
                'category' => 'settings',
                'url' => route('admin.settings.automation.scheduling')
            ],
            [
                'title' => 'API Settings',
                'category' => 'settings',
                'url' => route('admin.settings.api')
            ],
            [
                'title' => 'Provisionings',
                'category' => 'plugins',
                'url' => route('admin.provisionings')
            ],
            [
                'title' => 'Registrars',
                'category' => 'plugins',
                'url' => route('admin.registrars')
            ],
            [
                'title' => 'Gateways',
                'category' => 'plugins',
                'url' => route('admin.gateways')
            ],
            [
                'title' => 'Modules',
                'category' => 'plugins',
                'url' => route('admin.modules')
            ],
            [
                'title' => 'Email History',
                'category' => 'audits',
                'url' => route('admin.audits.email')
            ],
            [
                'title' => 'User Activity',
                'category' => 'audits',
                'url' => route('admin.audits.user')
            ],
            [
                'title' => 'System Logs',
                'category' => 'audits',
                'url' => route('admin.audits.system')
            ],
        ];
    }

    /**
     * Auto-discover all model classes that implement the BrowseInterface for searchable indexing.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function discoverSearchables(): Collection
    {
        return collect(File::allFiles(app_path('Models')))
            ->map(fn($file) => 'App\\Models\\' . $file->getFilenameWithoutExtension())
            ->filter(fn($class) => class_exists($class) && is_subclass_of($class, BrowseInterface::class));
    }

    /**
     * Retrieve all browse items from cache, returning only static navigation items.
     * Dynamic model records (services, invoices, etc.) are now searched live via search().
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItems(): Collection
    {
        return collect($this->staticItems());
    }

    /**
     * Live search endpoint. Called via AJAX from the Browse modal when the user types.
     * Merges static nav item results with live DB results from all searchable models.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = collect();

        $this->discoverSearchables()
            ->each(function ($model) use ($query, &$results) {
                $results = $results->merge($model::searchBrowseItems($query));
            });

        return response()->json($results->values()->take(20));
    }
}
