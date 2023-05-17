<section>
    <h2 class="header-4">@lang('pages.user-settings.create_ssh_title')</h2>
    <p>@lang('pages.user-settings.create_ssh_description')</p>

    <form class="mt-8" wire:submit.prevent="store">
        <div class="space-y-4">
            <x-ark-input type="text" name="name" :label="trans('forms.ssh_key.input_name')" :errors="$errors" />
            <x-ark-textarea :rows="7" name="public_key" :label="trans('forms.ssh_key.input_public_key')" :errors="$errors" />
        </div>

        <div class="flex justify-end mt-8">
            <button type="submit" class="w-full sm:w-auto button-secondary">@lang('actions.add')</button>
        </div>
    </form>
</section>
