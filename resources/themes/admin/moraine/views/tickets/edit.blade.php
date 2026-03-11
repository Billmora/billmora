@extends('admin::layouts.app')

@section('title', "Ticket Edit - {$ticket->ticket_number}")

@section('body')
<form action="{{ route('admin.tickets.update', ['ticket' => $ticket->id]) }}" method="POST"
    class="flex flex-col gap-5"
    x-data="{
        attachments: [{ id: 1 }],
        nextId: 2,
        selectedUser: {{ old('ticket_user_id', $ticket->user_id) }},
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
    @method('PUT')
    <div class="flex flex-col gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin::singleselect 
                name="ticket_user_id"
                label="{{ __('admin/tickets.ticket_user_label') }}"
                helper="{{ __('admin/tickets.ticket_user_helper') }}"
                :options="$users->map(fn($user) => [
                    'value' => $user->id,
                    'title' => $user->email,
                    'subtitle' => $user->fullname
                ])->toArray()"
                :selected="(int) old('ticket_user_id', $ticket->user_id)"
                required
            />
            <x-admin::select
                name="ticket_status"
                label="{{ __('admin/tickets.ticket_status_label') }}"
                helper="{{ __('admin/tickets.ticket_status_helper') }}"
                required
            >
                @foreach (['open', 'answered', 'replied', 'closed', 'on_hold', 'in_progress'] as $status)
                    <option value="{{ $status }}" {{ old('ticket_status', $ticket->status) === $status ? 'selected' : '' }}>
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
                    <option value="{{ $priority }}" {{ old('ticket_priority', $ticket->priority) === $priority ? 'selected' : '' }}>
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
                    <option value="{{ $department }}" {{ old('ticket_department', $ticket->department) === $department ? 'selected' : '' }}>
                        {{ ucwords($department) }}
                    </option>
                @endforeach
            </x-admin::select>
            <x-admin::singleselect 
                name="ticket_assigned_id"
                label="{{ __('admin/tickets.ticket_assigned_label') }}"
                helper="{{ __('admin/tickets.ticket_assigned_helper') }}"
                :options="$assigneds->map(fn($assigned) => [
                    'value' => $assigned->id,
                    'title' => $assigned->email,
                    'subtitle' => $assigned->fullname
                ])->toArray()"
                :selected="old('ticket_assigned_id', $ticket->assigned_to)"
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
                            :selected="service.value == '{{ old('ticket_service_id', $ticket->service_id) }}'"
                            x-text="service.label + ' (' + service.status + ')'"
                        ></option>
                    </template>
                </template>
            </x-admin::select>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.tickets') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    </div>
</form>
@endsection
