<div>
    @if($this->showModal)
        <div class="overflow-y-auto fixed inset-0 z-40 opacity-75 bg-theme-secondary-900"></div>

        <div
            class="flex overflow-y-auto fixed inset-0 z-50"
            wire:click="closeModal"
        >
            <div class="w-full h-full flex justify-center items-center{{ $class ?? '' }}">
                <div class="relative w-full mx-auto {{ $widthClass ?? 'max-w-5xl' }} px-4 sm:px-8">
                    <div class="shadow-2xl" style="position: relative; width: 100%; height: 0; padding-bottom: 56.25%;">
                        <iframe class="block absolute top-0 left-0 w-full h-full" src="{{ $this->url }}?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>

                    <div
                        class="flex absolute top-0 right-0 justify-center items-center mr-4 -mt-4 w-10 h-10 rounded cursor-pointer hover:shadow-lg text-theme-secondary-700 bg-theme-secondary-100 transition-default hover:bg-theme-secondary-300"
                        wire:click="closeModal"
                    >
                        <x-ark-icon name="cross" size="sm" class="m-auto" />
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
