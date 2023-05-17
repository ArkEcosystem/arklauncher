@component ('layouts.token', ['token' => $token, 'stepTitle' => 'collaborators'])
    @slot ('title')
        @lang ('tokens.collaborators.page_header')
    @endslot

    <div class="flex flex-row flex-wrap mt-8">
        <div class="w-full md">

            <div class="items-start">
                @can ('createCollaborator', $token)
                    @livewire ('invite-collaborator', ['token' => $token])

                    @livewire ('mailed-invitations', ['token' => $token])

                    <x-divider />
                @endcan

                @canany (['createCollaborator', 'deleteCollaborator'], $token)
                    @livewire ('manage-collaborators', ['token' => $token])
                @endcanany

                @can ('createCollaborator', $token)
                    @livewire ('update-collaborator', ['token' => $token])
                @endcan

                @can ('deleteCollaborator', $token)
                    @livewire ('delete-collaborator', ['token' => $token])
                @endcan
            </div>
        </div>
    </div>
@endcomponent
