<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\FlushMediaCache;
use App\Listeners\ListenForActivityLogs;
use Domain\Token\Events\NetworkCreated;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Events\ServerProviderCreated;
use Domain\Token\Events\ServerProviderDeleted;
use Domain\Token\Events\ServerProviderUpdated;
use Domain\Token\Events\ServerUpdating;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Listeners\ServerCreated\CreateServerOnProvider;
use Domain\Token\Listeners\ServerDeleted\DestroyServerOnServerProvider;
use Domain\Token\Listeners\ServerDeleted\NotifyUsersOfServerDeletion;
use Domain\Token\Listeners\ServerProviderDeleted\ForgetServerProviderTokenConfiguration;
use Domain\Token\Listeners\ServerProviderDeleted\TriggerSecureShellKeyRemovalFromServerProvider;
use Domain\Token\Listeners\ServerProviderUpdated\IndexServerProviderRelatedData;
use Domain\Token\Listeners\ServerUpdating\UpdateServerProviderName;
use Domain\Token\Listeners\TokenCreated\CreateDefaultNetworks;
use Domain\Token\Listeners\TokenDeleted\NotifyCollaborators;
use Domain\Token\Listeners\TokenDeleted\PurgeTokenResources;
use Domain\Token\Listeners\TokenDeleted\RemoveTokenNotifications;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // General
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MediaHasBeenAdded::class => [
            FlushMediaCache::class,
        ],

        // Tokens
        TokenCreated::class => [
            CreateDefaultNetworks::class,
        ],
        TokenDeleted::class => [
            PurgeTokenResources::class,
            RemoveTokenNotifications::class,
            NotifyCollaborators::class,
        ],

        // Networks
        NetworkCreated::class => [
            //
        ],

        // Servers
        ServerCreated::class => [
            CreateServerOnProvider::class,
        ],
        ServerDeleted::class => [
            NotifyUsersOfServerDeletion::class,
            DestroyServerOnServerProvider::class,
        ],
        ServerUpdating::class => [
            UpdateServerProviderName::class,
        ],

        // Server Providers
        ServerProviderCreated::class => [
            //
        ],
        ServerProviderDeleted::class => [
            ForgetServerProviderTokenConfiguration::class,
            TriggerSecureShellKeyRemovalFromServerProvider::class,
        ],
        ServerProviderUpdated::class => [
            IndexServerProviderRelatedData::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        ListenForActivityLogs::class,
    ];
}
