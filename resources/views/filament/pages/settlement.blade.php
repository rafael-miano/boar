<x-filament-panels::page>
    @php
        $settings          = $this->platformSettings;
        $totalOwed         = $this->totalPlatformFeeOwed;
        $pending           = $this->pendingSettlement;
        $lastRejected      = $this->lastRejectedSettlement;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Total owed --}}
        <x-filament::section>
            <x-slot name="heading">Total platform fee to pay</x-slot>
            <x-slot name="description">
                This is the total platform fee ({{ $settings->platform_fee_percentage }}% of confirmed money reservations) that you owe to the platform.
            </x-slot>

            <div class="text-center py-4">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    ₱{{ number_format($totalOwed, 2) }}
                </p>
                @if ($totalOwed <= 0)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        You have no platform fee to pay at the moment.
                    </p>
                @endif
            </div>
        </x-filament::section>

        {{-- Pending submission notice --}}
        @if ($pending)
            <x-filament::section>
                <x-slot name="heading">Receipt under review</x-slot>

                <div class="rounded-lg border border-yellow-200 bg-yellow-50 dark:bg-yellow-950 dark:border-yellow-800 p-4 text-sm text-yellow-800 dark:text-yellow-200">
                    Your payment receipt (₱{{ number_format($pending->amount, 2) }}) was submitted on
                    <strong>{{ $pending->submitted_at->format('F j, Y g:i A') }}</strong> and is currently being reviewed by the admin.
                    You will be notified once it is verified.
                </div>

                <div class="mt-4 flex justify-center">
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($pending->receipt_image) }}"
                        alt="Submitted receipt"
                        class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-700 shadow"
                    />
                </div>
            </x-filament::section>

        {{-- Rejection notice --}}
        @elseif ($lastRejected)
            <x-filament::section>
                <x-slot name="heading">Previous receipt rejected</x-slot>

                <div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 p-4 text-sm text-red-800 dark:text-red-200 mb-4">
                    Your last receipt was rejected.
                    @if ($lastRejected->rejection_reason)
                        <strong>Reason:</strong> {{ $lastRejected->rejection_reason }}
                    @endif
                    Please upload a new receipt below.
                </div>

                @if ($totalOwed > 0)
                    <form wire:submit="submitReceipt">
                        {{ $this->form }}
                        <div class="mt-4">
                            <x-filament::button type="submit" color="success" icon="heroicon-o-paper-airplane">
                                Submit New Receipt
                            </x-filament::button>
                        </div>
                    </form>
                @endif
            </x-filament::section>

        {{-- Upload form (no pending, no rejection) --}}
        @elseif ($totalOwed > 0)
            <x-filament::section>
                <x-slot name="heading">Submit payment receipt</x-slot>
                <x-slot name="description">
                    After paying via GCash, upload a screenshot of your payment confirmation here.
                </x-slot>

                <form wire:submit="submitReceipt">
                    {{ $this->form }}
                    <div class="mt-4">
                        <x-filament::button type="submit" color="success" icon="heroicon-o-paper-airplane">
                            Submit Receipt
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        @endif

        {{-- GCash QR --}}
        @if ($settings->gcash_qr_image)
            <x-filament::section>
                <x-slot name="heading">Platform GCash QR code</x-slot>
                <x-slot name="description">
                    Scan this QR code with your GCash app to send the platform fee.
                </x-slot>

                <div class="flex justify-center">
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->gcash_qr_image) }}"
                        alt="Platform GCash QR"
                        class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-700"
                    />
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    The platform has not set up a GCash QR yet. Contact the administrator for payment instructions.
                </p>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
