<?php

namespace App\Filament\Pages\Admin;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Page;
use Filament\Forms\Form;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'tabler-layout-dashboard';
    protected static string $view = 'filament.pages.admin.dashboard';
    protected ?string $heading = 'Welcome to Billmora!';

    public function form(Form $form): Form
    {
        return $form->schema([
            // ...
        ]);
    }
}
