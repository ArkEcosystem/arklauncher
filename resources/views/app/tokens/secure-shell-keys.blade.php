@component('layouts.token', ['token' => $token])
    @slot('title')
        @lang('tokens.secure-shell-keys.page_header')
    @endslot

    <div>
        @lang('tokens.secure-shell-keys.page_description')
        <div class="mt-3">
            <x-ark-external-link
                :url="trans('urls.documentation.servers_ssh')"
                :text="trans('actions.learn_more')"
            />
        </div>
    </div>

    <div class="mt-8">
        @livewire('manage-token-secure-shell-keys', ['token' => $token])
    </div>
@endcomponent
