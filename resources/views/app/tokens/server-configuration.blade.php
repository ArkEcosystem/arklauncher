@component('layouts.token', ['token' => $token])
    @slot('title')
        @lang('tokens.server-configuration.page_header')
    @endslot

    <div>
        @lang('tokens.server-configuration.page_description')
    </div>

    <div class="mt-8">
        @livewire('manage-server-configuration', ['token' => $token])
    </div>
@endcomponent
