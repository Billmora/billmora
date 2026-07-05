@extends('admin::layouts.app')

@section('title', __('admin/update.updating_title'))

@section('body')
    <div class="grid gap-5" x-data="updateProgress()" x-init="startPolling()">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-2xl font-bold text-slate-600">{{ __('admin/update.updating_title') }}</h2>
            <div>
                <template x-if="state === 'running'">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-600">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        {{ __('admin/update.progress.running') }}
                    </span>
                </template>
                <template x-if="state === 'completed'">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase bg-green-100 text-green-600">
                        {{ __('admin/update.progress.completed') }}
                    </span>
                </template>
                <template x-if="state === 'failed' || state === 'stale'">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase bg-red-100 text-red-600">
                        {{ __('admin/update.progress.failed') }}
                    </span>
                </template>
            </div>
        </div>

        {{-- Log Terminal Card --}}
        <div class="bg-white rounded-2xl p-5 sm:p-6 border-2 border-billmora-neutral-100">
            <div class="flex items-start sm:items-center gap-3 mb-4">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 shrink-0">
                    <x-lucide-terminal class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/update.progress.log_title') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/update.progress.log_description') }}</p>
                </div>
            </div>

            {{-- Terminal-style log area --}}
            <div class="bg-slate-900 rounded-xl p-4 sm:p-5 font-mono text-xs sm:text-sm leading-relaxed max-h-96 sm:max-h-[500px] overflow-y-auto border border-slate-700" x-ref="logArea">
                <template x-if="logs.length === 0">
                    <div class="flex items-center gap-2 text-slate-400">
                        <span class="animate-pulse">⏳</span>
                        <span>{{ __('admin/update.progress.waiting') }}</span>
                    </div>
                </template>

                <template x-for="(log, index) in logs" :key="index">
                    <div class="flex items-start gap-2 sm:gap-3 py-0.5">
                        <span class="text-slate-500 shrink-0" x-text="'[' + log.time + ']'"></span>
                        <span :class="{
                            'text-green-400': log.status === 'success',
                            'text-red-400': log.status === 'error',
                            'text-yellow-400': log.status === 'warning',
                            'text-blue-400': log.status === 'running',
                        }">
                            <span x-text="log.status === 'success' ? '✅' : (log.status === 'error' ? '❌' : (log.status === 'warning' ? '⚠️' : '⏳'))"></span>
                            <span x-text="log.message"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Result Alert: Success --}}
        <template x-if="state === 'completed'">
            <x-admin::alert variant="success" title="{{ __('admin/update.progress.success_title') }}">
                <p class="text-sm" x-text="'{{ __('admin/update.progress.success_message') }}'.replace(':version', version || '')"></p>
            </x-admin::alert>
        </template>

        {{-- Result Alert: Failed --}}
        <template x-if="state === 'failed'">
            <x-admin::alert variant="danger" title="{{ __('admin/update.progress.failed_title') }}">
                <p class="text-sm">{{ __('admin/update.progress.failed_message') }}</p>
            </x-admin::alert>
        </template>

        {{-- Result Alert: Stale (process crashed without writing final status) --}}
        <template x-if="state === 'stale'">
            <x-admin::alert variant="danger" title="{{ __('admin/update.progress.stale_title') }}">
                <p class="text-sm">{{ __('admin/update.progress.stale_message') }}</p>
            </x-admin::alert>
        </template>

        {{-- Back Button (always visible) --}}
        <div class="flex justify-start">
            <a href="{{ route('admin.update') }}"
                class="flex items-center gap-2 text-sm font-semibold text-billmora-primary-500 hover:underline transition-colors">
                <x-lucide-arrow-left class="w-4 h-4" />
                {{ __('admin/update.back_to_update') }}
            </a>
        </div>
    </div>

    <script>
        function updateProgress() {
            return {
                logs: [],
                state: 'running',
                version: null,
                pollInterval: null,

                startPolling() {
                    // Fetch immediately on load
                    this.fetchStatus();

                    // Then poll every 1.5 seconds
                    this.pollInterval = setInterval(() => {
                        this.fetchStatus();
                    }, 1500);
                },

                async fetchStatus() {
                    try {
                        const response = await fetch('{{ route("admin.update.status") }}');
                        const data = await response.json();

                        this.state = data.status.state || 'running';
                        this.version = data.status.version || null;
                        this.logs = data.logs || [];

                        // Auto-scroll to bottom
                        this.$nextTick(() => {
                            const el = this.$refs.logArea;
                            if (el) el.scrollTop = el.scrollHeight;
                        });

                        // Stop polling when done
                        if (this.state === 'completed' || this.state === 'failed' || this.state === 'stale') {
                            clearInterval(this.pollInterval);
                        }
                    } catch (e) {
                        // Silently handle fetch errors (e.g. during maintenance mode)
                    }
                },
            };
        }
    </script>
@endsection
