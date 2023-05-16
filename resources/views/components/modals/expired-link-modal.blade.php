@props ([
    'wire',
    'title',
    'message',
])

<x-ark-modal
    width-class="max-w-xl"
    title-class="header-2"
    :wire-close="$wire"
>
    <x-slot name="title">
        {{ $title }}
    </x-slot>

    <x-slot name="description">
        <div class="flex justify-center my-8">
            <img src="{{ asset('/images/modal/expired-link.svg') }}" alt="{{ $title }}" class="w-full">
        </div>

        <p>{{ $message }}</p>
    </x-slot>
</x-ark-modal>
