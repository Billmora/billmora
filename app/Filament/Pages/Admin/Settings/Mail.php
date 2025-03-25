<?php

namespace App\Filament\Pages\Admin\Settings;

use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;

class Mail extends Page
{
    protected static ?string $navigationIcon = 'tabler-mail';
    protected static string $view = 'filament.pages.admin.settings.mail';
    protected static ?string $slug = 'settings/mail';
    protected ?string $subheading = 'Configure a mail settings.';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Settings')
                ->url('/admin/settings')
                ->icon('tabler-settings')
                ->isActiveWhen(fn () => request()->is('admin/settings*'))
                ->sort(1),
        ];
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }
}
