<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Overdue Birth Reminder Banner -->
        @if($this->overdueBirths->count() > 0)
            <x-filament::section class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3" />
                        <div>
                            <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200">
                                Birth Confirmation Reminder
                            </h3>
                            <p class="text-amber-700 dark:text-amber-300">
                                You have {{ $this->overdueBirths->count() }} overdue birth confirmation{{ $this->overdueBirths->count() > 1 ? 's' : '' }} to complete.
                            </p>
                        </div>
                    </div>
                    <x-filament::button
                        color="warning"
                        size="sm"
                        tag="a"
                        href="{{ \App\Filament\Customer\Resources\BoarReservationResource::getUrl('index') }}"
                    >
                        View Requests
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        <!-- Search and Filter Section -->
        <x-filament::section>
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">MarketPlace</h1>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Browse and discover quality boars from trusted breeders</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <div class="w-full sm:flex-1">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            placeholder="Search boars by name, type, or breeder..."
                            class="w-full"
                            wire:model.live.debounce.300ms="search"
                        />
                    </x-filament::input.wrapper>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 w-full sm:w-auto">
                    <x-filament::input.wrapper class="w-full sm:w-48">
                        <x-filament::input.select class="w-full" wire:model.live="type">
                            <option value="">All Types</option>
                            <option value="pietrain">Pietrain</option>
                            <option value="large-white">Large White</option>
                            <option value="duroc">Duroc</option>
                            <option value="other">Other</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <!-- Boars Grid -->
        <x-filament::grid :default="1" :md="4" class="gap-4 md:gap-5 lg:gap-6">
            @forelse($this->boars as $boar)
                <x-filament::grid.column wire:key="boar-{{ $boar->id }}">
                <x-filament::section class="overflow-hidden flex flex-col h-full p-4 min-h-[440px]">
                    <!-- Boar Image Container - Fixed Height -->
                    <div class="w-full bg-gray-200 overflow-hidden" style="height: 180px; min-height: 180px; max-height: 180px;">
                        @if($boar->boar_picture)
                            <img src="{{ asset('storage/' . $boar->boar_picture) }}"
                                 alt="{{ $boar->boar_name }}"
                                 class="w-full h-full object-cover object-center"
                                 style="width: 100%; height: 180px; min-height: 180px; max-height: 180px; object-fit: cover; object-position: center;">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center" style="height: 180px; min-height: 180px; max-height: 180px;">
                                <x-heroicon-o-photo class="w-16 h-16 text-gray-400" />
                            </div>
                        @endif
                    </div>

                    <!-- Boar Details -->
                    <div class="flex flex-col flex-grow">
                        <div class="flex items-start justify-between mb-2 mt-3">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $boar->boar_name }}</h3>
                            <x-filament::badge
                                color="{{ $boar->health_status === 'healthy' ? 'success' : ($boar->health_status === 'sick' ? 'danger' : ($boar->health_status === 'injured' ? 'warning' : 'gray')) }}"
                                size="xs"
                            >
                                {{ ucfirst($boar->health_status) }}
                            </x-filament::badge>
                        </div>

                        {{-- Star Rating --}}
                        @php
                            $avg   = round($boar->ratings_avg_rating ?? 0, 1);
                            $count = (int) ($boar->ratings_count ?? 0);
                            $full  = (int) floor($avg);
                            $half  = ($avg - $full) >= 0.5;
                        @endphp
                        <div class="flex items-center gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $full)
                                    <span class="text-yellow-400 text-sm">★</span>
                                @elseif ($i == $full + 1 && $half)
                                    <span class="text-yellow-400 text-sm">½</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 text-sm">★</span>
                                @endif
                            @endfor
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                @if ($count > 0)
                                    {{ $avg }} ({{ $count }} {{ $count === 1 ? 'review' : 'reviews' }})
                                @else
                                    No ratings yet
                                @endif
                            </span>
                        </div>

                        <div class="mt-3 space-y-3 text-xs md:text-sm text-gray-700 dark:text-gray-300 flex-grow">
                            <div class="flex items-center gap-2 md:gap-3">
                                <x-heroicon-o-squares-2x2 class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                <span class="font-medium text-gray-900 dark:text-gray-100">Type:</span>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('-', ' ', $boar->boar_type)) }}</span>
                                @if($boar->boar_type_other)
                                    <span class="ml-1 text-gray-500">({{ $boar->boar_type_other }})</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 md:gap-3">
                                <x-heroicon-o-currency-dollar class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                <span class="font-medium text-gray-900 dark:text-gray-100">Price:</span>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">₱{{ number_format((int) ($boar->default_price_money ?? 0)) }}</span>
                            </div>

                            <div class="flex items-center gap-2 md:gap-3">
                                <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                <span class="font-medium text-gray-900 dark:text-gray-100">Down payment:</span>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">₱{{ number_format((int) ($boar->default_downpayment ?? 0)) }}</span>
                            </div>

                            <div class="flex items-center gap-2 md:gap-3">
                                <x-heroicon-o-cube class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                <span class="font-medium text-gray-900 dark:text-gray-100">Pay with pigs:</span>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ (int) ($boar->default_pay_with_pigs ?? 0) }} {{ (int) ($boar->default_pay_with_pigs ?? 0) === 1 ? 'pig' : 'pigs' }}</span>
                            </div>

                            <div class="flex items-center gap-2 md:gap-3">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                <span class="font-medium text-gray-900 dark:text-gray-100">Breeder:</span>
                                <span class="ml-2 truncate text-gray-700 dark:text-gray-300">{{ $boar->user->name }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <x-filament::button
                                color="success"
                                size="sm"
                                class="w-full"
                                wire:click="startRequest({{ $boar->id }})"
                            >
                                <x-slot name="icon">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                </x-slot>
                                Request Reservation
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
                </x-filament::grid.column>
            @empty
                <x-filament::section class="col-span-full text-center py-12">
                    <x-emoji-pig class="mx-auto h-16 w-16 text-gray-400" />
                    <h3 class="mt-2 text-md font-medium text-gray-900 dark:text-white">No Boars Available</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check back later for new listings from breeders.</p>
                </x-filament::section>
            @endforelse
        </x-filament::grid>
    </div>

</x-filament-panels::page>
