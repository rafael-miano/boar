<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recent Births Success Banner -->
        @if($this->recentBirths->count() > 0)
            <x-filament::section class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <x-heroicon-o-gift class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" />
                        <div>
                            <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">
                                Recent Successful Births
                            </h3>
                            <p class="text-green-700 dark:text-green-300">
                                {{ $this->recentBirths->count() }} recent birth{{ $this->recentBirths->count() > 1 ? 's' : '' }} confirmed from your boars!
                            </p>
                        </div>
                    </div>
                    <x-filament::button
                        color="success"
                        size="sm"
                        tag="a"
                        href="{{ \App\Filament\Resources\ApprovedReservationResource::getUrl('index') }}"
                    >
                        View All Services
                    </x-filament::button>
                </div>
                
                <!-- Recent Births List -->
                <div class="mt-4 space-y-2">
                    @foreach($this->recentBirths as $birth)
                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-green-200 dark:border-green-700">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                    <x-heroicon-o-gift class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $birth->boar->boar_name }} × {{ $birth->user->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Piglets born: {{ $birth->piglet_count ?? 'N/A' }} • {{ $birth->birth_confirmed_at->format('M j, Y') }}
                                    </p>
                                </div>
                            </div>
                            <x-filament::badge color="success" size="sm">
                                Confirmed
                            </x-filament::badge>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

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
            <x-filament::section class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-red-600 dark:text-red-400" />
                        <div>
                            <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
                                Account needs settlement
                            </h3>
                            <p class="text-red-700 dark:text-red-300">
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
                    >
                        View settlement
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        <!-- Approved services to fulfill (after admin approval) -->
        @if($this->pendingServices->count() > 0)
            <x-filament::section class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <x-heroicon-o-clock class="w-8 h-8 text-amber-600 dark:text-amber-400 flex-shrink-0" />
                        <div>
                            <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200">
                                Services to Fulfill
                            </h3>
                            <p class="text-amber-700 dark:text-amber-300">
                                You have {{ $this->pendingServices->count() }} boar reservation request{{ $this->pendingServices->count() > 1 ? 's' : '' }} to fulfill.
                            </p>
                        </div>
                    </div>
                    <x-filament::button
                        color="warning"
                        size="sm"
                        tag="a"
                        href="{{ \App\Filament\Resources\ApprovedReservationResource::getUrl('index') }}"
                    >
                        View Boar Reservation Requests
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
