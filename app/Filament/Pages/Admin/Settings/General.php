<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Services\BillmoraService as Billmora;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Locale;

class General extends Page
{
    protected static ?string $navigationIcon = 'tabler-nut';
    protected static string $view = 'filament.pages.admin.settings.general';
    protected static ?string $slug = 'settings/general';
    protected ?string $subheading = 'Configure a general settings.';

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

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->persistTabInQueryString()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('company')
                        ->label('Company')
                        ->icon('tabler-building')
                        ->schema($this->tabCompany()),
                    Forms\Components\Tabs\Tab::make('ordering')
                        ->label('Ordering')
                        ->icon('tabler-truck-delivery')
                        ->schema($this->tabOrdering()),
                ]),
        ];
    }

    private function tabCompany(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('company_name')
                                ->label('Name')
                                ->required()
                                ->helperText('The name of your Company.')
                                ->default(Billmora::getSetting('company_name', 'Billmora')),
                            Forms\Components\Select::make('company_portal_theme')
                                ->label('Portal Theme')
                                ->options(collect(File::directories(resource_path('themes/portal')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => basename($path)
                                    ])
                                    ->toArray())
                                ->native(false)
                                ->required()
                                ->helperText('The theme you want Billmora portal area to use.')
                                ->default(Billmora::getSetting('company_portal_theme', 'default')),
                            Forms\Components\Select::make('company_client_theme')
                                ->label('Client Theme')
                                ->options(collect(File::directories(resource_path('themes/client')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => basename($path)
                                    ])
                                    ->toArray())
                                ->native(false)
                                ->required()
                                ->helperText('The theme you want Billmora client area to use.')
                                ->default(Billmora::getSetting('company_client_theme', 'default')),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                             Forms\Components\TextInput::make('company_logo')
                                ->label('Logo URL')
                                ->suffixIcon('tabler-world')
                                ->required()
                                ->helperText('Enter your Company logo URL.')
                                ->default(Billmora::getSetting('company_logo', 'https://viidev.com/assets/img/logo/logo.png')),
                            Forms\Components\TextInput::make('company_favicon')
                                ->label('Favicon URL')
                                ->suffixIcon('tabler-world')
                                ->required()
                                ->helperText('Enter your Company favicon URL.')
                                ->default(Billmora::getSetting('company_favicon', 'https://viidev.com/assets/img/logo/logo.png')),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Textarea::make('company_description')
                                ->label('Description')
                                ->rows(3)
                                ->helperText('The description of your Company.')
                                ->default(Billmora::getSetting('company_description', 'Free and Open source Billing Management Operations & Recurring Automation.')),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Toggle::make('company_portal')
                                ->label('Portal')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->required()
                                ->helperText('If disable, area portal will be automated redirect to clientarea.')
                                ->default(Billmora::getSetting('company_portal', true)),
                            Forms\Components\Select::make('company_date_format')
                                ->label('Date Format')
                                ->options([
                                    'd/m/Y' => 'DD/MM/YYYY (31/12/2025)',
                                    'm/d/Y' => 'MM/DD/YYYY (12/31/2025)',
                                    'Y-m-d' => 'YYYY-MM-DD (2025-12-31)',
                                    'd-m-Y' => 'DD-MM-YYYY (31-12-2025)',
                                    'M d, Y' => 'Mon DD, YYYY (Dec 31, 2025)',
                                    'F d, Y' => 'Month DD, YYYY (December 31, 2025)',
                                ])
                                ->native(false)
                                ->required()
                                ->helperText('Default date format for your Company.')
                                ->default(Billmora::getSetting('company_date_format', 'd/m/Y')),
                            Forms\Components\Select::make('company_language')
                                ->label('Language')
                                ->options(collect(File::directories(resource_path('langs')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => Locale::getDisplayName(basename($path), app()->getLocale())
                                    ])
                                    ->toArray()
                                )
                                ->native(false)
                                ->required()
                                ->helperText('Default language for Billmora clientarea.')
                                ->default(Billmora::getSetting('company_language', 'en')),
                        ])
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Toggle::make('company_maintenance')
                                        ->label('Maintenance Mode')
                                        ->inline(false)
                                        ->onIcon('tabler-check')
                                        ->offIcon('tabler-x')
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->helperText('Prevents client area access when enabled.')
                                        ->default(Billmora::getSetting('company_maintenance', false)),
                                    Forms\Components\TextInput::make('company_maintenance_url')
                                        ->label('Maintenance URL')
                                        ->suffixIcon('tabler-world')
                                        ->helperText('If specified, clients will be redirected to that URL when Maintenance is enabled.')
                                        ->default(Billmora::getSetting('company_maintenance_url')),
                                ])
                                ->columnSpan(1),
                            Forms\Components\Textarea::make('company_maintenance_message')
                                ->label('Maintenance Message')
                                ->rows(5)
                                ->columnSpan(1)
                                ->helperText('The message that will be displayed when Maintenance is enabled.')
                                ->default(Billmora::getSetting('company_maintenance_message', 'We are currently performing maintenance and will be back shortly.')),
                        ]),
                ]),
        ];
    }

    private function tabOrdering(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Radio::make('ordering_redirect')
                        ->label('Auto Redirect on Checkout')
                        ->options([
                            'complete' => 'Just show the order completed page (no payment redirect)',
                            'invoice' => 'Automatically take the user to the invoice',
                            'payment' => 'Automatically forward the user to the payment gateway'
                        ])
                        ->required()
                        ->default(Billmora::getSetting('ordering_redirect', 'payment')),
                    Forms\Components\TextInput::make('ordering_grace')
                        ->label('Order days grace')
                        ->numeric()
                        ->required()
                        ->helperText('The number of days to allow for payment of an order before being overdue.')
                        ->default(Billmora::getSetting('ordering_grace', 0)),
                    Forms\Components\Toggle::make('ordering_tos')
                        ->label('Terms of Service')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('If enable, clients must agree to company Terms of Service.')
                        ->default(Billmora::getSetting('ordering_tos', true)),
                    Forms\Components\Toggle::make('ordering_notes')
                        ->label('Notes on Checkout')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('If enable, clients can enter additional notes on the order form.')
                        ->default(Billmora::getSetting('ordering_notes', false)),
                ]),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function save(): void
    {
        try {
            $validated = Validator::make($this->data, [
                'company_name' => ['required', 'string'],
                'company_portal_theme' => ['required', 'string'],
                'company_client_theme' => ['required', 'string'],
                'company_logo' => ['required', 'url'],
                'company_favicon' => ['required', 'url'],
                'company_description' => ['nullable', 'string'],
                'company_portal' => ['required', 'boolean'],
                'company_date_format' => ['required', 'string'],
                'company_language' => ['required', 'string'],
                'company_maintenance' => ['nullable', 'boolean'],
                'company_maintenance_url' => ['nullable', 'url'],
                'company_maintenance_message' => ['nullable', 'string'],

                'ordering_redirect' => ['required', 'string'],
                'ordering_grace' => ['required', 'integer'],
                'ordering_tos' => ['required', 'boolean'],
                'ordering_notes' => ['required', 'boolean'],
            ])->validate();

            Billmora::setSetting($validated);

            Notification::make()
                ->title('Success')
                ->body('General settings have been updated successfully.')
                ->success()
                ->send();
        } catch (ValidationException $e) {
            $errorMessages = '<ul>' . collect($e->errors())
            ->map(fn ($messages) => '<li>' . implode('</li><li>', $messages) . '</li>')
            ->implode('') . '</ul>';

            Notification::make()
                ->title('Validation Error')
                ->body($errorMessages)
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('An unexpected error occurred: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
