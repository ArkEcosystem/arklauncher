@component('layouts.user-settings', ['title' => trans('menus.user-settings.security')])
    @push('scripts')
        <script src="{{ asset('js/file-download.js')}}"></script>
    @endpush

    <livewire:profile.update-password-form />

    <x-divider />

    <livewire:profile.two-factor-authentication-form />
@endcomponent
