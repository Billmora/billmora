@extends('admin::layouts.app')

@section('title', "Ticket Reply - {$ticket->ticket_number}")

@section('body')
    <div class="flex flex-col-reverse md:flex-row gap-4">
        <div class="min-h-dvh w-full md:w-5/7 flex flex-col gap-8">
            <div x-data x-ref="chatContainer"
                x-init="$nextTick(() => { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight })"
                class="max-h-dvh w-full flex flex-col gap-4 overflow-y-auto">
                @foreach ($ticket->messages as $message)
                    @if ($message->is_staff_reply)
                        <div class="flex gap-4 ml-auto">
                            <div
                                class="w-auto md:min-w-100 grid gap-2 bg-white p-5 wrap-break-word border-2 border-billmora-2 rounded-2xl rounded-tr-none">
                                <div class="grid">
                                    <span class="text-slate-500 font-semibold">
                                        {{ $message->user->first_name }}
                                    </span>
                                    <span class="text-sm text-slate-400">
                                        @if ($message->user->is_root_admin)
                                            Administrator
                                        @else
                                            {{ $message->user->roles->pluck('name')->implode(', ') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="tiptap-content">{!! $message->message !!}</div>
                                @if ($message->attachments->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($message->attachments as $attachment)
                                            @if (str_starts_with($attachment->mime_type, 'image/'))
                                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="block">
                                                    <img src="{{ Storage::url($attachment->file_path) }}" alt="{{ $attachment->file_name }}"
                                                        class="max-h-40 max-w-60 rounded-lg border-2 border-billmora-2 object-cover hover:opacity-80 transition">
                                                </a>
                                            @else
                                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                                    download="{{ $attachment->file_name }}"
                                                    class="flex items-center gap-2 bg-billmora-1 hover:bg-billmora-primary-500 border-2 border-billmora-2 hover:border-billmora-primary-500 px-3 py-2 rounded-lg transition group">
                                                    <x-lucide-link class="w-4 h-auto text-slate-500 group-hover:text-white shrink-0" />
                                                    <span class="text-sm text-slate-600 group-hover:text-white font-medium truncate max-w-40">
                                                        {{ $attachment->file_name }}
                                                    </span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="flex gap-2 justify-between">
                                    <span class="text-sm text-slate-400 font-semibold">
                                        {{ $message->created_at->format(Billmora::getGeneral('company_date_format')) }}
                                        ({{ $message->created_at->format('g:i A') }})
                                    </span>
                                    @can('tickets.reply')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $message->id }}" variant="open"
                                            class="inline-flex items-center ml-auto text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">
                                            {{ __('common.delete') }}
                                        </x-admin::modal.trigger>
                                    @endcan
                                </div>
                            </div>
                            <img src="{{ $message->user->avatar }}" alt="User Avatar" class="w-10 h-10 rounded-full">
                        </div>
                    @else
                        <div class="flex gap-4 mr-auto">
                            <img src="{{ $message->user->avatar }}" alt="User Avatar" class="w-10 h-10 rounded-full">
                            <div
                                class="w-auto md:min-w-100 grid gap-2 bg-white p-5 wrap-break-word border-2 border-billmora-2 rounded-2xl rounded-tl-none">
                                <div class="grid">
                                    <span class="text-slate-500 font-semibold">
                                        {{ $message->user->first_name }}
                                    </span>
                                    <span class="text-sm text-slate-400">
                                        Client
                                    </span>
                                </div>
                                <div class="tiptap-content">{!! $message->message !!}</div>
                                @if ($message->attachments->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($message->attachments as $attachment)
                                            @if (str_starts_with($attachment->mime_type, 'image/'))
                                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="block">
                                                    <img src="{{ Storage::url($attachment->file_path) }}" alt="{{ $attachment->file_name }}"
                                                        class="max-h-40 max-w-60 rounded-lg border-2 border-billmora-2 object-cover hover:opacity-80 transition">
                                                </a>
                                            @else
                                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                                    download="{{ $attachment->file_name }}"
                                                    class="flex items-center gap-2 bg-billmora-1 hover:bg-billmora-primary-500 border-2 border-billmora-2 hover:border-billmora-primary-500 px-3 py-2 rounded-lg transition group">
                                                    <x-lucide-link class="w-4 h-auto text-slate-500 group-hover:text-white shrink-0" />
                                                    <span class="text-sm text-slate-600 group-hover:text-white font-medium truncate max-w-40">
                                                        {{ $attachment->file_name }}
                                                    </span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="flex gap-2 justify-between">
                                    <span class="text-sm text-slate-400 font-semibold">
                                        {{ $message->created_at->format(Billmora::getGeneral('company_date_format')) }}
                                        ({{ $message->created_at->format('g:i A') }})
                                    </span>
                                    @can('tickets.reply')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $message->id }}" variant="open"
                                            class="inline-flex items-center ml-auto text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">
                                            {{ __('common.delete') }}
                                        </x-admin::modal.trigger>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <form action="{{ route('admin.tickets.reply.send', ['ticket' => $ticket->id]) }}" method="POST"
                enctype="multipart/form-data" id="formMessage"
                class="flex flex-col gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl" x-data="{
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
                }">
                @csrf
                <x-admin::editor.text name="ticket_message" label="{{ __('admin/tickets.ticket_message_label') }}"
                    helper="{{ __('admin/tickets.ticket_message_helper') }}"
                    required>{{ old('ticket_message') }}</x-admin::editor.text>
                <button type="button" x-on:click="add()"
                    class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer">
                    {{ __('admin/tickets.ticket_add_attachments') }}
                </button>
                <template x-for="attachment in attachments" :key="attachment.id">
                    <div class="flex items-center gap-3">
                        <x-admin::input name="ticket_attachments[]"
                            label="{{ __('admin/tickets.ticket_attachments_label') }}"
                            helper="{{ __('admin/tickets.ticket_attachments_helper') }}" type="file"
                            accept="{{ Billmora::getGeneral('ticket_allowed_attachment_types') }}"
                            error="{{ $errors->first('ticket_attachments.*') }}" />
                        <button type="button" x-on:click="remove(attachment.id)" x-show="attachments.length > 1"
                            class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                            title="{{ __('common.remove') }}">
                            {{ __('common.remove') }}
                        </button>
                    </div>
                </template>
                <div class="flex gap-4 ml-auto">
                    <button type="submit"
                        class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.send') }}
                    </button>
                </div>
            </form>
        </div>
        <div class="w-full md:w-2/7 h-fit grid gap-4 md:sticky top-28 right-0 shrink-0">
            <div class="grid gap-2">
                <h3 class="text-lg text-slate-600 font-semibold">
                    {{ __('admin/tickets.ticket_information') }}
                </h3>
                <div class="bg-white p-4 border-2 border-billmora-2 rounded-2xl">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_user_label') }}</h4>
                        <a href="{{ route('admin.users.summary', ['user' => $ticket->user->id]) }}"
                            class="text-slate-500 font-medium">
                            {{ $ticket->user->fullname }}
                        </a>
                    </div>
                    <hr class="border-t-2 border-billmora-2 my-2">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_subject_label') }}</h4>
                        <span class="text-slate-500 font-medium">{{ $ticket->subject }}</span>
                    </div>
                    <hr class="border-t-2 border-billmora-2 my-2">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_status_label') }}</h4>
                        <span
                            class="text-slate-500 font-medium">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
                    </div>
                    <hr class="border-t-2 border-billmora-2 my-2">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_priority_label') }}
                        </h4>
                        <span class="text-slate-500 font-medium">{{ ucwords($ticket->priority) }}</span>
                    </div>
                    <hr class="border-t-2 border-billmora-2 my-2">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_department_label') }}
                        </h4>
                        <span class="text-slate-500 font-medium">{{ ucwords($ticket->department ?? '-') }}</span>
                    </div>
                    <hr class="border-t-2 border-billmora-2 my-2">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_assigned_label') }}
                        </h4>
                        <span class="text-slate-500 font-medium">{{ $ticket->assignedTo->fullname ?? '-' }}</span>
                    </div>
                    <hr class="border-t-2 border-billmora-2 my-2">
                    <div class="grid">
                        <h4 class="text-sm text-slate-400 font-semibold">{{ __('admin/tickets.ticket_service_label') }}</h4>
                        <span class="text-slate-500 font-medium">
                            @if ($ticket->service)
                                <a href="{{ route('admin.services.edit', ['service' => $ticket->service->id]) }}">
                                    {{ $ticket->service->name }} - {{ ucwords($ticket->service->status) }}
                                </a>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
                <div class="flex gap-2 text-center">
                    <button type="button"
                        onclick="document.getElementById('formMessage').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="w-full bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.reply') }}
                    </button>
                    @if ($ticket->status !== 'closed')
                        <x-admin::modal.trigger modal="closeModal"
                            class="w-full bg-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.close') }}
                        </x-admin::modal.trigger>
                    @else
                        <button type="button"
                            class="w-full bg-red-400 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-not-allowed">
                            {{ __('common.close') }}
                        </button>
                    @endif
                </div>
            </div>
            <div class="grid gap-2">
                <h3 class="text-lg text-slate-600 font-semibold">
                    {{ __('admin/tickets.ticket_attachments') }}
                </h3>
                @foreach ($ticket->messages as $message)
                    @foreach ($message->attachments as $attachment)
                        <a href="{{ Storage::url($attachment->file_path) }}"
                            class="flex items-center gap-2 bg-white hover:bg-billmora-primary-500 border-2 border-billmora-2 hover:border-billmora-primary-500 px-3 py-2 rounded-lg transition group">
                            <x-lucide-download class="w-4 h-auto text-slate-500 group-hover:text-white" />
                            <span class="text-slate-500 group-hover:text-white font-medium">
                                {{ $attachment->file_name }}
                            </span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
    @foreach ($ticket->messages as $message)
        <x-admin::modal.content modal="deleteModal-{{ $message->id }}" variant="danger" size="xl" position="centered"
            title="{{ __('common.confirm_modal_title') }}"
            description="{{ __('common.confirm_modal_description', ['item' => __('admin/tickets.ticket_message_label')]) }}">
            <form action="{{ route('admin.tickets.reply.destroy', ['ticket' => $ticket->id, 'message' => $message->id]) }}"
                method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close"
                        class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.cancel') }}
                    </x-admin::modal.trigger>
                    <button type="submit"
                        class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.delete') }}
                    </button>
                </div>
            </form>
        </x-admin::modal.content>
    @endforeach
    @if ($ticket->status !== 'closed')
        <x-admin::modal.content modal="closeModal" variant="danger" size="xl" position="centered"
            title="{{ __('common.confirm_modal_title') }}"
            description="{{ __('common.confirm_modal_description', ['item' => $ticket->ticket_number]) }}">
            <form action="{{ route('admin.tickets.close', ['ticket' => $ticket->id]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close"
                        class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.cancel') }}
                    </x-admin::modal.trigger>
                    <button type="submit"
                        class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.delete') }}
                    </button>
                </div>
            </form>
        </x-admin::modal.content>
    @endif
@endsection