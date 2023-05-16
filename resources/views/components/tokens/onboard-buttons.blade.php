<div {{ $attributes->class('flex flex-col-reverse sm:flex-row justify-end items-center w-full sm:space-x-3') }}>
    @if($showCancel ?? true)
        <a
            href="{{ $token ? route('tokens.show', $token) : route('tokens') }}"
            @isset($disableCancel)
                class="w-full sm:w-auto button-secondary mt-3 sm:mt-0 {{ $disableCancel === true ? 'disabled' : ''}}"
            @endisset
            @empty($disableCancel)
                class="mt-3 w-full sm:mt-0 sm:w-auto button-secondary"
            @endempty
        >
            @lang('actions.cancel')
        </a>
    @endif

    @if($step ?? false)
        @if (! $token || $token->onboarding()->isStep(Format::stepTitle($step)))
            @unless($submitButton ?? false)
                <a
                    dusk="save-and-continue"
                    class="w-full sm:w-auto button-primary cursor-pointer {{ $state ?? '' }}
                        {{ $disableSubmit ?? false || ($token && $token->onboarding()->available(Format::stepTitle($step)) === false) ? 'disabled' : '' }}
                    "
                    @unless($onClick ?? false)
                        href="{{ route('tokens.show', $token) }}"
                    @else
                        wire:click="{{ $onClick }}"
                    @endunless
                >
                    {{ $title ?? trans('actions.save_continue') }}
                </a>
            @else
                <button
                    dusk="save-and-continue"
                    type="submit"
                    class="w-full sm:w-auto button-primary cursor-pointer
                        {{ $disableSubmit ?? false || ($token && $token->onboarding()->available(Format::stepTitle($step)) === false) ? 'disabled' : '' }}
                    "
                    @if($onClick ?? false)
                        wire:click="{{ $onClick }}"
                    @elseif($alpineClick ?? false)
                        x-on:click="{{ $alpineClick }}"
                    @endif
                >
                    {{ $title ?? trans('actions.save_continue') }}
                </a>
            @endif
        @endif
    @endif
</div>
