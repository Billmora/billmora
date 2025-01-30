<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Models\Setting;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class General extends Page
{
    protected static ?string $navigationIcon = 'tabler-nut';
    protected static string $view = 'filament.pages.admin.settings.general';
    protected static ?string $slug = 'settings/general';
    protected ?string $subheading = 'Configure a general settings.';

    public $company_name, $company_theme, $company_logo, $company_favicon, $company_description, $company_maintenance, $company_maintenance_url, $company_maintenance_message;

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

    public function mount(): void
    {
        $defaults = [
            'company_name' => 'Billmora',
            'company_theme' => 'default',
            'company_logo' => 'https://viidev.com/assets/img/logo/logo.png',
            'company_favicon' => 'https://viidev.com/assets/img/logo/logo.png',
            'company_description' => 'Free and Open source Billing Management Operations & Recurring Automation.',
            'company_maintenance' => null,
            'company_maintenance_url' => null,
            'company_maintenance_message' => 'We are currently performing maintenance and will be back shortly.',
        ];
    
        $settings = Setting::pluck('value', 'key');
    
        foreach ($defaults as $key => $defaultValue) {
            $this->$key = $settings[$key] ?? $defaultValue;
        }
    }    

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('Tabs')
                ->persistTabInQueryString()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('company')
                        ->label('Company')
                        ->icon('tabler-building')
                        ->schema($this->tabCompany()),
                ]),
        ];
    }

    private function tabCompany()
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('company_name')
                        ->label('Name')
                        ->required(),
                    Forms\Components\Select::make('company_theme')
                        ->label('Theme')
                        ->options(collect(File::directories(resource_path('views/theme')))
                            ->mapWithKeys(fn ($path) => [
                                basename($path) => basename($path)
                            ])
                            ->toArray())
                        ->native(false)
                        ->required()
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('company_logo')
                        ->label('Logo URL')
                        ->required(),
                    Forms\Components\TextInput::make('company_favicon')
                        ->label('Favicon URL')
                        ->required(),
                ]),

            Forms\Components\Grid::make(1)
                ->schema([
                    Forms\Components\Textarea::make('company_description')
                        ->label('Description')
                        ->rows(3)
                ]),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Toggle::make('company_maintenance')
                                    ->label('Maintenance Mode')
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),
                                Forms\Components\TextInput::make('company_maintenance_url')
                                    ->label('Maintenance URL'),
                            ])
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('company_maintenance_message')
                            ->label('Maintenance Message')
                            ->rows(4)
                            ->columnSpan(2), 
                    ]),

        ];
    }
    
    public function save()
    {
        try {
            $validated = $this->validate([
                'company_name' => 'required|string|max:255',
                'company_theme' => 'required',
                'company_logo' => 'required|url',
                'company_favicon' => 'required|url',
                'company_description' => 'nullable|string|max:255',
                'company_maintenance' => 'nullable|boolean',
                'company_maintenance_url' => 'nullable|url',
                'company_maintenance_message' => 'nullable|string|max:255',
            ]);
    
            collect($validated)->each(function ($value, $key) {
                if (!is_null($value)) {
                    Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                }
            });
    
            Notification::make()
                ->title('Saved successfully!')
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title('Failed to save')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
    
}
