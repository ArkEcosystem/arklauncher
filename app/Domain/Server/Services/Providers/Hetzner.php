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
use Domain\Server\Support\Rules\ValidHetznerServerName;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

final class Hetzner implements ServerProviderClient
{
    /**
     * Create a new Hetzner service instance.
     *
     * @param ServerProvider $serverProvider
     */
    public function __construct(private ServerProvider $serverProvider)
    {
    }

    /**
     * Validate the access token.
     *
     * @see https://docs.hetzner.cloud/#servers-get-all-servers
     *
     * @return bool
     */
    public function valid(): bool
    {
        try {
            $this->request('get', 'locations');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Create a new server.
     *
     * @see https://docs.hetzner.cloud/#servers-create-a-server
     *
     * @param ServerModel $serverToBeCreated
     *
     * @return Server
     */
    public function create(ServerModel $serverToBeCreated): Server
    {
        $image  = ServerProviderImage::where('uuid', $this->getImageId())->firstOrFail();

        $response = $this->request('post', 'servers', [
            'name'        => Str::slug($serverToBeCreated->name),
            'location'    => $serverToBeCreated->region->uuid,
            'server_type' => $serverToBeCreated->plan->uuid,
            'image'       => $image->uuid,
            'ssh_keys'    => [$this->findSecureShellKey((string) $serverToBeCreated->serverProvider->provider_key_id)->id],
        ])['server'];

        return $this->server($response['id']);
    }

    /**
     * Retrieve the server for the given ID.
     *
     * @see https://docs.hetzner.cloud/#servers-get-a-server
     *
     * @param int $id
     *
     * @return Server
     */
    public function server(int $id): Server
    {
        $server = $this->request('get', 'servers/'.$id)['server'];

        return new Server([
            'id'            => Arr::get($server, 'id'),
            'name'          => Arr::get($server, 'name'),
            'plan'          => Arr::get($server, 'server_type.id'),
            'memory'        => (int) Arr::get($server, 'server_type.memory') * 1024,
            'cores'         => (int) Arr::get($server, 'server_type.cores'),
            'disk'          => (int) Arr::get($server, 'server_type.disk'),
            'region'        => Arr::get($server, 'datacenter.name'),
            'status'        => Arr::get($server, 'status'),
            'remoteAddress' => Arr::get($server, 'public_net.ipv4.ip'),
            'image'         => Arr::get($server, 'image.name'),
        ]);
    }

    /**
     * Delete the server for the given ID.
     *
     * @see https://docs.hetzner.cloud/#servers-delete-a-server
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $response = $this->request('delete', 'servers/'.$id);

            HetznerExceptionHandler::newWithResponse($response)->handle();
        } catch (ServerNotFound $exception) {
            // Server was already deleted by the user. Nothing to do.
        }

        return true;
    }

    /**
     * Rename the server for the given ID.
     *
     * @see https://docs.hetzner.cloud/#servers-update-a-server
     *
     * @param int    $id
     * @param string $name
     *
     * @return bool
     */
    public function rename(int $id, string $name): bool
    {
        $response = $this->request('put', 'servers/'.$id, [
            'name' => $name,
        ]);

        HetznerExceptionHandler::newWithResponse($response)->handle();

        return true;
    }

    /**
     * Start the server for the given ID.
     *
     * @see https://docs.hetzner.cloud/#server-actions-power-on-a-server
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function start(int $id): bool
    {
        $response = $this->request('post', 'servers/'.$id.'/actions/poweron', ['body' => '{}']);

        HetznerExceptionHandler::newWithResponse($response)->handle();

        return true;
    }

    /**
     * Stop the server for the given ID.
     *
     * @see https://docs.hetzner.cloud/#server-actions-power-off-a-server
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function stop(int $id): bool
    {
        $response = $this->request('post', 'servers/'.$id.'/actions/poweroff', ['body' => '{}']);

        HetznerExceptionHandler::newWithResponse($response)->handle();

        return true;
    }

    /**
     * Reboot the server for the given ID.
     *
     * @see https://docs.hetzner.cloud/#server-actions-soft-reboot-a-server
     *
     * @param int $id
     *
     * @throws ClientException     if request fails
     * @throws ServerProviderError if response contains an error property
     *
     * @return bool
     */
    public function reboot(int $id): bool
    {
        $response = $this->request('post', 'servers/'.$id.'/actions/reboot', ['body' => '{}']);

        HetznerExceptionHandler::newWithResponse($response)->handle();

        return true;
    }

    /**
     * Get all available plans.
     *
     * @see https://docs.hetzner.cloud/#server-types-get-all-server-types
     *
     * @throws UnknownProperties
     */
    public function plans(): PlanCollection
    {
        /** @var array<int, array<string, string|array<int, array<string, string>>>> */
        $plans = Arr::get($this->request('get', 'server_types'), 'server_types', []);

        return new PlanCollection(
            items: collect($plans)
                ->map(fn (array $plan): Plan => new Plan([
                    'id'      => $plan['name'],
                    'disk'    => (int) $plan['disk'],
                    'memory'  => (int) $plan['memory'] * 1024,
                    'cores'   => (int) $plan['cores'],
                    // @phpstan-ignore-next-line
                    'regions' => collect($plan['prices'])->pluck('location')->toArray(),
                ]))->toArray()
        );
    }

    /**
     * Get all available regions.
     *
     * @see https://docs.hetzner.cloud/#locations-get-all-locations
     *
     * @throws UnknownProperties
     */
    public function regions(): RegionCollection
    {
        /** @var array<int, array<string, string>> */
        $regions = Arr::get($this->request('get', 'locations'), 'locations', []);

        return new RegionCollection(
            items: collect($regions)
                ->map(fn ($region): Region => new Region([
                    'id'   => $region['name'],
                    'name' => $region['description'],
                ]))->toArray()
        );
    }

    /**
     * Get all available images.
     *
     * @see https://docs.hetzner.cloud/#images-get-all-images
     *
     * @throws UnknownProperties
     */
    public function images(): ImageCollection
    {
        /** @var array<int, array<string, mixed>> */
        $images = Arr::get($this->request('get', 'images'), 'images', []);

        return new ImageCollection(
            items: collect($images)
                ->filter(fn ($image) => $image['name'] !== null)
                ->map(fn ($image): Image => new Image([
                    'id'   => $image['name'],
                    'name' => $image['description'],
                ]))->toArray()
        );
    }

    /**
     * Add an SSH key to the account.
     *
     * @see https://docs.hetzner.cloud/#ssh-keys-create-an-ssh-key
     *
     * @param string $name
     * @param string $publicKey
     *
     * @return SecureShellKey
     */
    public function createSecureShellKey(string $name, string $publicKey): SecureShellKey
    {
        $response = $this->request('post', '/ssh_keys', [
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
     * @see https://docs.hetzner.cloud/#ssh-keys-get-all-ssh-keys
     *
     * @param string $id
     *
     * @return SecureShellKey
     */
    public function findSecureShellKey(string $id): SecureShellKey
    {
        $response = $this->request('get', "/ssh_keys/{$id}")['ssh_key'];

        return new SecureShellKey([
            'id'        => $response['id'],
            'publicKey' => $response['public_key'],
        ]);
    }

    /**
     * Remove an SSH key from the account.
     *
     * @see https://docs.hetzner.cloud/#ssh-keys-delete-an-ssh-key
     *
     * @param string $id
     *
     * @return bool
     */
    public function deleteSecureShellKey(string $id): bool
    {
        $this->request('delete', "/ssh_keys/{$id}");

        return true;
    }

    /**
     * Get the image identifier.
     *
     * @return string
     */
    public function getImageId(): string
    {
        return 'ubuntu-22.04';
    }

    /**
     * Returns an instantiable Rule class for validate the server name.
     *
     * @return string
     */
    public static function nameValidator(): string
    {
        return ValidHetznerServerName::class;
    }

    /**
     * Make an HTTP request to Hetzner.
     *
     * @param string $method
     * @param string $path
     * @param array  $parameters
     */
    private function request(string $method, string $path, array $parameters = []): array
    {
        try {
            $response = Http::withToken($this->token())
                ->send($method, 'https://api.hetzner.cloud/v1/'.ltrim($path, '/'), $method === 'get' ? ['query' => $parameters] : ['json' => $parameters]);

            $response->throw();

            // Some API calls return a 204 No Content response.
            return $response->json() ?? [];
        } catch (RequestException $exception) {
            return HetznerExceptionHandler::new($exception)->handle();
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
