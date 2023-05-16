<div class="flex -mx-2">
    <div class="flex justify-end items-center px-2 w-1/3 border-r">
        <h3 class="mr-6 font-semibold text-md">{{ $title }}</h3>
    </div>

    <div class="px-2 w-2/3">
        <div class="overflow-hidden py-4 px-6 bg-white">
            <div class="space-y-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
