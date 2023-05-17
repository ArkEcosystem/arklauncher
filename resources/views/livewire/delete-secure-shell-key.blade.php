<div>
    @if ($this->keyId)
        <x-delete-modal
            :title="trans('pages.user-settings.delete_ssh_title')"
            :description="trans('pages.user-settings.delete_ssh_description')"
            image="/images/modal/delete.svg"
            action-method="destroy"
            close-method="cancel"
            can-submit
        />
    @endif
</div>
