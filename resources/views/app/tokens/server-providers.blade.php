@component('layouts.token', ['token' => $token])
    @slot('title')
        @lang('tokens.server-providers.page_header')
    @endslot

    <div>
        @lang('tokens.server-providers.page_description')
    </div>

    <div class="mt-8">
        @livewire('manage-server-providers', ['token' => $token])
    </div>
@endcomponent
