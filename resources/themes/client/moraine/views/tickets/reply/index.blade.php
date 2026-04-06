@extends('client::layouts.app')

@section('title', "Ticket Reply - {$ticket->ticket_number}")

@section('body')
    <div class="flex flex-col gap-5">
        @if ($ticket->status === 'closed')
            <x-client::alert variant="warning" title="{{ __('client/tickets.ticket_closed_hint') }}" />
        @endif
        <div class="flex flex-col-reverse md:flex-row gap-4">
            <div class="min-h-dvh w-full md:w-5/7 flex flex-col gap-8">
                <div x-data x-ref="chatContainer"
                    x-init="$nextTick(() => { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight })"
                    class="max-h-dvh w-full flex flex-col gap-4 overflow-y-auto">
                    @foreach ($ticket->messages as $message)
                        @if (!$message->is_staff_reply)
                            <div class="flex gap-4 ml-auto">
                                <div
                                    class="w-auto md:min-w-100 grid gap-2 bg-billmora-bg p-5 wrap-break-word border-2 border-billmora-2 rounded-2xl rounded-tr-none">
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
                                                        <span
                                                            class="text-sm text-slate-600 group-hover:text-white font-medium truncate max-w-40">
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
                                    </div>
                                </div>
                                <img src="{{ $message->user->avatar }}" alt="User Avatar" class="w-10 h-10 rounded-full">
                            </div>
                        @else
                            <div class="flex gap-4 mr-auto">
                                <img src="{{ $message->user->avatar }}" alt="User Avatar" class="w-10 h-10 rounded-full">
                                <div
                                    class="w-auto md:min-w-100 grid gap-2 bg-billmora-bg p-5 wrap-break-word border-2 border-billmora-2 rounded-2xl rounded-tl-none">
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
                                                        <span
                                                            class="text-sm text-slate-600 group-hover:text-white font-medium truncate max-w-40">
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
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <form action="{{ route('client.tickets.reply.send', ['ticket' => $ticket->ticket_number]) }}" method="POST"
                    enctype="multipart/form-data" id="formMessage"
                    class="flex flex-col gap-4 bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl" x-data="{
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
                    <x-client::editor.text name="ticket_message" label="{{ __('client/tickets.ticket_message_label') }}"
                        required>{{ old('ticket_message') }}</x-client::editor.text>
                    <button type="button" x-on:click="add()"
                        class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 ml-auto text-white rounded-lg transition-colors duration-150 cursor-pointer">
                        {{ __('client/tickets.ticket_add_attachments') }}
                    </button>
                    <template x-for="attachment in attachments" :key="attachment.id">
                        <div class="flex items-end gap-3">
                            <x-client::input name="ticket_attachments[]"
                                label="{{ __('client/tickets.ticket_attachments_label') }}" type="file"
                                accept="{{ Billmora::getGeneral('ticket_allowed_attachment_types') }}"
                                error="{{ $errors->first('ticket_attachments.*') }}" />
                            <button type="button" x-on:click="remove(attachment.id)" x-show="attachments.length > 1"
                                class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
                                title="{{ __('common.remove') }}">
                                {{ __('common.remove') }}
                            </button>
                        </div>
                    </template>
                    <x-client::captcha form="ticket_form" class="mt-4 mx-auto" />
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
                        {{ __('client/tickets.ticket_information') }}
                    </h3>
                    <div class="bg-billmora-bg p-4 border-2 border-billmora-2 rounded-2xl">
                        <div class="grid">
                            <h4 class="text-sm text-slate-400 font-semibold">{{ __('client/tickets.ticket_subject_label') }}
                            </h4>
                            <span class="text-slate-500 font-medium">{{ $ticket->subject }}</span>
                        </div>
                        <hr class="border-t-2 border-billmora-2 my-2">
                        <div class="grid">
                            <h4 class="text-sm text-slate-400 font-semibold">{{ __('client/tickets.ticket_status_label') }}
                            </h4>
                            <span
                                class="text-slate-500 font-medium">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
                        </div>
                        @if ($ticket->status === 'open')
                            <hr class="border-t-2 border-billmora-2 my-2">
                            <div class="grid">
                                <h4 class="text-sm text-slate-400 font-semibold">
                                    {{ __('client/tickets.ticket_opened_at_label') }}</h4>
                                <span class="text-slate-500 font-medium">
                                    {{ $ticket->created_at->format(Billmora::getGeneral('company_date_format')) }}
                                    ({{ $ticket->created_at->format('g:i A') }})
                                </span>
                            </div>
                        @elseif ($ticket->status === 'closed')
                            <hr class="border-t-2 border-billmora-2 my-2">
                            <div class="grid">
                                <h4 class="text-sm text-slate-400 font-semibold">
                                    {{ __('client/tickets.ticket_closed_at_label') }}</h4>
                                <span class="text-slate-500 font-medium">
                                    {{ $ticket->closed_at->format(Billmora::getGeneral('company_date_format')) }}
                                    ({{ $ticket->closed_at->format('g:i A') }})
                                </span>
                            </div>
                        @elseif ($ticket->status === 'replied')
                            <hr class="border-t-2 border-billmora-2 my-2">
                            <div class="grid">
                                <h4 class="text-sm text-slate-400 font-semibold">
                                    {{ __('client/tickets.ticket_replied_at_label') }}</h4>
                                <span class="text-slate-500 font-medium">
                                    {{ $ticket->last_reply_at->format(Billmora::getGeneral('company_date_format')) }}
                                    ({{ $ticket->last_reply_at->format('g:i A') }})
                                </span>
                            </div>
                        @elseif ($ticket->status === 'answered')
                            <hr class="border-t-2 border-billmora-2 my-2">
                            <div class="grid">
                                <h4 class="text-sm text-slate-400 font-semibold">
                                    {{ __('client/tickets.ticket_answered_at_label') }}</h4>
                                <span class="text-slate-500 font-medium">
                                    {{ $ticket->last_reply_at->format(Billmora::getGeneral('company_date_format')) }}
                                    ({{ $ticket->last_reply_at->format('g:i A') }})
                                </span>
                            </div>
                        @endif
                        <hr class="border-t-2 border-billmora-2 my-2">
                        <div class="grid">
                            <h4 class="text-sm text-slate-400 font-semibold">
                                {{ __('client/tickets.ticket_priority_label') }}</h4>
                            <span class="text-slate-500 font-medium">{{ ucwords($ticket->priority) }}</span>
                        </div>
                        <hr class="border-t-2 border-billmora-2 my-2">
                        <div class="grid">
                            <h4 class="text-sm text-slate-400 font-semibold">
                                {{ __('client/tickets.ticket_department_label') }}</h4>
                            <span class="text-slate-500 font-medium">{{ ucwords($ticket->department ?? '-') }}</span>
                        </div>
                        <hr class="border-t-2 border-billmora-2 my-2">
                        <div class="grid">
                            <h4 class="text-sm text-slate-400 font-semibold">{{ __('client/tickets.ticket_service_label') }}
                            </h4>
                            <span class="text-slate-500 font-medium">
                                @if ($ticket->service)
                                    <a href="{{ route('client.services.show', ['service' => $ticket->service->id]) }}">
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
                        @if (Billmora::getTicket('ticketing_allow_client_close') && $ticket->status !== 'closed')
                            <x-client::modal.trigger modal="closeModal"
                                class="w-full bg-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                {{ __('common.close') }}
                            </x-client::modal.trigger>
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
                        {{ __('client/tickets.ticket_attachments') }}
                    </h3>
                    @foreach ($ticket->messages as $message)
                        @foreach ($message->attachments as $attachment)
                            <a href="{{ Storage::url($attachment->file_path) }}"
                                class="flex items-center gap-2 bg-billmora-bg hover:bg-billmora-primary-500 border-2 border-billmora-2 hover:border-billmora-primary-500 px-3 py-2 rounded-lg transition group">
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
        @if (Billmora::getTicket('ticketing_allow_client_close') && $ticket->status !== 'closed')
            <x-client::modal.content modal="closeModal" variant="danger" size="xl" position="centered"
                title="{{ __('common.confirm_modal_title') }}"
                description="{{ __('common.confirm_modal_description', ['item' => $ticket->ticket_number]) }}">
                <form action="{{ route('client.tickets.close', ['ticket' => $ticket->ticket_number]) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-client::modal.trigger type="button" variant="close"
                            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.cancel') }}
                        </x-client::modal.trigger>
                        <button type="submit"
                            class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.delete') }}
                        </button>
                    </div>
                </form>
            </x-client::modal.content>
        @endif
    </div>
@endsection