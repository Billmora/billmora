<?php

namespace App\Filament\Pages\Admin\Settings;

use App\Services\BillmoraService as Billmora;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Authentication extends Page
{
    protected static ?string $navigationIcon = 'tabler-password-user';
    protected static string $view = 'filament.pages.admin.settings.authentication';
    protected static ?string $slug = 'settings/authentication';
    protected ?string $subheading = 'Configure a authentication settings.';

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

    public function form(Form $form): Form
    {
        return $form
        ->statePath('data')
        ->schema([
            Forms\Components\Tabs::make()
                ->persistTabInQueryString()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('user')
                        ->label('User')
                        ->icon('tabler-user-plus')
                        ->schema($this->tabUser()),
                ]),
        ]);
    }

    private function tabUser(): array
    {
        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Toggle::make('user_verified')
                        ->label('User Must Verified')
                        ->inline(false)
                        ->onIcon('tabler-check')
                        ->offIcon('tabler-x')
                        ->onColor('success')
                        ->offColor('danger')
                        ->required()
                        ->helperText('Require users to verify their email address before they can login.')
                        ->default(Billmora::getAuth('user_verified')),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\CheckboxList::make('form_disable')
                            ->label('Hide Registration Form')
                            ->options([
                                'phone_number' => 'Phone Number',
                                'company_name' => 'Company Name',
                                'street_address_1' => 'Street Address 1',
                                'street_address_2' => 'Street Address 2',
                                'city' => 'City',
                                'country' => 'Country',
                                'state' => 'State',
                                'postcode' => 'Postcode',
                            ])
                            ->columns(4)
                            ->helperText('Select the fields you want to disable from the registration form.')
                            ->default(Billmora::getAuth('form_disable')),
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\CheckboxList::make('form_required')
                            ->label('Required Registration Form')
                            ->options([
                                'phone_number' => 'Phone Number',
                                'company_name' => 'Company Name',
                                'street_address_1' => 'Street Address 1',
                                'street_address_2' => 'Street Address 2',
                                'city' => 'City',
                                'country' => 'Country',
                                'state' => 'State',
                                'postcode' => 'Postcode',
                            ])
                            ->columns(4)
                            ->helperText('Select the fields you want to required from the registration form.')
                            ->default(Billmora::getAuth('form_required')),
                        ])
                ]),
        ];
    }

    public function save(): void
    {
        try {
            $validated = Validator::make($this->data, [
                'user_verified' => ['required', 'boolean'],
                'form_disable' => ['nullable', 'array'],
                'form_required' => ['nullable', 'array'],
            ])->validate();

            Billmora::setAuth($validated);

            Notification::make()
                ->title('Success')
                ->body('Authentication settings have been updated successfully.')
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
