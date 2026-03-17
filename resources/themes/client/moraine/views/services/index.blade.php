@extends('client::layouts.app')

@section('title', 'Services')

@section('body')
<div class="overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead class="bg-billmora-2">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/services.service_number') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/services.package_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/services.billing_cycle_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/services.price_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/services.expires_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                        <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-billmora-2 bg-billmora-bg">
                    @foreach ($services as $service)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                <a href="{{ route('client.services.show', ['service' => $service->service_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">{{ $service->service_number }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->cycle_label }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($service->price, $service->currency) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->next_due_date?->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->status }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                <a href="{{ route('client.services.show', ['service' => $service->service_number]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">{{ __('common.manage') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>
    {{ $services->links('client::layouts.partials.pagination') }}
</div>
@endsection