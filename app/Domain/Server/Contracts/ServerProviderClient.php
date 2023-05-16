<?php

declare(strict_types=1);

namespace Domain\Server\Contracts;

use Domain\Server\DTO\ImageCollection;
use Domain\Server\DTO\PlanCollection;
use Domain\Server\DTO\RegionCollection;
use Domain\Server\DTO\SecureShellKey;
use Domain\Server\DTO\Server;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Models\Server as ServerModel;
use GuzzleHttp\Exception\ClientException;

interface ServerProviderClient
{
    /**
     * Validate the access token.
     *
     * @return bool
     */
    public function valid(): bool;

    /**
     * Create a new server.
     *
     * @param ServerModel $server
     *
     * @return Server
     */
    public function create(ServerModel $server): Server;

    /**
     * Retrieve the server for the given ID.
     *
     * @param int $id
     *
     * @return Server
     */
    public function server(int $id): Server;

    /**
     * Delete the server for the given ID.
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Rename the server for the given ID and Name.
     *
     * @param int    $id
     * @param string $name
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function rename(int $id, string $name): bool;

    /**
     * Start the server for the given ID..
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function start(int $id): bool;

    /**
     * Stop the server for the given ID.
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function stop(int $id): bool;

    /**
     * Reboot the server for the given ID.
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function reboot(int $id): bool;

    /**
     * Get all available plans.
     *
     * @return PlanCollection
     */
    public function plans(): PlanCollection;

    /**
     * Get all available regions.
     *
     * @return RegionCollection
     */
    public function regions(): RegionCollection;

    /**
     * Get all available images.
     *
     * @return ImageCollection
     */
    public function images(): ImageCollection;

    /**
     * Add an SSH key to the account.
     *
     * @param string $name
     * @param string $publicKey
     *
     * @return SecureShellKey
     */
    public function createSecureShellKey(string $name, string $publicKey): SecureShellKey;

    /**
     * Attempt to find an SSH key on the account.
     *
     * @param string $id
     *
     * @return SecureShellKey
     */
    public function findSecureShellKey(string $id): SecureShellKey;

    /**
     * Remove an SSH key from the account.
     *
     * @param string $id
     *
     * @throws ClientException if request fails
     *
     * @return bool
     */
    public function deleteSecureShellKey(string $id): bool;

    /**
     * Get the image identifier.
     *
     * @return string
     */
    public function getImageId(): string;

    /**
     * Returns an instanciable Rule class for validate the server name.
     *
     * @return string
     */
    public static function nameValidator(): string;
}
