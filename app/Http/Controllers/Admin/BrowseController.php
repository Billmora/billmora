<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\BrowseInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class BrowseController extends Controller
{
    protected const CACHE_KEY = 'admin.browse.items';
    protected const CACHE_TTL = 300; // 5 minute

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
                'title' => 'Provisionings',
                'category' => 'plugins',
                'url' => route('admin.provisionings')
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
     * Retrieve all browse items from cache, merging static items with discovered model items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItems(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $items = collect($this->staticItems());

            $this->discoverSearchables()
                ->each(function ($model) use (&$items) {
                    $items = $items->merge($model::toBrowseItems());
                });

            return $items->values();
        });
    }

    /**
     * Invalidate the browse items cache to force a fresh re-index on next retrieval.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
