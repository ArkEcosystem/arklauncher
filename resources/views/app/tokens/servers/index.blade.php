@component('layouts.token', ['token' => $token])
    @push('scripts')
        <script src="{{ mix('js/clipboard.js')}}"></script>
    @endpush

    @slot('title')
        @lang('pages.token.servers.title')
    @endslot

    <livewire:active-servers :selected-token="$token" />

@endcomponent

