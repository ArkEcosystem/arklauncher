<div>
    @push ('scripts')
        <script src="{{ asset('js/file-upload.js')}}"></script>
    @endpush

    <div class="flex space-x-4">
        @for ($i = 1; $i <= $this->steps; $i++)
            <x-tokens.step-indicator :active="$this->step" :current="$i" />
        @endfor
    </div>

    {{-- General --}}
    @if ($this->step === 1)
        <section class="mt-10">
            @include ('app.tokens.configure.general')
        </section>
    @endif

    {{-- Network --}}
    @if ($this->step === 2)
        <section class="mt-10">
            @include ('app.tokens.configure.network')
        </section>
    @endif

    {{-- Fees --}}
    @if ($this->step === 3)
        <section class="mt-10">
            @include ('app.tokens.configure.fees')
        </section>
    @endif

    {{-- Review --}}
    @if ($this->isLastStep())
        <section class="mt-10">
            @include ('app.tokens.configure.review')
        </section>
    @endif

    @if (! $this->isLastStep())
        @include ('app.tokens.configure.buttons')
    @else
        <x-tokens.onboard-buttons
            class="justify-end mt-5 space-y-3 sm:flex sm:space-y-0 sm:space-x-3"
            :token="$this->tokenObject"
            step="configuration"
            on-click="update"
        />
    @endif

    <livewire:use-defaults-modal />
</div>
