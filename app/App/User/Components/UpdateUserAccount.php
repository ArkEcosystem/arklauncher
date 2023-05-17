<?php

declare(strict_types=1);

namespace App\User\Components;

use ARKEcosystem\Foundation\UserInterface\Components\Concerns\HandleToast;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use ARKEcosystem\Foundation\UserInterface\Rules\CurrentPassword;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Support\Components\Concerns\InteractsWithUser;

final class UpdateUserAccount extends Component
{
    use InteractsWithUser;
    use HasModal;
    use HandleToast;

    public string $errorMessage;

    public string $email = '';

    public string $name = '';

    public string $password = '';

    public bool $confirmEmailChangeModal = false;

    public bool $confirmEmailChangeModalConfirmed = false;

    public ?string $signature = null;

    public bool $emailUpdated = false;

    public bool $invalidLink = false;

    public bool $expiredLink = false;

    // @phpstan-ignore-next-line
    protected $queryString = ['signature'];

    public function mount(Request $request) : void
    {
        if ($request->has(['email', 'signature'])) {
            // @phpstan-ignore-next-line
            if (! $request->hasValidSignature()) {
                $this->invalidLink = true;
            } elseif ($this->emailConfirmationLinkExpired($request)) {
                $this->expiredLink = true;
            } else {
                $this->updateEmail($request->input('email'));
            }

            // We want to remove the signature from the query string so it's not persisted if the user refreshes the page...
            $this->signature = null;
        }

        $this->name  = $this->user->name;
        $this->email = $this->user->email;
    }

    public function update() : void
    {
        Validator::make([
            'name'  => $this->name,
            'email' => $this->email,
        ], $this->validationRules())->validate();

        $this->user->fill([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        // If user wants to change email, we want to first have them confirm this change...
        if ($this->user->isDirty('email')) {
            $this->confirmEmailChangeModal = true;

            return;
        }

        $this->user->save();

        $this->toast(trans('pages.user-settings.profile.saved'));
    }

    public function closeEmailConfirmationModal() : void
    {
        $this->confirmEmailChangeModal          = false;
        $this->confirmEmailChangeModalConfirmed = false;
        $this->password                         = '';

        $this->closeModal();
    }

    public function confirmEmailChange() : void
    {
        $this->validate([
            'password' => ['required', 'string', new CurrentPassword($this->user)],
        ]);

        $this->user->sendEmailChangeConfirmationMail($this->email);

        $this->confirmEmailChangeModalConfirmed = true;
    }

    public function cancelConfirmationEmail() : void
    {
        $this->user->forgetMetaAttribute('email_to_update');
        $this->user->forgetMetaAttribute('email_to_update_stored_at');
    }

    public function resendConfirmationEmail() : void
    {
        $pendingEmail = $this->user->getMetaAttribute('email_to_update');

        // User has confirmed & updated the email in the meanwhile, so just refresh to avoid weird state...
        if ($pendingEmail === '' || $pendingEmail === null) {
            $this->redirectRoute('user.profile');

            return;
        }

        $this->user->sendEmailChangeConfirmationMail($this->user->getMetaAttribute('email_to_update'));

        $this->toast(trans('pages.user-settings.email-change.resend'));
    }

    public function closeEmailUpdatedFeedbackModal() : void
    {
        $this->emailUpdated = false;
        $this->invalidLink  = false;
        $this->expiredLink  = false;

        $this->closeModal();
    }

    public function render() : Renderable
    {
        return view('livewire.update-user-account');
    }

    private function validationRules() : array
    {
        return [
            'email' => ['required', 'max:255', 'email', Rule::unique('users')->ignore($this->user)],
            'name'  => 'required|string|max:50',
        ];
    }

    private function updateEmail(string $email) : void
    {
        $this->user->update([
            'email' => strtolower($email),
        ]);

        $this->user->forgetMetaAttribute('email_to_update');
        $this->user->forgetMetaAttribute('email_to_update_stored_at');

        $this->emailUpdated = true;
    }

    private function emailConfirmationLinkExpired(Request $request) : bool
    {
        $pendingEmail = $this->user->getMetaAttribute('email_to_update');

        if ($pendingEmail === null) {
            return true;
        }

        return strtolower($request->input('email')) !== strtolower($pendingEmail)
                ? true
                : Carbon::now()->gte(Carbon::createFromTimestamp($request->input('expires')));
    }
}
