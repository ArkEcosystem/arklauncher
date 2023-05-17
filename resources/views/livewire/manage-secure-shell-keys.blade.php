<section>
    <h2 class="header-4">@lang('pages.user-settings.manage_ssh_title')</h2>
    <p>@lang('pages.user-settings.manage_ssh_description')</p>

    @if (count($this->keys) > 0)
        <section class="hidden mt-8 md:block">
            <x-profile.keys.desktop :keys="$this->keys" />
        </section>

        <section class="block mt-8 md:hidden">
            <x-profile.keys.mobile :keys="$this->keys" />
        </section>
    @else
        <x-blank class="mt-8">{{ trans('pages.user-settings.no_ssh_keys') }}</x-blank>
    @endif
</section>
