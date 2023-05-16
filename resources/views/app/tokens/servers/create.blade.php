@component('layouts.token', ['token' => $token])
    @slot('title')
        @lang('actions.create_server')
    @endslot

    @livewire('create-server', ['network' => $network])

@endcomponent
