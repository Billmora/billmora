@extends('client::layouts.app')

@section('title', 'Ticket Create')

@section('body')
<form action="{{ route('client.tickets.store') }}" method="POST" enctype="multipart/form-data"
    class="flex flex-col gap-5"
    x-data="{
        attachments: [{ id: 1 }],
        nextId: 2,
        add() {
            this.attachments.push({ id: this.nextId++ });
        },
        remove(id) {
            if (this.attachments.length > 1) {
                this.attachments = this.attachments.filter(a => a.id !== id);
            }
        }
    }"
>
    @csrf
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-client::select
                name="ticket_priority"
                label="{{ __('client/tickets.ticket_priority_label') }}"
                required
            >
                @foreach (['low', 'normal', 'medium', 'high'] as $priority)
                    <option value="{{ $priority }}" {{ old('ticket_priority', 'normal') === $priority ? 'selected' : '' }}>
                        {{ ucwords($priority) }}
                    </option>
                @endforeach
            </x-client::select>

            <x-client::select
                name="ticket_department"
                label="{{ __('client/tickets.ticket_department_label') }}"
                required
            >
                @foreach (Billmora::getTicket('ticketing_departments') as $department)
                    <option value="{{ $department }}" {{ old('ticket_department') === $department ? 'selected' : '' }}>
                        {{ ucwords($department) }}
                    </option>
                @endforeach
            </x-client::select>
            <x-client::select
                name="ticket_service_id"
                label="{{ __('client/tickets.ticket_service_label') }}"
            >
                <option value="" selected>None</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }} - ({{ ucwords($service->status) }})</option>
                @endforeach
            </x-client::select>
        </div>
        <x-client::input 
            name="ticket_subject"
            label="{{ __('client/tickets.ticket_subject_label') }}"
            required
        />
        <x-client::editor.text 
            name="ticket_message"
            label="{{ __('client/tickets.ticket_message_label') }}"
            required
        >{{ old('ticket_message') }}</x-client::editor.text>
        <button 
            type="button" 
            x-on:click="add()"
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('client/tickets.ticket_add_attachments') }}
        </button>
        <template x-for="attachment in attachments" :key="attachment.id">
            <div class="flex items-end gap-3">
                <x-client::input
                    name="ticket_attachments[]"
                    label="{{ __('client/tickets.ticket_attachments_label') }}"
                    type="file"
                    accept="{{ Billmora::getGeneral('ticket_allowed_attachment_types') }}"
                />
                <button
                    type="button"
                    x-on:click="remove(attachment.id)"
                    x-show="attachments.length > 1"
                    class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                    title="{{ __('common.remove') }}"
                >
                    {{ __('common.remove') }}
                </button>
            </div>
        </template>
        <x-client::captcha form="ticket_form" class="mt-4 mx-auto" />
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('client.tickets') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.create') }}
        </button>
    </div>
</form>
@endsection
