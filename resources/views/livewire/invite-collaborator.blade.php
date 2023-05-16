<section>
    <h2 class="header-4">{{ trans('pages.collaborators.send_invitation_title') }}</h2>
    <p class="mt-4">{{ trans('pages.collaborators.send_invitation_description', ['appName' => config('app.name')]) }}</p>

    <form wire:submit.prevent="invite">
        <div class="mb-4">
            <x-ark-flash />
        </div>

        <x-ark-input type="email" name="email" :label="trans('forms.email_address')" :errors="$errors" />

        <div class="flex flex-col mt-4">
            <span class="flex justify-between mb-4 text-sm font-semibold">
                @lang('forms.invite_collaborator.permissions')

                <div class="divide-x select-none divide-theme-secondary-200">
                    <span class="pr-2 cursor-pointer link" wire:click="selectAll" role="button">@lang('actions.select_all')</span>
                    <span class="pl-2 cursor-pointer link" wire:click="deselectAll" role="button">@lang('actions.deselect_all')</span>
                </div>
            </span>

            <div class="flex flex-wrap w-full collaborator-grid">
                @foreach ($this->availablePermissions as $permission)
                    <div class="flex">
                        <x-ark-checkbox 
                            class="mt-0" 
                            :name="$permission" 
                            :label="trans('pages.collaborators.permissions.'.$permission)" 
                            :value="$permission" 
                            model="permissions"
                        />
                    </div>
                @endforeach
            </div>

            <div>
                @error('permissions')
                    <p class="input-help--error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end mt-8 space-x-3">
            <button type="submit" class="button-secondary">@lang('forms.invite_collaborator.send_invitation')</button>
        </div>
    </form>
</section>
