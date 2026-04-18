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
