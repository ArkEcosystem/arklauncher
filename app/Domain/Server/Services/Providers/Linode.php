<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Domain\Server\Contracts\ServerProviderClient;
use Domain\Server\DTO\Image;
use Domain\Server\DTO\ImageCollection;
use Domain\Server\DTO\Plan;
use Domain\Server\DTO\PlanCollection;
use Domain\Server\DTO\Region;
use Domain\Server\DTO\RegionCollection;
use Domain\Server\DTO\SecureShellKey;
use Domain\Server\DTO\Server;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server as ServerModel;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Support\Rules\ValidLinodeServerName;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

/**
 * Linode does not support cloud-init but instead uses StackScript.
 */
final class Linode implements ServerProviderClient
{
    /**
     * Create a new Linode service instance.
     *
     * @param ServerProvider $serverProvider
     */
    public function __construct(private ServerProvider $serverProvider)
    {
    }

    /**
     * Validate the access token.
     *
     * @see https://developers.linode.com/api/v4/account
     *
     * @return bool
     */
    public function valid(): bool
    {
        try {
            $this->request('get', 'account');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Create a new server.
     *
     * @see https://developers.linode.com/api/v4/linode-instances/#post
     *
     * @param ServerModel $serverToBeCreated
     *
     * @return Server
     */
    public function create(ServerModel $serverToBeCreated): Server
    {
        $image  = ServerProviderImage::where('uuid', $this->getImageId())->firstOrFail();

        $response = $this->request('post', 'linode/instances', [
            'label'           => Str::slug($serverToBeCreated->name),
            'region'          => $serverToBeCreated->region->uuid,
            'type'            => $serverToBeCreated->plan->uuid,
            'image'           => $image->uuid,
            'root_pass'       => $serverToBeCreated->sudo_password,
            'authorized_keys' => [$this->findSecureShellKey((string) $serverToBeCreated->serverProvider->provider_key_id)->publicKey],
        ]);

        return $this->server($response['id']);
    }

    /**
     * Retrieve the server for the given ID.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id
     *
     * @param int $id
     *
     * @return Server
     */
    public function server(int $id): Server
    {
        $server = $this->request('get', "linode/instances/{$id}");

        return new Server([
            'id'            => Arr::get($server, 'id'),
            'name'          => Arr::get($server, 'label'),
            'plan'          => Arr::get($server, 'type'),
            'memory'        => (int) Arr::get($server, 'specs.memory'),
            'cores'         => (int) Arr::get($server, 'specs.vcpus'),
            'disk'          => (int) Arr::get($server, 'specs.disk'),
            'region'        => Arr::get($server, 'region'),
            'status'        => Arr::get($server, 'status'),
            'remoteAddress' => Arr::get($server, 'ipv4.0'),
            'image'         => Arr::get($server, 'image'),
        ]);
    }

    /**
     * Delete the server for the given ID.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id/#delete
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->request('delete', 'linode/instances/'.$id);

        return true;
    }

    /**
     * Rename the server for the given ID.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id/#put
     *
     * @param int    $id
     * @param string $name
     *
     * @return bool
     */
    public function rename(int $id, string $name): bool
    {
        $this->request('put', 'linode/instances/'.$id, [
            'label' => $name,
        ]);

        return true;
    }

    /**
     * Start the server for the given ID.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id-boot/#post
     *
     * @param int $id
     *
     * @return bool
     */
    public function start(int $id): bool
    {
        $this->request('post', 'linode/instances/'.$id.'/boot');

        return true;
    }

    /**
     * Stop the server for the given ID.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id-shutdown/#post
     *
     * @param int $id
     *
     * @return bool
     */
    public function stop(int $id): bool
    {
        $this->request('post', 'linode/instances/'.$id.'/shutdown');

        return true;
    }

    /**
     * Reboot the server for the given ID.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id-reboot/#post
     *
     * @param int $id
     *
     * @return bool
     */
    public function reboot(int $id): bool
    {
        $this->request('post', 'linode/instances/'.$id.'/reboot');

        return true;
    }

    /**
     * Get all available plans.
     *
     * @see https://developers.linode.com/api/v4/linode-types
     *
     * @throws UnknownProperties
     */
    public function plans(): PlanCollection
    {
        /** @var array<int, array<string, string>> */
        $plans = Arr::get($this->request('get', 'linode/types'), 'data', []);

        return new PlanCollection(
            items: collect($plans)
                ->map(fn ($plan): Plan => new Plan([
                    'id'      => $plan['id'],
                    'disk'    => (int) $plan['disk'],
                    'memory'  => (int) $plan['memory'],
                    'cores'   => (int) $plan['vcpus'],
                    'regions' => [],
                ]))->toArray()
        );
    }

    /**
     * Get all available regions.
     *
     * @see https://developers.linode.com/api/v4/regions
     *
     * @throws UnknownProperties
     */
    public function regions(): RegionCollection
    {
        /** @var array<int, array<string, string>> */
        $regions = Arr::get($this->request('get', 'regions'), 'data', []);

        return new RegionCollection(
            items: collect($regions)
                ->map(fn ($region): Region => new Region([
                    'id'   => $region['id'],
                    'name' => $region['country'],
                ]))->toArray()
        );
    }

    /**
     * Get all available images.
     *
     * @see https://developers.linode.com/api/v4/images
     *
     * @throws UnknownProperties
     */
    public function images(): ImageCollection
    {
        /** @var array<int, array<string, string>> */
        $images = Arr::get($this->request('get', 'images'), 'data', []);

        return new ImageCollection(
            items: collect($images)
                ->map(fn ($image): Image => new Image([
                    'id'   => $image['id'],
                    'name' => $image['label'],
                ]))->toArray()
        );
    }

    /**
     * Add an SSH key to the account.
     *
     * @see https://developers.linode.com/api/v4/profile-sshkeys/#post
     *
     * @param string $name
     * @param string $publicKey
     *
     * @return SecureShellKey
     */
    public function createSecureShellKey(string $name, string $publicKey): SecureShellKey
    {
        $response = $this->request('post', '/profile/sshkeys', [
            'label'   => $name,
            'ssh_key' => trim($publicKey),
        ]);

        return new SecureShellKey([
            'id'        => $response['id'],
            'publicKey' => $response['ssh_key'],
        ]);
    }

    /**
     * Attempt to find an SSH key on the account.
     *
     * @see https://developers.linode.com/api/v4/profile-sshkeys-ssh-key-id
     *
     * @param string $id
     *
     * @return SecureShellKey
     */
    public function findSecureShellKey(string $id): SecureShellKey
    {
        $response = $this->request('get', "/profile/sshkeys/{$id}");

        return new SecureShellKey([
            'id'        => $response['id'],
            'publicKey' => $response['ssh_key'],
        ]);
    }

    /**
     * Remove an SSH key from the account.
     *
     * @see https://developers.linode.com/api/v4/profile-sshkeys-ssh-key-id/#delete
     *
     * @param string $id
     *
     * @return bool
     */
    public function deleteSecureShellKey(string $id): bool
    {
        $this->request('delete', "/profile/sshkeys/{$id}");

        return true;
    }

    /**
     * Get the image identifier.
     *
     * @return string
     */
    public function getImageId(): string
    {
        return 'linode/ubuntu22.04';
    }

    /**
     * Returns an instantiable Rule class for validate the server name.
     *
     * @return string
     */
    public static function nameValidator(): string
    {
        return ValidLinodeServerName::class;
    }

    /**
     * Make an HTTP request to Linode.
     *
     * @param string $method
     * @param string $path
     * @param array  $parameters
     *
     * @return array
     */
    private function request(string $method, string $path, array $parameters = []): array
    {
        try {
            $response = Http::withToken($this->token())
                ->withHeaders(['Content-Type'  => 'application/json'])
                ->send($method, 'https://api.linode.com/v4/'.ltrim($path, '/'), $method === 'get' ? ['query' => $parameters] : ['json' => $parameters]);

            $response->throw();

            return $response->json();
        } catch (RequestException $exception) {
            return LinodeExceptionHandler::new($exception)->handle();
        }
    }

    /**
     * Get the authentication token for the provider.
     *
     * @return string
     */
    private function token(): string
    {
        return $this->serverProvider->getMetaAttribute(ServerAttributeEnum::ACCESS_TOKEN);
    }
}
