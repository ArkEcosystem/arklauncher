<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <x-ark-pages-includes-layout-head
        :default-name="config('app.name')"
        mask-icon-color="#5452ce"
        microsoft-tile-color="#5452ce"
        theme-color="#ffffff"
    >
        <script src="https://kit.fontawesome.com/30c8fd749c.js" crossorigin="anonymous"></script>
    </x-ark-pages-includes-layout-head>

    <x-ark-pages-includes-layout-body :cookie-domain="config('tracking.analytics.domain')">
        <x-ark-navbar
            :title="config('app.name')"
            breakpoint="lg"
            :navigation="[
                ['route' => $currentUser ? 'tokens' : 'home', 'label' => $currentUser ? trans('menus.dashboard') : trans('menus.home')],
            ]"
            :profile-menu="[
                [
                    'label' => trans('menus.user-settings.settings'),
                    'route' => 'user.profile',
                    'icon' => 'gear',
                ],
                [
                    'label' => trans('menus.user-settings.teams'),
                    'route' => 'user.teams',
                    'icon' => 'users',
                ],
                [
                    'label' => trans('actions.logout'),
                    'route' => 'logout',
                    'isPost' => true,
                    'icon' => 'exit',
                ]
            ]"
            profile-menu-class="w-50 profile-menu"
            :identifier="$currentUser ? $currentUser->name : false"
            show-identifier-letters
            dropdown-classes="md:w-120"
        >
            @auth
                <x-slot name="notificationsIndicator">
                    @livewire('notifications-indicator')
                </x-slot>

                <x-slot name="notifications">
                    <x-hermes-navbar-notifications render-as-html />
                </x-slot>
            @endauth

            <x-slot name="logo">
                <span class="flex relative items-center">
                    <div class="relative">
                        <img src="{{ asset('images/logo.svg') }}" class="h-10 md:h-11 lg:ml-0" alt="{{ config('app.name') }} " />
                    </div>

                    <span class="hidden ml-4 transition sm:text-2xl md:block text-theme-secondary-900 duration-400">
                        <span class="font-bold">@lang('general.ark')</span><span class="uppercase">@lang('general.launcher')</span>
                    </span>
                </span>
            </x-slot>
        </x-ark-navbar>

        <x-slot name="footer">
            <x-ark-footer
                :creator="[
                    'url' => trans('urls.ardent'),
                    'label' => trans('general.ardent'),
                    'newWindow' => true,
                ]"
                :policy="[
                    'url' => route('privacy-policy'),
                    'label' => trans('general.privacy_policy'),
                ]"
                :terms="[
                    'url' => route('terms-of-service'),
                    'label' => trans('general.terms_of_service'),
                ]"
                :socials="config('social.networks')"
            />
        </x-slot>
    </x-ark-pages-includes-layout-body>
</html>
