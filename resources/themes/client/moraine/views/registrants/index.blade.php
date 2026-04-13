@extends('client::layouts.app')

@section('title', 'Registered Domains')

@section('body')
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                {{ __('client/registrants.registrant_number_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                {{ __('client/registrants.domain_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                {{ __('client/registrants.registration_date_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                {{ __('client/registrants.expires_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                {{ __('common.status') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">
                                {{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-billmora-bg">
                        @forelse ($registrants as $registrant)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('client.registrants.show', ['registrant' => $registrant->registrant_number]) }}"
                                        class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">{{ $registrant->registrant_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $registrant->domain }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $registrant->registered_at?->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $registrant->expires_at?->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $registrant->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('client.registrants.show', ['registrant' => $registrant->registrant_number]) }}"
                                        class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">{{ __('common.manage') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">{{ __('common.no_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4">
        {{ $registrants->links('client::layouts.partials.pagination') }}
    </div>
@endsection