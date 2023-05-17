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
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Models\Server as ServerModel;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Support\Rules\ValidDigitalOceanServerName;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

final class DigitalOcean implements ServerProviderClient
{
    /**
     * Create a new DigitalOcean service instance.
     *
     * @param ServerProvider $serverProvider
     */
    public function __construct(private ServerProvider $serverProvider)
    {
    }

    /**
     * Validate the access token.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#get-user-information
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
     * @see https://developers.digitalocean.com/documentation/v2/#create-a-new-droplet
     *
     * @param ServerModel  $serverToBeCreated
     *
     * @return Server
     */
    public function create(ServerModel $serverToBeCreated): Server
    {
        $image  = ServerProviderImage::where('uuid', $this->getImageId())->firstOrFail();

        $server = $this->request('post', 'droplets', [
            'name'               => Str::slug($serverToBeCreated->name),
            'region'             => $serverToBeCreated->region->uuid,
            'size'               => $serverToBeCreated->plan->uuid,
            'image'              => $image->uuid,
            'backups'            => false,
            'ipv6'               => false,
            'private_networking' => false,
            'monitoring'         => false,
            'ssh_keys'           => [$this->findSecureShellKey((string) $serverToBeCreated->serverProvider->provider_key_id)->id],
        ])['droplet'];

        return $this->server($server['id']);
    }

    /**
     * Retrieve the server for the given ID.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#retrieve-an-existing-droplet-by-id
     *
     * @param int $id
     *
     * @return Server
     */
    public function server(int $id): Server
    {
        $server = $this->request('get', 'droplets/'.$id)['droplet'];

        /** @var array<int, array<string, string>> */
        $response = Arr::get($server, 'networks.v4');

        /** @var array<string, string> */
        $network = collect($response)->firstWhere('type', 'public');

        return new Server([
            'id'            => Arr::get($server, 'id'),
            'name'          => Arr::get($server, 'name'),
            'plan'          => Arr::get($server, 'size_slug'),
            'memory'        => (int) Arr::get($server, 'memory'),
            'cores'         => (int) Arr::get($server, 'vcpus'),
            'disk'          => (int) Arr::get($server, 'disk'),
            'region'        => Arr::get($server, 'region.slug'),
            'status'        => Arr::get($server, 'status'),
            'remoteAddress' => Arr::get($network, 'ip_address'),
            'image'         => Arr::get($server, 'image.slug'),
        ]);
    }

    /**
     * Delete the server for the given ID.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#delete-a-droplet
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $this->request('delete', 'droplets/'.$id);
        } catch (ServerNotFound $exception) {
            // Server was already deleted by the user. Nothing to do.
        }

        return true;
    }

    /**
     * Rename the server for the given ID.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#rename-a-droplet
     *
     * @param int    $id
     * @param string $name
     *
     * @return bool
     */
    public function rename(int $id, string $name): bool
    {
        $this->request('post', 'droplets/'.$id.'/actions', [
            'type' => 'rename',
            'name' => $name,
        ]);

        return true;
    }

    /**
     * Start the server for the given ID.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#power-on-a-droplet
     *
     * @param int $id
     *
     * @return bool
     */
    public function start(int $id): bool
    {
        $this->request('post', 'droplets/'.$id.'/actions', ['type' => 'power_on']);

        return true;
    }

    /**
     * Stop the server for the given ID.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#power-off-a-droplet
     *
     * @param int $id
     *
     * @return bool
     */
    public function stop(int $id): bool
    {
        $this->request('post', 'droplets/'.$id.'/actions', ['type' => 'power_off']);

        return true;
    }

    /**
     * Reboot the server for the given ID.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#reboot-a-droplet
     *
     * @param int $id
     *
     * @return bool
     */
    public function reboot(int $id): bool
    {
        $this->request('post', 'droplets/'.$id.'/actions', ['type' => 'reboot']);

        return true;
    }

    /**
     * Get all available plans.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#list-all-sizes
     *
     * @throws UnknownProperties
     */
    public function plans(): PlanCollection
    {
        /** @var array<int, array<string, string>> */
        $array = Arr::get($this->request('get', 'sizes'), 'sizes', []);
        $plans = collect($array)->where('available', true);

        return new PlanCollection(
            items: $plans->map(fn (array $plan): Plan => new Plan([
                'id'      => $plan['slug'],
                'disk'    => (int) $plan['disk'],
                'memory'  => (int) $plan['memory'],
                'cores'   => (int) $plan['vcpus'],
                'regions' => $plan['regions'],
            ]))->toArray()
        );
    }

    /**
     * Get all available regions.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#list-all-regions
     *
     * @throws UnknownProperties
     */
    public function regions(): RegionCollection
    {
        /** @var array<int, array<string, string>> */
        $array   = Arr::get($this->request('get', 'regions'), 'regions', []);
        $regions = collect($array)->where('available', true);

        return new RegionCollection(
            items: $regions->map(fn (array $region): Region => new Region([
                'id'   => $region['slug'],
                'name' => $region['name'],
            ]))->toArray()
        );
    }

    /**
     * Get all available images.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#images
     *
     * @throws UnknownProperties
     */
    public function images(): ImageCollection
    {
        /** @var array<int, array<string, string>> */
        $images = Arr::get($this->request('get', 'images', ['type' => 'distribution']), 'images', []);

        return new ImageCollection(
            items: collect($images)
                ->map(fn (array $image): Image => new Image([
                    'id'   => $image['slug'] ?? $image['id'],
                    'name' => $image['name'],
                ]))->toArray()
        );
    }

    /**
     * Add an SSH key to the account.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#create-a-new-key
     *
     * @param string $name
     * @param string $publicKey
     *
     * @return SecureShellKey
     */
    public function createSecureShellKey(string $name, string $publicKey): SecureShellKey
    {
        $response = $this->request('post', '/account/keys', [
            'name'       => $name,
            'public_key' => $publicKey,
        ])['ssh_key'];

        return new SecureShellKey([
            'id'        => $response['id'],
            'publicKey' => $response['public_key'],
        ]);
    }

    /**
     * Attempt to find an SSH key on the account.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#list-all-keys
     *
     * @param string $id
     *
     * @return SecureShellKey
     */
    public function findSecureShellKey(string $id): SecureShellKey
    {
        $response = $this->request('get', "/account/keys/{$id}")['ssh_key'];

        return new SecureShellKey([
            'id'        => $response['id'],
            'publicKey' => $response['public_key'],
        ]);
    }

    /**
     * Remove an SSH key from the account.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#destroy-a-key
     *
     * @param string $id
     *
     * @return bool
     */
    public function deleteSecureShellKey(string $id): bool
    {
        $this->request('delete', "/account/keys/{$id}");

        return true;
    }

    /**
     * Get the image identifier.
     *
     * @return string
     */
    public function getImageId(): string
    {
        return 'ubuntu-18-04-x64';
    }

    /**
     * Returns an instantiable Rule class for validate the server name.
     *
     * @return string
     */
    public static function nameValidator(): string
    {
        return ValidDigitalOceanServerName::class;
    }

    /**
     * Make an HTTP request to DigitalOcean.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  array  $parameters
     * @throws ServerProviderError
     * @throws \Domain\SecureShell\Exceptions\SecureShellKeyAlreadyInUse
     * @throws \Domain\Server\Exceptions\ServerLimitExceeded
     * @throws \Domain\Server\Exceptions\ServerNotFound
     * @return array
     */
    private function request(string $method, string $path, array $parameters = []): array
    {
        try {
            $response = Http::withToken($this->token())
                ->withHeaders(['Content-Type'  => 'application/json'])
                ->send($method, 'https://api.digitalocean.com/v2/'.ltrim($path, '/'), $method === 'get' ? ['query' => $parameters] : ['json' => $parameters]);

            $response->throw();

            if (in_array($response->status(), [201, 204], true) && $response->body() === '') {
                return [];
            }

            return $response->json();
        } catch (RequestException $exception) {
            return DigitalOceanExceptionHandler::new($exception)->handle();
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
