@extends('client::services.show')

@section('workspaces')
<div class="bg-white border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
    <div class="bg-slate-50 px-6 py-4 border-b-2 border-billmora-neutral-100 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-key text-billmora-primary-500"></i>
            <h3 class="font-semibold text-slate-600">SSH Keys</h3>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if($error)
            <x-client::alert variant="danger" title="Cannot Manage SSH Keys">
                <p class="text-sm font-medium">{{ $error }}</p>
            </x-client::alert>
        @else
            <div>
                <p class="text-sm text-slate-500 mb-4">Manage SSH keys authorized to log into the root account of this Virtual Machine. Keys are applied instantly without rebooting.</p>
                
                @if(empty($keys))
                    <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-8 text-center text-slate-500">
                        No SSH keys found in authorized_keys.
                    </div>
                @else
                    <div class="border-2 border-billmora-neutral-100 rounded-xl overflow-hidden">
                        <ul class="divide-y divide-billmora-neutral-100">
                            @foreach($keys as $key)
                                <li class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                                    <div class="overflow-hidden flex-1">
                                        <p class="text-sm font-mono text-slate-600 truncate bg-slate-100 px-2 py-1 rounded">{{ $key }}</p>
                                    </div>
                                    <x-client::modal.trigger modal="remove-key-{{ $loop->index }}" class="text-red-500 hover:text-red-600 p-2 bg-red-50 hover:bg-red-100 rounded-lg transition-colors cursor-pointer" title="Remove Key">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </x-client::modal.trigger>

                                    <x-client::modal.content modal="remove-key-{{ $loop->index }}" variant="danger" title="Remove SSH Key" description="Are you sure you want to remove this SSH key? You will no longer be able to log in using this key.">
                                        <form action="{{ route('client.services.provisioning.handle', ['service' => $service->service_number, 'slug' => 'remove_ssh_key']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="ssh_key" value="{{ $key }}">
                                            <div class="mt-6 flex justify-end gap-3">
                                                <button type="button" x-on:click="$store.modal.close()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors cursor-pointer">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-colors cursor-pointer">Remove Key</button>
                                            </div>
                                        </form>
                                    </x-client::modal.content>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <hr class="border-t-2 border-billmora-neutral-100 my-2">
            <div>   
                <h4 class="font-semibold text-slate-700 mb-3">Add New SSH Key</h4>
                <form action="{{ route('client.services.provisioning.handle', ['service' => $service->service_number, 'slug' => 'add_ssh_key']) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <x-client::textarea name="ssh_key" rows="3" class="font-mono text-sm" placeholder="ssh-rsa AAAAB3Nz... user@device" :required="true" />
                    </div>
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm cursor-pointer">
                        <i class="fa-solid fa-plus mr-1.5"></i> Add Key
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
