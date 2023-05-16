@extends('layouts.app')

@section('content')
    <x-ark-container>
        <div class="items-center">
            <h1>{{ $server->name }}</h1>
        </div>

        <div>
            <div class="flex flex-row flex-wrap mt-4">
                <div class="w-full text-left md:w-1/5 lg:border-r text-theme-secondary-700">
                    <hr class="ml-10" />
                    <div class="mt-10">
                        <div class="flex px-10 text-xl font-semibold align-bottom border-l-4 border-transparent">
                            <img class="mt-1 mr-2 h-3" src="/images/container/bars.svg" />
                            <span>@lang('menus.menu')</span>
                        </div>

                        <div class="sidebar-menu-entries">
                            <div class="border-l-4 border-transparent container-sidebar-menu-entry-wrapper border-color">
                                <a href="{{ route('servers.show', $server) }}" class="border-none">@lang('menus.server.details')</a>
                            </div>

                            <div class="border-l-4 border-transparent container-sidebar-menu-entry-wrapper border-color">
                                <a href="{{ route('servers.node', $server) }}" class="border-none">@lang('menus.server.node_information')</a>
                            </div>

                            <div class="border-l-4 border-transparent container-sidebar-menu-entry-wrapper border-color">
                                <a href="{{ route('servers.logs', $server) }}" class="border-none">@lang('menus.server.logs')</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-4/5">
                    <hr class="mr-10" />

                    <div class="items-start mx-10 mt-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </x-ark-container>
@endsection
