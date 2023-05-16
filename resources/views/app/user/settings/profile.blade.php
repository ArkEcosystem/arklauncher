@component('layouts.user-settings')

    <livewire:update-user-account :request="request()" />

    <x-divider />

    <livewire:profile.update-timezone-form />

    <x-divider />

    <livewire:profile.export-user-data />

    <x-divider />

    <livewire:profile.delete-user-form
        confirm-name
        :confirm-password="false"
        :show-confirmation-message="false"
        alert-type="warning"
        :alert="trans('modals.delete-user.alert')" />

@endcomponent
