@extends('admin::layouts.app')

@section('title', 'Ticket Create')

@section('body')
<form action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data"
    class="flex flex-col gap-5"
    x-data="{
        attachments: [{ id: 1 }],
        nextId: 2,
        selectedUser: {{ old('ticket_user_id') ? (int) old('ticket_user_id') : 'null' }},
        allServices: @js($services->map(fn($s) => [
            'value' => $s->id,
            'label' => $s->name,
            'status' => $s->status,
            'user_id' => $s->user_id,
        ])->toArray()),
        filteredServices() {
            if (!this.selectedUser) return [];
            return this.allServices.filter(s => s.user_id === this.selectedUser);
        },
        add() {
            this.attachments.push({ id: this.nextId++ });
        },
        remove(id) {
            if (this.attachments.length > 1) {
                this.attachments = this.attachments.filter(a => a.id !== id);
            }
        }
    }"
    x-on:picked.window="if ($event.detail.name === 'ticket_user_id') selectedUser = $event.detail.value"
>
    @csrf
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::singleselect 
                name="ticket_user_id"
                label="{{ __('admin/tickets.ticket_user_label') }}"
                helper="{{ __('admin/tickets.ticket_user_helper') }}"
                :options="$userOptions"
                :selected="old('ticket_user_id')"
                required
            />
            <x-admin::select
                name="ticket_status"
                label="{{ __('admin/tickets.ticket_status_label') }}"
                helper="{{ __('admin/tickets.ticket_status_helper') }}"
                required
            >
                @foreach (['open', 'answered', 'replied', 'closed', 'on_hold', 'in_progress'] as $status)
                    <option value="{{ $status }}" {{ old('ticket_status', 'open') === $status ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
            </x-admin::select>
            <x-admin::select
                name="ticket_priority"
                label="{{ __('admin/tickets.ticket_priority_label') }}"
                helper="{{ __('admin/tickets.ticket_priority_helper') }}"
                required
            >
                @foreach (['low', 'normal', 'medium', 'high'] as $priority)
                    <option value="{{ $priority }}" {{ old('ticket_priority', 'normal') === $priority ? 'selected' : '' }}>
                        {{ ucwords($priority) }}
                    </option>
                @endforeach
            </x-admin::select>

            <x-admin::select
                name="ticket_department"
                label="{{ __('admin/tickets.ticket_department_label') }}"
                helper="{{ __('admin/tickets.ticket_department_helper') }}"
            >
                @foreach (Billmora::getTicket('ticketing_departments') as $department)
                    <option value="{{ $department }}" {{ old('ticket_department') === $department ? 'selected' : '' }}>
                        {{ ucwords($department) }}
                    </option>
                @endforeach
            </x-admin::select>
            <x-admin::singleselect 
                name="ticket_assigned_id"
                label="{{ __('admin/tickets.ticket_assigned_label') }}"
                helper="{{ __('admin/tickets.ticket_assigned_helper') }}"
                :options="$assignedOptions"
            />
            <x-admin::select
                name="ticket_service_id"
                label="{{ __('admin/tickets.ticket_service_label') }}"
                helper="{{ __('admin/tickets.ticket_service_helper') }}"
            >
                <option value="" selected>None</option>
                <template x-if="selectedUser && filteredServices().length > 0">
                    <template x-for="service in filteredServices()" :key="service.value">
                        <option
                            :value="service.value"
                            :selected="service.value == '{{ old('ticket_service_id') }}'"
                            x-text="service.label + ' (' + service.status + ')'"
                        ></option>
                    </template>
                </template>
            </x-admin::select>
        </div>
        <x-admin::input 
            name="ticket_subject"
            label="{{ __('admin/tickets.ticket_subject_label') }}"
            helper="{{ __('admin/tickets.ticket_subject_helper') }}"
            required
        />
        <x-admin::editor.text 
            name="ticket_message"
            label="{{ __('admin/tickets.ticket_message_label') }}"
            helper="{{ __('admin/tickets.ticket_message_helper') }}"
            required
        >{{ old('ticket_message') }}</x-admin::editor.text>
        <button 
            type="button" 
            x-on:click="add()"
            class="bg-billmora-primary hover:bg-billmora-primary-hover px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer"
        >
            {{ __('admin/tickets.ticket_add_attachments') }}
        </button>
        <template x-for="attachment in attachments" :key="attachment.id">
            <div class="flex items-center gap-3">
                <x-admin::input
                    name="ticket_attachments[]"
                    label="{{ __('admin/tickets.ticket_attachments_label') }}"
                    helper="{{ __('admin/tickets.ticket_attachments_helper') }}"
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
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.tickets') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.create') }}
        </button>
    </div>
</form>
@endsection
