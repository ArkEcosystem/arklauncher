@php($isCompleted = $token->onboarding()->completed($name))
@php($isAvailable = $token->onboarding()->available($name))

<div class="flex pt-6">
    @if($isCompleted)
        <div class="mt-1" data-tippy-content="{{ trans('tooltips.steps.completed') }}">
            <x-ark-status-circle type="success" />
        </div>
    @elseif($isAvailable)
        <div class="mt-1" data-tippy-content="{{ trans('tooltips.steps.active') }}">
            <x-ark-status-circle type="active" />
        </div>
    @else
        <div class="mt-1 opacity-40" data-tippy-content="{{ trans('tooltips.steps.locked') }}">
            <x-ark-status-circle type="locked" />
        </div>
    @endif
    <div class="ml-3">
        <div class="select-none">
            @if($isCompleted || $isAvailable)
                <a
                    href="{{ $route ?? '' ? route($route, $token) : '#' }}"
                    id="onboard-step-{{ $name }}"
                    class="text-lg font-semibold hover:underline transition-default @if($isCompleted) line-through text-theme-success-600 @else text-theme-primary-500 @endif"
                >
                    {{ $title }}
                </a>
            @else
                <span class="text-lg font-semibold opacity-40 cursor-not-allowed text-theme-secondary-900">
                    {{ $title }}
                </span>
            @endif
            @if($optional ?? false)
                <span class="text-lg font-bold cursor-default text-theme-secondary-500">Optional</span>
            @endif
        </div>
        <div class="mt-2 @if($isCompleted) text-theme-secondary-500 @elseif(!$isAvailable) opacity-40 @endif">{{ $description }}</div>
    </div>
</div>
