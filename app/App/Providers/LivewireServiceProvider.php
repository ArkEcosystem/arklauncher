<?php

declare(strict_types=1);

namespace App\Providers;

use App\Collaborator\Components\CollaboratorPermissionsModal;
use App\Collaborator\Components\DeclineInvitationModal;
use App\Collaborator\Components\DeleteCollaborator;
use App\Collaborator\Components\InviteCollaborator;
use App\Collaborator\Components\LeaveTeamModal;
use App\Collaborator\Components\MailedInvitations;
use App\Collaborator\Components\ManageCollaborators;
use App\Collaborator\Components\UpdateCollaborator;
use App\Http\Components\DownloadInstallScript;
use App\Http\Components\ManageWelcomeScreens;
use App\Http\Components\Modals\BetaNotice;
use App\Http\Components\UseDefaultsModal;
use App\SecureShell\Components\CreateSecureShellKey;
use App\SecureShell\Components\DeleteSecureShellKey;
use App\Server\Components\CreateServer;
use App\Server\Components\DeleteServer;
use App\Server\Components\DeleteServerProvider;
use App\Server\Components\ManageServerConfiguration;
use App\Server\Components\ManageServerProviders;
use App\Server\Components\RedirectOnServerProviderCompletion;
use App\Server\Components\RenameServer;
use App\Server\Components\ServerDeploymentTracker;
use App\Token\Components\ActiveServers;
use App\Token\Components\CreateTokenModal;
use App\Token\Components\DeleteToken;
use App\Token\Components\DeployBlockchain;
use App\Token\Components\LogoUpload;
use App\Token\Components\ManageTokens;
use App\Token\Components\ManageTokenSecureShellKeys;
use App\Token\Components\UpdateToken;
use App\User\Components\ManageSecureShellKeys;
use App\User\Components\UpdateUserAccount;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Support\Components\YoutubeModal;

final class LivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('create-secure-shell-key', CreateSecureShellKey::class);
        Livewire::component('create-server', CreateServer::class);
        Livewire::component('create-token-modal', CreateTokenModal::class);
        Livewire::component('delete-secure-shell-key', DeleteSecureShellKey::class);
        Livewire::component('delete-server-provider', DeleteServerProvider::class);
        Livewire::component('delete-server', DeleteServer::class);
        Livewire::component('rename-server', RenameServer::class);
        Livewire::component('delete-token', DeleteToken::class);
        Livewire::component('deploy-blockchain', DeployBlockchain::class);
        Livewire::component('decline-invitation-modal', DeclineInvitationModal::class);
        Livewire::component('download-install-script', DownloadInstallScript::class);
        Livewire::component('leave-team-modal', LeaveTeamModal::class);
        Livewire::component('logo-upload', LogoUpload::class);
        Livewire::component('manage-secure-shell-keys', ManageSecureShellKeys::class);
        Livewire::component('manage-server-configuration', ManageServerConfiguration::class);
        Livewire::component('manage-server-providers', ManageServerProviders::class);
        Livewire::component('manage-token-secure-shell-keys', ManageTokenSecureShellKeys::class);
        Livewire::component('manage-tokens', ManageTokens::class);
        Livewire::component('manage-welcome-screens', ManageWelcomeScreens::class);
        Livewire::component('modals.beta-notice', BetaNotice::class);
        Livewire::component('server-deployment-tracker', ServerDeploymentTracker::class);
        Livewire::component('update-token', UpdateToken::class);
        Livewire::component('update-user-account', UpdateUserAccount::class);
        Livewire::component('use-defaults-modal', UseDefaultsModal::class);
        Livewire::component('youtube-modal', YoutubeModal::class);
        Livewire::component('redirect-on-server-provider-completion', RedirectOnServerProviderCompletion::class);

        Livewire::component('collaborator-permissions-modal', CollaboratorPermissionsModal::class);
        Livewire::component('delete-collaborator', DeleteCollaborator::class);
        Livewire::component('invite-collaborator', InviteCollaborator::class);
        Livewire::component('manage-collaborators', ManageCollaborators::class);
        Livewire::component('update-collaborator', UpdateCollaborator::class);
        Livewire::component('mailed-invitations', MailedInvitations::class);
        Livewire::component('active-servers', ActiveServers::class);
    }
}
