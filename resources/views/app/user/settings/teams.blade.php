@component('layouts.app', ['fullWidth' => true, 'isLanding' => true])

    @section('title', trans('pages.user-settings.teams.my_teams_title'))

    @section('content')
        <x-ark-pages-includes-header
            :title="trans('pages.user-settings.teams.my_teams_title')"
            :description="trans('pages.user-settings.teams.my_teams_description')"
        />

        <x-ark-container container-class="flex flex-col">
            <x-pending-invitations />

            <x-user-teams />

            <livewire:collaborator-permissions-modal />

            <livewire:decline-invitation-modal />

            <livewire:leave-team-modal />
        </x-ark-container>
    @endsection
@endcomponent
