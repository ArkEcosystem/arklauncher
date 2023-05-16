<div>
    @push('scripts')
        <script src="{{ mix('js/swiper.js')}}"></script>
        <script src="{{ mix('js/clipboard.js')}}"></script>
    @endpush

    <div class="flex flex-col mb-6 space-y-6 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
        <h1 class="m-0">@lang('pages.token.page_name')</h1>

        <button
            onclick="livewire.emit('onCreateToken')"
            class="w-full sm:w-auto button-secondary"
        >
            @lang('actions.create_token')
        </button>
    </div>

    <x-token-slider :selected-token="$selectedToken" :tokens="$tokens" />

    @if($selectedToken)
        <livewire:active-servers :title="@trans('pages.manage-tokens.active_servers')" :selected-token="$selectedToken" />
    @endif

    @livewire('manage-welcome-screens')

    <livewire:create-token-modal />

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            const mySwiper = document.querySelector('.swiper').swiper;

            const queryString = window.location.search;
            const urlParams   = new URLSearchParams(queryString);
            const index       = urlParams.get('index') ?? 0;

            mySwiper.slideTo(index);

            mySwiper.on('click', function () {
                if (this.clickedIndex !== undefined) {
                    window.livewire.emit('setIndex', this.clickedIndex);
                }
            });
        });
    </script>
</div>
