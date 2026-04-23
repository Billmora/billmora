<form action="{{ route('admin.registrants.update', ['registrant' => $registrant->id]) }}" method="POST" class="w-full lg:w-5/7 flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input
            name="domain_display"
            label="{{ __('admin/registrants.domain_label') }}"
            helper="{{ __('admin/registrants.domain_helper') }}"
            value="{{ $registrant->domain }}"
            disabled
        />
        <x-admin::input
            name="registrant_number_display"
            label="{{ __('admin/registrants.number_label') }}"
            helper="{{ __('admin/registrants.number_helper') }}"
            value="{{ $registrant->registrant_number }}"
            disabled
        />
        <div class="w-full">
            <label class="flex text-slate-600 font-semibold mb-1">
                {{ __('admin/registrants.user_label') }}
            </label>
            <a href="{{ route('admin.users.summary', ['user' => $registrant->user_id]) }}"
               class="relative inline-block w-full group" target="_blank">
                <input type="text" value="{{ $registrant->user->email }}"
                    class="w-full px-3 py-2.25 bg-billmora-1 text-slate-700 border-2 border-billmora-2 rounded-xl cursor-not-allowed"
                    disabled>
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <span class="block bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white text-sm rounded-lg transition duration-300 cursor-pointer">
                        {{ __('admin/registrants.go_to_user') }}
                    </span>
                </div>
            </a>
            <p class="mt-1 text-sm text-slate-500">{{ __('admin/registrants.user_helper') }}</p>
        </div>
        <x-admin::input
            name="tld_display"
            label="{{ __('admin/registrants.tld_label') }}"
            helper="{{ __('admin/registrants.tld_helper') }}"
            value="{{ $registrant->tld->tld ?? '-' }}"
            disabled
        />
        <x-admin::input
            name="registration_type_display"
            label="{{ __('admin/registrants.registration_type_label') }}"
            helper="{{ __('admin/registrants.registration_type_helper') }}"
            value="{{ ucfirst(str_replace('_', ' ', $registrant->registration_type)) }}"
            disabled
        />
        <div wire:key="bridge-plugin" x-on:change="$wire.set('plugin_id', $event.target.value)">
            <x-admin::select
                wire:key="select-plugin"
                name="plugin_id"
                label="{{ __('admin/registrants.registrar_label') }}"
                helper="{{ __('admin/registrants.registrar_helper') }}"
            >
                <option value="">{{ __('common.none') }}</option>
                @foreach ($this->registrars as $registrar)
                    <option value="{{ $registrar->id }}" wire:key="opt-reg-{{ $registrar->id }}"
                        @selected($plugin_id == $registrar->id)>
                        {{ $registrar->name }}
                    </option>
                @endforeach
            </x-admin::select>
        </div>
        <x-admin::select name="status" label="{{ __('admin/registrants.status_label') }}" helper="{{ __('admin/registrants.status_helper') }}" required>
            @foreach(['pending', 'active', 'expired', 'suspended', 'pending_transfer', 'transferred_away', 'cancelled', 'redemption', 'terminated'] as $status)
                <option value="{{ $status }}" {{ old('status', $registrant->status) === $status ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </option>
            @endforeach
        </x-admin::select>
        <x-admin::input
            name="registered_at"
            type="date"
            label="{{ __('admin/registrants.registered_label') }}"
            helper="{{ __('admin/registrants.registered_helper') }}"
            value="{{ old('registered_at', $registrant->registered_at?->format('Y-m-d')) }}"
        />
        <x-admin::input
            name="expires_at"
            type="date"
            label="{{ __('admin/registrants.expires_label') }}"
            helper="{{ __('admin/registrants.expires_helper') }}"
            value="{{ old('expires_at', $registrant->expires_at?->format('Y-m-d')) }}"
        />
        <x-admin::input
            name="years"
            type="number"
            label="{{ __('admin/registrants.years_label') }}"
            helper="{{ __('admin/registrants.years_helper') }}"
            value="{{ old('years', $registrant->years) }}"
            min="1"
            required
        />
        <x-admin::input
            name="price"
            type="number"
            step="0.01"
            label="{{ __('admin/registrants.price_label') }}"
            helper="{{ __('admin/registrants.price_helper') }}"
            value="{{ old('price', $registrant->price) }}"
            required
        />
        <x-admin::toggle
            name="auto_renew"
            label="{{ __('admin/registrants.auto_renew_label') }}"
            helper="{{ __('admin/registrants.auto_renew_helper') }}"
            :checked="(bool) old('auto_renew', $registrant->auto_renew)"
        />
    </div>
    <div>
        <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/registrants.nameservers_label') }}</h4>
        <span class="text-slate-500">{{ __('admin/registrants.nameservers_helper') }}</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        @for ($i = 0; $i < 4; $i++)
            <x-admin::input
                name="nameservers[{{ $i }}]"
                type="text"
                label="NS{{ $i + 1 }}"
                placeholder="ns{{ $i + 1 }}.example.com"
                value="{{ old('nameservers.' . $i, $registrant->nameservers[$i] ?? '') }}"
            />
        @endfor
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.registrants') }}"
            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit"
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.update') }}
        </button>
    </div>
</form>
