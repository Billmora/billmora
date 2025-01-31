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
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

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
        $term_tos,
        $term_tos_url,
        $term_tos_content,
        $term_toc,
        $term_toc_url,
        $term_toc_content,
        $term_privacy,
        $term_privacy_url,
        $term_privacy_content,
        $social_discord,
        $social_youtube,
        $social_whatsapp,
        $social_instagram,
        $social_facebook,
        $social_twitter,
        $social_linkedin,
        $social_github,
        $social_reddit,
        $social_skype,
        $social_telegram,
        $ordering_tos,
        $ordering_notes,
        $ordering_firstname,
        $ordering_lastname,
        $ordering_email,
        $ordering_phone,
        $ordering_company,
        $ordering_address1,
        $ordering_address2,
        $ordering_city,
        $ordering_country,
        $ordering_state,
        $ordering_postcode;

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
            'term_tos' => false,
            'term_tos_url' => null,
            'term_tos_content' => null,
            'term_toc' => false,
            'term_toc_url' => null,
            'term_toc_content' => null,
            'term_privacy' => false,
            'term_privacy_url' => null,
            'term_privacy_content' => null,
            'social_discord' => null,
            'social_youtube' => null,
            'social_whatsapp' => null,
            'social_instagram' => null,
            'social_facebook' => null,
            'social_twitter' => null,
            'social_linkedin' => null,
            'social_github' => null,
            'social_reddit' => null,
            'social_skype' => null,
            'social_telegram' => null,
            'ordering_tos' => false,
            'ordering_notes' => false,
            'ordering_firstname' => null,
            'ordering_lastname' => null,
            'ordering_email' => null,
            'ordering_phone' => null,
            'ordering_company' => null,
            'ordering_address1' => null,
            'ordering_address2' => null,
            'ordering_city' => null,
            'ordering_country' => 'ID',
            'ordering_state' => null,
            'ordering_postcode' => null,
        ];
    
        $settings = Setting::pluck('value', 'key');
    
        foreach ($defaults as $key => $defaultValue) {
            $value = $settings[$key] ?? $defaultValue;

            if (is_bool($defaultValue)) {
                $this->$key = (bool) $value;
            } elseif (is_int($defaultValue)) {
                $this->$key = (int) $value;
            } elseif (is_float($defaultValue)) {
                $this->$key = (float) $value;
            } elseif (is_null($defaultValue)) {
                $this->$key = $value !== null ? $value : null;
            } else {
                $this->$key = $value;
            }
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
                    Forms\Components\Tabs\Tab::make('ordering')
                        ->label('Ordering')
                        ->icon('tabler-truck-delivery')
                        ->schema($this->tabOrdering()),
                    Forms\Components\Tabs\Tab::make('term')
                        ->label('Term')
                        ->icon('tabler-circle-dashed-check')
                        ->schema($this->tabTerm()),
                    Forms\Components\Tabs\Tab::make('social')
                        ->label('Social')
                        ->icon('tabler-social')
                        ->schema($this->tabSocial()),
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
                                ->options(config('utils.countries'))
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
                                        ->onIcon('tabler-check')
                                        ->offIcon('tabler-x')
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

    private function tabOrdering()
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Toggle::make('ordering_tos')
                        ->label('Terms of Service')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('If enable, clients must agree to company Terms of Service.'),
                    Forms\Components\Toggle::make('ordering_notes')
                        ->label('Notes on Checkout')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('If enable, clients can enter additional info on the order form.'),
                ]),
            Forms\Components\Section::make('Default Billing or Contact details')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('ordering_firstname')
                                ->label('First Name'),
                            Forms\Components\TextInput::make('ordering_lastname')
                                ->label('Last Name'),
                            Forms\Components\TextInput::make('ordering_email')
                                ->label('Email Address'),
                            PhoneInput::make('ordering_phone')
                                ->label('Phone Number')
                                ->displayNumberFormat(PhoneInputNumberType::E164),
                            Forms\Components\TextInput::make('ordering_company')
                                ->label('Company Name'),
                            Forms\Components\TextInput::make('ordering_address1')
                                ->label('Street Address 1'),
                            Forms\Components\TextInput::make('ordering_address2')
                                ->label('Street Address 2'),
                            Forms\Components\TextInput::make('ordering_city')
                                ->label('City'),
                            Forms\Components\Select::make('ordering_country')
                                ->label('Country')
                                ->options(config('utils.countries'))
                                ->searchable(),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('ordering_state')
                                        ->label('State/Region'),
                                    Forms\Components\TextInput::make('ordering_postcode')
                                        ->label('Postcode'),
                                ])
                                ->columnSpan(1),
                        ]),
                ])
        ];
    }

    private function tabTerm()
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('term_tos')
                                ->label('Terms of Service')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/terms-of-service.'),
                            Forms\Components\TextInput::make('term_tos_url')
                                ->label('Terms of Service URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('term_tos_content')
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
                            Forms\Components\Toggle::make('term_toc')
                                ->label('Terms of Condition')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/terms-of-condition.'),
                            Forms\Components\TextInput::make('term_toc_url')
                                ->label('Terms of Condition URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('term_toc_content')
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
                            Forms\Components\Toggle::make('term_privacy')
                                ->label('Privacy Policy')
                                ->inline(false)
                                ->onIcon('tabler-check')
                                ->offIcon('tabler-x')
                                ->onColor('success')
                                ->offColor('danger')
                                ->helperText('Enable to show the page at: https://example.com/privacy-policy.'),
                            Forms\Components\TextInput::make('term_privacy_url')
                                ->label('Privacy Policy URL')
                                ->suffixIcon('tabler-world')
                                ->helperText('If specified, clients will be redirected to that URL when they want reading.'),
                        ]),
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\RichEditor::make('term_privacy_content')
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

    private function tabSocial()
    {
        return [
            Forms\Components\Grid::make(1)
                ->schema([  
                    Forms\Components\TextInput::make('social_discord')
                        ->label('Discord')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_youtube')
                        ->label('YouTube')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_whatsapp')
                        ->label('WhatsApp')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_instagram')
                        ->label('Instagram')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_facebook')
                        ->label('Facebook')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_twitter')
                        ->label('Twitter')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_linkedin')
                        ->label('LinkedIn')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_github')
                        ->label('GitHub')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_reddit')
                        ->label('Reddit')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_skype')
                        ->label('Skype')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    Forms\Components\TextInput::make('social_telegram')
                        ->label('Telegram')
                        ->suffixIcon('tabler-world')
                        ->inlineLabel(),
                    
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
                'term_tos' => 'nullable|boolean',
                'term_tos_url' => 'nullable|url',
                'term_tos_content' => 'nullable',
                'term_toc' => 'nullable|boolean',
                'term_toc_url' => 'nullable|url',
                'term_toc_content' => 'nullable',
                'term_privacy' => 'nullable|boolean',
                'term_privacy_url' => 'nullable|url',
                'term_privacy_content' => 'nullable',
                'social_discord' => 'nullable|url',
                'social_youtube' => 'nullable|url',
                'social_whatsapp' => 'nullable|url',
                'social_instagram' => 'nullable|url',
                'social_facebook' => 'nullable|url',
                'social_twitter' => 'nullable|url',
                'social_linkedin' => 'nullable|url',
                'social_github' => 'nullable|url',
                'social_reddit' => 'nullable|url',
                'social_skype' => 'nullable|url',
                'social_telegram' => 'nullable|url',
                'ordering_tos' => 'nullable|boolean',
                'ordering_notes' => 'nullable|boolean',
                'ordering_firstname' => 'nullable|string',
                'ordering_lastname' => 'nullable|string',
                'ordering_email' => 'nullable|email',
                'ordering_phone' => 'nullable|string',
                'ordering_company' => 'nullable|string',
                'ordering_address1' => 'nullable|string',
                'ordering_address2' => 'nullable|string',
                'ordering_city' => 'nullable|string',
                'ordering_country' => 'nullable|string',
                'ordering_state' => 'nullable|string',
                'ordering_postcode' => 'nullable|string',
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
