@extends('layouts.app')

@section('content')
    <x-ark-container container-class="flex">
        <div class="hidden pr-10 w-1/4 border-r lg:block border-theme-secondary-200 sidebar-menu-entries">
            <x-ark-sidebar-link :name="trans('menus.user-settings.profile')" route="user.profile" icon="user" icon-alignment="left" />
            <x-ark-sidebar-link :name="trans('menus.user-settings.security')" route="user.security" icon="lock" icon-alignment="left" />
            <x-ark-sidebar-link :name="trans('menus.user-settings.ssh-keys')" route="user.ssh-keys" icon="server-lock" icon-alignment="left" />
        </div>

        <div class="w-full lg:px-10 lg:w-3/4">
            <div class="mb-5 lg:hidden">
                <x-ark-secondary-menu :title="trans(Str::replace('user.', 'menus.user-settings.', Route::currentRouteName()))">
                    <x-slot name="navigation">
                        <x-ark-sidebar-link :name="trans('menus.user-settings.profile')" route="user.profile" icon="user" icon-alignment="left" />
                        <x-ark-sidebar-link :name="trans('menus.user-settings.security')" route="user.security" icon="lock" icon-alignment="left" />
                        <x-ark-sidebar-link :name="trans('menus.user-settings.ssh-keys')" route="user.ssh-keys" icon="server-lock" icon-alignment="left" />
                    </x-slot>
                </x-ark-secondary-menu>
            </div>

            @if (($title ?? null) !== false)
                <h1 class="mb-8">{{ $title ?? trans('pages.user-settings.page_name') }}</h1>
            @endif

            <div>{{ $slot }}</div>
        </div>
    </x-ark-container>
@endsection
