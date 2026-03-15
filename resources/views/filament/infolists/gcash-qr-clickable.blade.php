@php
    $record = $getRecord();
    $path = $record?->boar?->gcash_qr_image ?? null;
    $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
@endphp
@if($url)
    <div class="fi-in-image flex flex-col" x-data="{ gcashModalOpen: false }">
        <button
            type="button"
            @click="gcashModalOpen = true"
            class="inline-flex cursor-pointer rounded-lg border-2 border-gray-200 dark:border-gray-600 transition hover:border-primary-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 p-0"
        >
            <img
                src="{{ $url }}"
                alt="GCash QR code – click to enlarge"
                class="block max-w-full h-auto rounded-lg object-contain pointer-events-none"
            />
        </button>
        <p class="mt-1 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
            <x-heroicon-o-qr-code class="w-4 h-4 flex-shrink-0" />
            Click the image to view larger
        </p>

        {{-- Modal: teleported to body so backdrop-blur works outside Filament layout --}}
        <template x-teleport="body">
            <div
                x-show="gcashModalOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 pt-24 pb-6 overflow-y-auto"
                style="display: none;"
                @keydown.escape.window="gcashModalOpen = false"
            >
                <div
                    class="absolute inset-0 bg-gray-900/70 dark:bg-gray-900/80"
                    style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"
                    @click="gcashModalOpen = false"
                ></div>
            <div
                class="relative z-10 w-full max-w-md rounded-xl bg-white dark:bg-gray-800 shadow-xl p-6 mt-4"
                @click.stop
            >
                <img
                    src="{{ $url }}"
                    alt="GCash QR code"
                    class="w-full max-w-sm mx-auto rounded-lg border-2 border-gray-200 dark:border-gray-600 shadow-md"
                />
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">Scan this QR code with your GCash app to pay the downpayment.</p>
                <button
                    type="button"
                    @click="gcashModalOpen = false"
                    class="mt-6 w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    Close
                </button>
            </div>
        </div>
        </template>
    </div>
@endif
