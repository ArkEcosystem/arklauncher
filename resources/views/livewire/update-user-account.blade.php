<div>
    <div>
        <h2 class="header-4">{{ trans('ui::pages.user-settings.contact_information_title') }}</h2>
        <p class="mt-4">{{ trans('ui::pages.user-settings.contact_information_description') }}</p>

        <form class="mt-8" wire:submit.prevent="update">
            <div>
                <x-ark-input type="text" name="name" model="name" :label="trans('ui::forms.name')" :errors="$errors" />
                <x-ark-input
                    class="mt-4"
                    type="email"
                    name="email"
                    model="email"
                    :label="trans('ui::forms.email_address')"
                    :errors="$errors"
                    :disabled="$this->user->waitingForEmailConfirmation()"
                    :readonly="$this->user->waitingForEmailConfirmation()"
                />

                @if ($this->user->waitingForEmailConfirmation())
                    <div class="mt-6">
                        <x-ark-alert>
                            <p>{!! trans('pages.user-settings.email-change.waiting_for_verification', ['email' => $this->user->getMetaAttribute('email_to_update')]) !!}</p>

                            <div class="flex items-center mt-4 space-x-4">
                                <button wire:click="resendConfirmationEmail" type="button" class="flex items-center space-x-1 text-sm font-semibold link">
                                    <x-ark-icon name="arrows.arrow-rotate-left" size="sm" />
                                    <span>{{ trans('actions.resend') }}</span>
                                </button>

                                <button wire:click="cancelConfirmationEmail" type="button" class="flex items-center space-x-1 text-sm font-semibold hover:underline text-theme-danger-400">
                                    <x-ark-icon name="trash" size="sm" />
                                    <span>{{ trans('actions.delete') }}</span>
                                </button>
                            </div>
                        </x-ark-alert>
                    </div>
                @endif
            </div>
            <div class="flex justify-end mt-8">
                <button type="submit" class="button-secondary">@lang('ui::actions.update')</button>
            </div>
        </form>
    </div>

    <div>
        @if ($invalidLink)
            <x-modals.invalid-link-modal
                wire="closeEmailUpdatedFeedbackModal"
                :title="trans('pages.user-settings.email-change.invalid_link_title')"
                :message="trans('pages.user-settings.email-change.invalid_link_description')"
            />
        @elseif ($expiredLink)
            <x-modals.expired-link-modal
                wire="closeEmailUpdatedFeedbackModal"
                :title="trans('pages.user-settings.email-change.expired_link_title')"
                :message="trans('pages.user-settings.email-change.expired_link_description')"
            />
        @elseif ($emailUpdated)
            <x-ark-modal
                width-class="max-w-xl"
                title-class="header-2"
                wire-close="closeEmailUpdatedFeedbackModal"
            >
                <x-slot name="title">
                    {{ trans('pages.user-settings.email-change.email_updated') }}
                </x-slot>

                <x-slot name="description">
                    <div class="flex justify-center my-8">
                        <img src="{{ asset('/images/modal/success.svg') }}" alt="{{ trans('pages.user-settings.email-change.email_updated') }}" class="w-full">
                    </div>

                    <p>{!! trans('pages.user-settings.email-change.email_updated_description', ['email' => $this->user->email]) !!}</p>
                </x-slot>
            </x-ark-modal>
        @endif
    </div>

    <div>
        @if ($confirmEmailChangeModal)
            <x-ark-modal
                :width-class="$confirmEmailChangeModalConfirmed ? 'max-w-lg' : 'max-w-xl'"
                title-class="header-2"
                wire-close="closeEmailConfirmationModal"
            >
                @if ($confirmEmailChangeModalConfirmed)
                    <x-slot name="title">
                        {{ trans('pages.user-settings.email-change.link_sent') }}
                    </x-slot>

                    <x-slot name="description">
                        <div class="flex justify-center my-8">
                            <img src="{{ asset('/images/modal/email-change-confirm.svg') }}" alt="{{ trans('pages.user-settings.email-change.link_sent') }}" class="w-full">
                        </div>

                        {!! trans('pages.user-settings.email-change.email_sent', ['email' => $email]) !!}
                    </x-slot>
                @else
                    <x-slot name="title">
                        {{ trans('pages.user-settings.email-change.confirm_new_email') }}
                    </x-slot>

                    <x-slot name="description">
                        <div class="flex justify-center my-8">
                            <img src="{{ asset('/images/modal/email-change.svg') }}" alt="{{ trans('pages.user-settings.email-change.confirm_new_email') }}" class="w-full">
                        </div>

                        <p class="mb-5">{{ trans('pages.user-settings.email-change.enter_password') }}</p>

                        <x-ark-password-toggle
                            name="password"
                            autocomplete="password"
                            class="w-full"
                            :errors="$errors"
                        />
                    </x-slot>

                    <x-slot name="buttons">
                        <button type="button" class="button-secondary" wire:click="closeEmailConfirmationModal">
                            {{ trans('actions.cancel') }}
                        </button>

                        <button type="button" class="button-primary" wire:click="confirmEmailChange" dusk="confirm-update">
                            {{ trans('actions.confirm') }}
                        </button>
                    </x-slot>
                @endif
            </x-ark-modal>
        @endif
    </div>
</div>
