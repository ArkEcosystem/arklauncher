<div class="w-full">
    <x-ark-modal
        class="w-full"
        width-class="max-w-2xl"
        wire-close="close"
        buttons-style="flex flex-col-reverse"
        close-style="absolute right-0 top-0"
        padding-class="p-0"
        close-button-only
        x-data="{
            index: 0,
            lastIndex: 3,
            takeTour: false,
            startTour() {
                this.takeTour = true;

                this.$nextTick(() => {
                    document.querySelector('#swiper-welcome').swiper.on('slideChange', e => {
                        this.index = e.activeIndex;
                    });
                })
            },
            next() {
                if (this.index < this.lastIndex) {
                    document.querySelector('#swiper-welcome').swiper.slideTo(this.index+1);
                }
            },
            prev() {
                if (this.index > 0) {
                    document.querySelector('#swiper-welcome').swiper.slideTo(this.index-1);
                }
            }
        }"
    >
        <x-slot name="description">
            <div x-show="! takeTour">
                <div class="px-10 pt-10">
                    <h3>{{ trans('pages.welcome.intro.title') }}</h3>
                </div>

                <div class="flex justify-center px-10 mt-8 w-full">
                    <img class="welcome-screen" src="{{ asset("images/welcome/intro.svg") }}" />
                </div>

                <div class="px-10 mt-8 w-full">
                    <p>{{ trans('pages.welcome.intro.description') }}</p>
                </div>
            </div>

            <div class="-mt-8 md:mt-0" x-show="takeTour" x-cloak>
                <x-ark-slider
                    id="welcome"
                    :columns="1"
                    auto-height
                    hide-navigation
                    top-pagination
                    hide-bullets
                >
                    @for ($i = 0; $i < 3; $i++)
                        <x-ark-slider-slide>
                            <div class="px-10 pt-10 pb-8">
                                <h3>{{ trans('pages.welcome.slide'.$i.'.title') }}</h3>
                            </div>

                            <div class="flex justify-center w-full border-t border-b border-theme-secondary-300">
                                <img class="welcome-screen" src="{{ asset("images/welcome/slide{$i}.svg") }}" />
                            </div>

                            <div class="px-10 pt-8">
                                <p>{{ trans('pages.welcome.slide'.$i.'.description') }}</p>
                            </div>
                        </x-ark-slider-slide>
                    @endfor

                    {{-- Video slide --}}
                    {{-- <x-ark-slider-slide>
                        <div class="px-10 pt-10 pb-8">
                            <h3>{{ trans('pages.welcome.slide3.title') }}</h3>
                        </div>

                        <div class="overflow-hidden relative" style="padding-top:56.25%; height: 0;">
                            <iframe
                                class="absolute top-0 left-0 w-full h-full"
                                src="https://www.youtube.com/embed/35vfqcmqeZQ?rel=0"
                                frameborder="0"
                                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>

                        <div class="px-10 pt-8">
                            <p>{{ trans('pages.welcome.slide3.description') }}</p>
                        </div>
                    </x-ark-slider-slide> --}}
                </x-ark-slider>
            </div>
        </x-slot>

        <x-slot name="buttons">
            <div x-show="!takeTour" class="flex justify-between items-center px-10 pb-10 space-x-3">
                <div>
                    <x-ark-checkbox
                        name="hideForever"
                        deferred
                        :label="trans('pages.welcome.intro.dismiss')"
                        class="mt-0"
                    />
                </div>

                <div class="flex items-center space-x-3">
                    <button type="button" class="button-secondary" wire:click="close">
                        {{ trans('actions.skip') }}
                    </button>

                    <button type="button" class="button-primary" @click="startTour">
                        {{ trans('actions.start') }}
                    </button>
                </div>
            </div>

            <div x-show="takeTour" class="flex justify-between px-10 pb-10 -mt-8 space-x-2" x-cloak>
                <div class="flex items-center space-x-2">
                    @for ($i = 0; $i < 3; $i++)
                        <span class="inline-flex w-2 h-2 rounded-full" :class="{
                            'bg-theme-secondary-300': {{ $i }} !== index,
                            'bg-theme-primary-600': {{ $i }} === index,
                        }"></span>
                    @endfor
                </div>

                <div class="flex justify-end items-center space-x-2">
                    <button type="button" class="button-secondary" @click="prev()" x-bind:disabled="index === 0">
                        {{ trans('actions.back') }}
                    </button>

                    <button x-show="index !== (lastIndex - 1)" type="button" class="button-primary" @click="next()">
                        {{ trans('actions.next') }}
                    </button>

                    <button x-show="index === (lastIndex - 1)" type="button" class="button-primary" wire:click="closeForever">
                        {{ trans('actions.finish') }}
                    </button>
                </div>
            </div>
        </x-slot>
    </x-ark-modal>
</div>
