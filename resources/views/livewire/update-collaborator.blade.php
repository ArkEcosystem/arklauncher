<div>
    @if ($this->collaboratorId)
        <x-divider />

        <div>
            <h2 class="header-4">{{ trans('tokens.token_collaborator') }}</h2>
            <p>{{ trans('tokens.invitations.mailed_invitations_description') }}</p>

            <form class="mt-5 space-y-4" wire:submit.prevent="update">
                <div>
                    <x-ark-flash />
                </div>

                <div class="flex flex-col">
                    <span class="flex justify-between mb-4 text-sm font-semibold">
                        {{ trans('forms.invite_collaborator.permissions') }}

                        <div class="divide-x select-none divide-theme-secondary-200">
                            <span class="pr-2 cursor-pointer link" wire:click="selectAll" role="button">@lang('Select All')</span>
                            <span class="pl-2 cursor-pointer link" wire:click="deselectAll" role="button">@lang('Deselect All')</span>
                        </div>
                    </span>

                    <div class="flex flex-wrap w-full collaborator-grid">
                        @foreach ($this->availablePermissions as $permission)
                            <div class="flex">
                                <x-ark-checkbox 
                                    :id="'update-'.$permission" 
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

                <div class="flex justify-end space-x-3">
                    <button type="button" class="button-secondary" wire:click="close">@lang('actions.cancel')</button>
                    <button type="submit" class="button-primary">@lang('actions.update')</button>
                </div>
            </form>
        </div>
    @endif
</div>
