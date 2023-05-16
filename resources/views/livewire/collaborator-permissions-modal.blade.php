<div>
    @if ($this->modalShown)
        <x-ark-modal class="w-full md:mx-auto" width-class="max-w-2xl" wire-close="closeModal">
            @slot('title') @lang('pages.user-settings.role_permissions_title') @endslot

            @slot('description')
                <div class="mt-4">
                    <div class="flex flex-wrap w-full collaborator-grid">
                        @foreach($this->availablePermissions as $permission)
                            <div class="flex space-x-4">
                                @if($this->permissions === [] || in_array($permission, $this->permissions))
                                    <x-ark-status-circle type="success" />
                                @else
                                    <x-ark-status-circle type="undefined" />
                                @endif

                                <span>{{ trans('pages.collaborators.permissions.'.$permission) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endslot

            @slot('buttons')
                <div class="flex justify-end mt-5 space-x-3">
                    <button class="button-secondary" wire:click="closeModal">@lang('actions.close')</button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>

