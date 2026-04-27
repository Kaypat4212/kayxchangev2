<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Profile Settings') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your identity, password, and account security details.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-full bg-emerald-500/10 px-4 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-300 ring-1 ring-emerald-500/20 hover:bg-emerald-500/20">
                <svg class="h-4 w-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/15 via-slate-900 to-slate-950 p-6 sm:p-8">
                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-emerald-400/20 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-12 h-44 w-44 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="relative z-10 grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-300">KayXchange Account</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">Keep your profile secure and up to date</h3>
                        <p class="mt-2 text-sm text-slate-300">Changes here keep your account details accurate for payouts, support, and security checks.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-1 sm:justify-items-end">
                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400">KYC</p>
                            <p class="text-sm font-semibold {{ auth()->user()?->kyc_verified ? 'text-emerald-300' : 'text-amber-300' }}">{{ auth()->user()?->kyc_verified ? 'Verified' : 'Pending' }}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                            <p class="text-[11px] uppercase tracking-wider text-slate-400">Balance</p>
                            <p class="text-sm font-semibold text-cyan-300">₦{{ number_format(auth()->user()?->balance ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800/90 sm:p-8 lg:col-span-2">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- KX Tag card --}}
                <div class="rounded-2xl border border-emerald-200/60 bg-white p-5 shadow-sm dark:border-emerald-800/40 dark:bg-gray-800/90 sm:p-8 lg:col-span-2">
                    <div class="max-w-2xl">
                        <header class="mb-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">KX Tag</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Your KX Tag is your unique username on KayXchange — people use it to send you money instantly.
                                It must be 3–20 characters and may only contain letters, numbers, and underscores.
                            </p>
                        </header>

                        @if(session('status') === 'tag-updated')
                            <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">
                                ✅ KX Tag updated successfully!
                            </div>
                        @endif

                        {{-- Current tag display --}}
                        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-800/50 dark:bg-emerald-950/20">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a0 0 010-4z"/>
                            </svg>
                            <div>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400">Current KX Tag</p>
                                <p class="font-mono text-base font-bold text-emerald-700 dark:text-emerald-300">@{{ $user->kx_tag }}</p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.kx-tag') }}" class="space-y-4">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="kx_tag" value="New KX Tag" />
                                <div class="relative mt-1">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500 select-none">@</span>
                                    <x-text-input
                                        id="kx_tag"
                                        name="kx_tag"
                                        type="text"
                                        class="block w-full pl-7"
                                        :value="old('kx_tag', $user->kx_tag)"
                                        placeholder="e.g. john_doe123"
                                        maxlength="20"
                                        autocomplete="off"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z0-9_]/g,'')"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Letters, numbers and underscores only. 3–20 characters.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('kx_tag')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>Save Tag</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800/90 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="rounded-2xl border border-red-200/60 bg-white p-5 shadow-sm dark:border-red-900/40 dark:bg-gray-800/90 sm:p-8">
                    <div class="max-w-xl">
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-300">
                            Deleting your account is permanent and cannot be undone.
                        </div>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
