<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Models\Setting;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Locale;

class General extends Page
{
    protected static ?string $navigationIcon = 'tabler-nut';
    protected static string $view = 'filament.pages.admin.settings.general';
    protected static ?string $slug = 'settings/general';
    protected ?string $subheading = 'Configure a general settings.';

    public 
        $company_name, 
        $company_theme, 
        $company_logo, 
        $company_favicon, 
        $company_description, 
        $company_date_format, 
        $company_language, 
        $company_country, 
        $company_maintenance, 
        $company_maintenance_url, 
        $company_maintenance_message,
        $company_tos,
        $company_tos_url,
        $company_tos_content,
        $company_toc,
        $company_toc_url,
        $company_toc_content,
        $company_privacy,
        $company_privacy_url,
        $company_privacy_content;

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
            'company_date_format' => 'd/m/Y',
            'company_language' => 'en_US',
            'company_country' => 'ID',
            'company_maintenance' => null,
            'company_maintenance_url' => null,
            'company_maintenance_message' => 'We are currently performing maintenance and will be back shortly.',
            'company_tos' => null,
            'company_tos_url' => null,
            'company_tos_content' => null,
            'company_toc' => null,
            'company_toc_url' => null,
            'company_toc_content' => null,
            'company_privacy' => null,
            'company_privacy_url' => null,
            'company_privacy_content' => null,
        ];
    
        $settings = Setting::pluck('value', 'key');
    
        foreach ($defaults as $key => $defaultValue) {
            $this->$key = $settings[$key] ?? $defaultValue;
        }
    }    

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->persistTabInQueryString()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('company')
                        ->label('Company')
                        ->icon('tabler-building')
                        ->schema($this->tabCompany()),
                    Forms\Components\Tabs\Tab::make('term')
                        ->label('Term')
                        ->icon('tabler-circle-dashed-check')
                        ->schema($this->tabTerm()),
                ]),
        ];
    }

    private function tabCompany()
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('company_name')
                                ->label('Name')
                                ->required()
                                ->helperText('The name of your Company.'),
                            Forms\Components\Select::make('company_theme')
                                ->label('Theme')
                                ->options(collect(File::directories(resource_path('views/theme')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => basename($path)
                                    ])
                                    ->toArray())
                                ->native(false)
                                ->required()
                                ->columnSpan(1)
                                ->helperText('The theme you want Billmora to use.'),
                            Forms\Components\TextInput::make('company_logo')
                                ->label('Logo URL')
                                ->suffixIcon('tabler-world')
                                ->required()
                                ->helperText('Enter your Company logo URL.'),
                            Forms\Components\TextInput::make('company_favicon')
                                ->label('Favicon URL')
                                ->suffixIcon('tabler-world')
                                ->required()
                                ->helperText('Enter your Company favicon URL.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Textarea::make('company_description')
                                ->label('Description')
                                ->rows(3)
                                ->helperText('The description of your Company.'),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
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
                                ->helperText('Default date format for your Company.'),
                            Forms\Components\Select::make('company_language')
                                ->label('Language')
                                ->options(collect(File::directories(resource_path('lang')))
                                    ->mapWithKeys(fn ($path) => [
                                        basename($path) => Locale::getDisplayName(basename($path), app()->getLocale())
                                    ])
                                    ->toArray()
                                )
                                ->native(false)
                                ->required()
                                ->helperText('Default language for Billmora clientarea.'),
                            Forms\Components\Select::make('company_country')
                                ->label('Country')
                                ->options(function () {
                                    $client = new Client();
                                    $response = $client->get('https://restcountries.com/v3.1/all');
                                    $countries = json_decode($response->getBody()->getContents(), true);
                
                                    $options = [];
                                    foreach ($countries as $country) {
                                        $options[$country['cca2']] = $country['name']['common'];
                                    }

                                    asort($options);
                
                                    return $options;
                                })
                                ->searchable()
                                ->native(false)
                                ->required()
                                ->helperText('Default country for your Company.'),
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
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->helperText('Prevents client area access when enabled.'),
                                    Forms\Components\TextInput::make('company_maintenance_url')
                                        ->label('Maintenance URL')
                                        ->suffixIcon('tabler-world')
                                        ->helperText('If specified, clients will be redirected to that URL when Maintenance is enabled.'),
                                ])
                                ->columnSpan(1),
                            Forms\Components\Textarea::make('company_maintenance_message')
                                ->label('Maintenance Message')
                                ->rows(5)
                                ->columnSpan(1)
                                ->helperText('The message that will be displayed when Maintenance is enabled.'),
                        ]),
                ]),
        ];
    }

    private function tabTerm()
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('company_tos')
                                ->label('Terms of Service')
                                ->inline(false)
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/terms-of-service.'),
                            Forms\Components\TextInput::make('company_tos_url')
                                ->label('Terms of Service URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('company_tos_content')
                                ->label('Terms of Service Content')
                                ->disableToolbarButtons([
                                    'attachFiles',
                                ])
                                ->extraAttributes(['x-ref' => 'editor'])
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('fullscreen')
                                        ->icon('tabler-arrows-maximize')
                                        ->alpineClickHandler('$refs.editor.requestFullscreen()'))
                                        ->helperText('This content will be displayed at: https://example.com/terms-of-service.'),
                        ])
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('company_toc')
                                ->label('Terms of Condition')
                                ->inline(false)
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/terms-of-condition.'),
                            Forms\Components\TextInput::make('company_toc_url')
                                ->label('Terms of Condition URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('company_toc_content')
                                ->label('Terms of Condition Content')
                                ->disableToolbarButtons([
                                    'attachFiles',
                                ])
                                ->extraAttributes(['x-ref' => 'editor'])
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('fullscreen')
                                        ->icon('tabler-arrows-maximize')
                                        ->alpineClickHandler('$refs.editor.requestFullscreen()'))
                                        ->helperText('This content will be displayed at: https://example.com/terms-of-condition.'),

                        ])
                ]),

            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('company_privacy')
                                ->label('Privacy Policy')
                                ->inline(false)
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/privacy-policy.'),
                            Forms\Components\TextInput::make('company_privacy_url')
                                ->label('Privacy Policy URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('company_privacy_content')
                                ->label('Privacy Policy Content')
                                ->disableToolbarButtons([
                                    'attachFiles',
                                ])
                                ->extraAttributes(['x-ref' => 'editor'])
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('fullscreen')
                                        ->icon('tabler-arrows-maximize')
                                        ->alpineClickHandler('$refs.editor.requestFullscreen()'))
                                        ->helperText('This content will be displayed at: https://example.com/privacy-policy.'),

                        ])
                ]),
        ];
    }

    
    public function save()
    {
        try {
            $validated = $this->validate([
                'company_name' => 'required|string|max:255',
                'company_theme' => 'required|string',
                'company_logo' => 'required|url',
                'company_favicon' => 'required|url',
                'company_description' => 'nullable|string|max:255',
                'company_date_format' => 'required|string|in:d/m/Y,m/d/Y,Y-m-d,d-m-Y,M d, Y,F d, Y',
                'company_language' => 'required|string',
                'company_country' => 'required|string',
                'company_maintenance' => 'nullable|boolean',
                'company_maintenance_url' => 'nullable|url',
                'company_maintenance_message' => 'nullable|string|max:255',
                'company_tos' => 'nullable|boolean',
                'company_tos_url' => 'nullable|url',
                'company_tos_content' => 'nullable',
                'company_toc' => 'nullable|boolean',
                'company_toc_url' => 'nullable|url',
                'company_toc_content' => 'nullable',
                'company_privacy' => 'nullable|boolean',
                'company_privacy_url' => 'nullable|url',
                'company_privacy_content' => 'nullable',
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
