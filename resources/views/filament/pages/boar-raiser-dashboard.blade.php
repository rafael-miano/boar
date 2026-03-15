<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <x-filament::section>
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Welcome, {{ auth()->user()->name }}!
                </h1>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage your boars and boar reservation requests from your dashboard.
                </p>
            </div>
        </x-filament::section>

        @if($this->unpaidPlatformFeeTotal > 500)
            <x-filament::section class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 overflow-hidden">
                <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-red-600 dark:text-red-400 flex-shrink-0" />
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
                                Account needs settlement
                            </h3>
                            <p class="text-red-700 dark:text-red-300 mt-0.5">
                                You have unpaid platform fees of
                                <span class="font-semibold">₱{{ number_format($this->unpaidPlatformFeeTotal, 2) }}</span>.
                                Please settle at least ₱500 in platform fees before adding new boars.
                            </p>
                        </div>
                    </div>
                    <x-filament::button
                        color="danger"
                        size="sm"
                        tag="a"
                        href="{{ route('filament.admin.pages.settlement') }}"
                        class="w-full sm:w-auto shrink-0"
                    >
                        View settlement
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
