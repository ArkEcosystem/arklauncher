<div class="flex -mx-2">
    <div class="px-2 w-1/3 border-r">
        <h3 class="font-semibold text-md">{{ $title }}</h3>
        <div class="mt-2 text-sm text-theme-secondary-800">{{ $description }}</div>
    </div>

    <div class="px-2 w-2/3">
        <div class="overflow-hidden py-4 px-6 bg-white">
            <div class="space-y-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
