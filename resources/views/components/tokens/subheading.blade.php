@props ([
    'title',
    'description' => null,
    'fields' => null,
    'wire' => null,
])

<div {{ $attributes }}>
    <div class="flex items-center">
        <h2
            id="{{ Str::slug($title) }}"
            class="pr-2 border-r header-4 border-theme-secondary-200"
        >
            {{ $title }}
        </h2>

        <button
            type="button"
            class="ml-2 font-semibold link"
            id="default-values-{{ Str::slug($title) }}"
            @if ($wire)
                wire:click="{{ $wire }}"
            @else
                wire:click="handleDefaults({{ $fields }})"
            @endif
        >
            {{ trans('actions.use_defaults') }}
        </button>
    </div>

    @if ($description)
        <div>
            <p>{{ $description }}</p>
        </div>
    @endif
</div>
