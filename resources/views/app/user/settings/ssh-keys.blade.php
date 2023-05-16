@component('layouts.user-settings', ['title' => trans('menus.user-settings.ssh-keys')])
    @livewire('create-secure-shell-key')

    <x-divider />

    @livewire('manage-secure-shell-keys')

    @livewire('delete-secure-shell-key')
@endcomponent
