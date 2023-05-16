<div
    x-data="{
        scrollUp() {
            window.scrollTo(0, 0)
        }
    }"
    class="flex flex-col mt-10 space-y-3 sm:flex-row sm:justify-end sm:space-y-0 sm:space-x-3"
>
    @if ($this->hasReachedReviewStage)
        <button
            type="button"
            class="w-full sm:w-auto button-secondary"
            wire:click="cancelChanges"
            @click="scrollUp"
        >
            {{ trans('actions.cancel') }}
        </button>

        <button
            type="button"
            class="w-full sm:w-auto button-primary"
            wire:click="returnToReview"
            @click="scrollUp"
            dusk="save-and-continue"
        >
            {{ trans('actions.save_continue') }}
        </button>
    @else
        <button
            type="button"
            class="w-full sm:w-auto button-secondary"
            wire:click="previous"
            @click="scrollUp"
            @if($this->step === 1) disabled @endif
        >
            {{ trans('actions.back') }}
        </button>

        <button
            type="button"
            class="w-full sm:w-auto button-primary"
            wire:click="next"
            @click="scrollUp"
        >
            {{ trans('actions.continue') }}
        </button>
    @endif
</div>
