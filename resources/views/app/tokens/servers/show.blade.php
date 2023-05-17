@component('layouts.token', ['token' => $token, 'server' => $server])
    @slot('title')
        @lang('pages.server.installation.page_name')
    @endslot

    @livewire('server-deployment-tracker', ['token' => $token, 'serverId' => $server->id])
@endcomponent
