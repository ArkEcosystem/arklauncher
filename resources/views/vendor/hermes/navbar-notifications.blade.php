<div class="flex-1 px-8 md:px-10">
    @if(Auth::check() && $notificationCount > 0)
        <div class="inline-block py-4 w-full md:py-4" dusk="navigation-notifications">
            @foreach($currentUser->notifications->take(4) as $notification)
                <a
                    class="flex px-4 pt-6 pb-4 -mx-4 leading-5 rounded-xl group dark:hover:bg-theme-success-900 hover:bg-theme-success-50"
                    dusk="navigation-notification-{{$loop->index}}"
                    href="{{ $notification->link() ?? $notification->route() }}"
                >
                    <x-hermes-notification-icon
                        :notification="$notification"
                        :type="$notification->data['type']"
                        :relatable="$notification->relatable()->withTrashed()->first()"
                    />

                    <div class="flex overflow-auto flex-col ml-5 space-y-1 w-full">
                        <div class="flex flex-row justify-between">
                            <span class="flex-grow font-semibold truncate text-theme-secondary-900 dark:text-theme-secondary-200">
                                {{ $notification->title() }}
                            </span>

                            <span class="hidden text-sm whitespace-nowrap md:block md:text-right text-theme-secondary-400 dark:text-theme-secondary-700">
                                {{ $notification->created_at_local->diffForHumans() }}
                            </span>
                        </div>

                        <div class="flex flex-col justify-between md:flex-row md:space-x-3 dark:text-theme-secondary-500">
                            <span class="notification-truncate">
                                @if ($renderAsHtml ?? false)
                                    {!! $notification->content() !!}
                                @else
                                    {{ $notification->content() }}
                                @endif
                            </span>

                            <div class="flex flex-row space-x-4">
                                @if($notification->hasAction())
                                    <span class="mt-1 font-semibold whitespace-nowrap md:mt-0 link">
                                        {{ $notification->linkTitle() }}
                                    </span>
                                @endif

                                <span class="block mt-1 text-sm md:hidden text-theme-secondary-400">
                                    {{ $notification->created_at_local->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </a>

                @unless ($loop->last)
                    <span class="block w-full border-b border-dashed border-theme-secondary-200 dark:border-theme-secondary-800"></span>
                @endunless
            @endforeach

            <div class="flex flex-row justify-center px-2 pb-6 mt-4 w-full">
                <a href="{{ route('user.notifications') }}" class="w-full cursor-pointer button-secondary">
                    {{ $notificationCount > 4 ? trans('ui::actions.show_all') : trans('ui::actions.open_notifications') }}
                </a>
            </div>
        </div>
    @else
        <div class="p-6 mt-8 text-center rounded-xl border-2 border-theme-secondary-200 dark:border-theme-secondary-800">
            <span>@lang('ui::menus.notifications.no_notifications')</span>
        </div>
        <div class="py-8 md:px-8">

            <x-ark-icon name="notification.empty" class="w-full h-full light-dark-icon" />

        </div>
    @endif
</div>
