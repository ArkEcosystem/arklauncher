@props ([
    'title',
    'description',
    'link' => null,
])

<section {{ $attributes }}>
    <h1>{{ $title }}</h1>
    <p class="mt-2">{{ $description }}</p>

    @if ($link)
        <div class="flex mt-4">
            <x-ark-external-link :url="$link" :text="trans('actions.learn_more')" />
        </div>
    @endif
</section>
