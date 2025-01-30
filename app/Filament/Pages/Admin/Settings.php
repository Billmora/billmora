<?php

namespace App\Filament\Pages\Admin;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'tabler-settings';
    protected static string $view = 'filament.pages.admin.settings';
    protected ?string $subheading = 'Set up and configure your Billmora Settings.';
}
