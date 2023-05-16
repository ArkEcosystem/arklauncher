<?php

declare(strict_types=1);

return [
    'server_started'  => 'Server ":server" has been started',
    'server_stopped'  => 'Server ":server" has been stopped',
    'server_rebooted' => 'Server ":server" has been rebooted',

    'server_not_found' => 'Server ":server" could not be found',

    'something_went_wrong'                 => 'Something went wrong.',
    'server_provider_authentication_error' => 'ARKLauncher was unable to access :provider using the ":name" API key. Please check that the API key exists on :provider or contact support.',

    'subjects' => [
        // Invitations...
        'collaborator_accepted_invitation' => 'Invitation accepted by collaborator <span class="font-semibold">:collaborator</span>',
        'collaborator_refused_invitation'  => 'Invitation declined by collaborator <span class="font-semibold">:collaborator</span>',
        'new_invitation'                   => 'You have been invited to join the team',

        // Tokens...
        'token_deleted' => 'Deletion of blockchain <span class="font-semibold">:token</span> successful',

        // Servers...
        'remote_server_limit_reached' => 'Server limit reached on <span class="font-semibold">:serverProvider</span>',
        'server_deleted'              => 'Deletion of server <span class="font-semibold">:server</span> successful',
        'server_deployed'             => 'Deployment of server <span class="font-semibold">:server</span> successful',
        'server_provisioned'          => 'Server <span class="font-semibold">:server</span> is ready to use',
        'server_failed_deployment'    => 'Failed to deploy server <span class="font-semibold">:server</span>',
        'server_ip_retrieval_failed'  => 'Failed to retrieve IP address for server <span class="font-semibold">:server</span>',
        'server_failed_to_create'     => 'Failed to create server <span class="font-semibold">:server</span> on :provider',
        'server_unreachable'          => 'Unable to reach server <span class="font-semibold">:server</span>',

        // Server Providers...
        'server_provider_auth_failed'           => 'Authentication failed for server provider <span class="font-semibold">:serverProvider</span>',
        'server_provider_image_unavailable'     => 'Image unavailable on server provider <span class="font-semibold">:serverProvider</span>',
        'server_provider_server_removal_failed' => 'Failed to remove server on server provider <span class="font-semibold">:serverProvider</span>',
        'server_provider_image_index_failed'    => 'Unable to index image data from <span class="font-semibold">:serverProvider</span>',
        'server_provider_region_index_failed'   => 'Unable to index region data from <span class="font-semibold">:serverProvider</span>',
        'server_provider_plan_index_failed'     => 'Unable to index plan data from <span class="font-semibold">:serverProvider</span>',

        // SSH Keys...
        'server_provider_ssh_key_limit_reached'    => 'SSH key limit reached on <span class="font-semibold">:serverProvider</span>',
        'server_provider_ssh_key_uniqueness_error' => 'Failed to add SSH key on server provider <span class="font-semibold">:serverProvider</span> - key already exists on provider',
        'server_provider_ssh_key_addition_failed'  => 'Failed to add SSH key for server provider <span class="font-semibold">:serverProvider</span>',
        'server_provider_ssh_key_removal_failed'   => 'Failed to remove SSH key on server provider <span class="font-semibold">:serverProvider</span>',
    ],
];
