<div class="space-y-4">
    <div class="text-start">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Selected Boar</h3>
    </div>

    @if($boarImage)
        <div class="flex justify-start mb-4">
            <img src="{{ $boarImage }}"
                 alt="{{ $boarName }}"
                 class="object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 w-full max-w-[280px] h-auto aspect-[280/270]">
        </div>
    @endif

    @if(isset($breederName) || isset($breederPhone) || !empty($breederAvatarUrl))
        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <div class="flex items-center gap-3 mb-4">
                @if(!empty($breederAvatarUrl))
                    <img src="{{ $breederAvatarUrl }}"
                         alt="{{ $breederName ?? 'Boar raiser' }}"
                         class="w-11 h-11 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600 shadow-sm">
                @endif
                <h2 class="text-xl font-semibold text-black dark:text-white">Owner Information</h2>
            </div>
            @if(!empty($breederName))
                <p><span class="font-medium text-gray-700 dark:text-gray-300">Breeder:</span> {{ $breederName }}</p>
            @endif
            @if(!empty($breederPhone))
                <p><span class="font-medium text-gray-700 dark:text-gray-300">Contact number:</span> {{ $breederPhone }}</p>
            @endif
        </div>
    @endif
</div>
