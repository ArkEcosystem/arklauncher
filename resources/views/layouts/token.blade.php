@extends('layouts.app')

@push('scripts')
    <x-ark-pages-includes-crop-image-scripts />
@endpush

@section('content')
    <x-ark-container container-class="flex flex-col md:flex-row">
        <div class="flex flex-col pb-6 mb-6 border-b sm:flex-row md:pb-0 md:mb-0 md:border-b-0 border-theme-secondary-200">
            @unless($token->canBeEdited())
                <div class="sm:pr-6 sm:border-r md:pr-8 border-theme-secondary-200">
                    <div class="flex overflow-auto items-center mb-4 sm:mb-0 md:mb-4 md:w-56 md:h-11">
                        <div class="overflow-hidden flex-shrink-0 w-11 h-11">
                            <livewire:logo-upload
                                :token="$token"
                                dimensions="w-11 h-11"
                                :display-text="false"
                                icon-size="base"
                                without-border
                                :upload-tooltip="trans('tooltips.photo_requirements',
                                [
                                    'min-width' => config('ui.upload.image-single.dimensions.min-width'),
                                    'min-height' => config('ui.upload.image-single.dimensions.min-height'),
                                    'max-filesize' => (config('ui.upload.image-single.max-filesize') / 1024)
                                ])"
                            />
                        </div>

                        <div class="flex overflow-auto flex-col justify-between ml-3 h-full">
                            <span class="text-xs font-semibold text-theme-secondary-500">
                                @lang('tokens.token')
                            </span>

                            <span class="font-semibold text-theme-primary-500 truncate">
                                {{ $token->name }}
                            </span>
                        </div>
                    </div>

                    <x-tokens.sidebar-divider class="hidden my-1 md:block" />

                    <div class="hidden w-56 md:block sidebar-menu-entries">
                        <x-token-sidebar-links :token="$token"/>
                    </div>
                </div>
            @endunless

            <div class="flex-1 w-full sm:pl-6 md:hidden">
                @unless($token->canBeEdited())
                    <x-ark-secondary-menu
                        :title="trans('menus.tokens.menu')"
                        button-class="h-16"
                    >
                        <x-slot name="navigation">
                            <x-token-sidebar-links :token="$token"/>
                        </x-slot>
                    </x-ark-secondary-menu>
                @endunless
            </div>
        </div>

        <div class="mx-auto w-full min-w-0 md:pl-10">
            <h1>{{ $title ?? '' }}</h1>
            <div>{{ $slot }}</div>
        </div>
    </x-ark-container>
@endsection
