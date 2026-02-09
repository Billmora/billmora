@extends('client::layouts.app')

@section('title', 'Services')

@section('body')
<div class="overflow-x-auto">
    <div class="min-w-full inline-block align-middle">
        <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
            <table class="min-w-full divide-y divide-billmora-2">
                <thead class="bg-billmora-2">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/service.package_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/service.category_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/service.billing_cycle_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/service.price_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('client/service.expires_label') }}</th>
                        <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.status') }}</th>
                        <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-billmora-2 bg-white">
                    @foreach ($services as $service)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                <a href="#" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ $service->name }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->package->catalog->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->cycle_label }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ Currency::format($service->price, $service->currency) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->next_due_date?->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $service->status }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                <a href="#" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ __('common.manage') }}</a>
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