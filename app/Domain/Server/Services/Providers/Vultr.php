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
use Domain\Server\Models\Server as ServerModel;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Support\Rules\ValidVultrServerName;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

final class Vultr implements ServerProviderClient
{
    /**
     * Create a new Vultr service instance.
     *
     * @param ServerProvider $serverProvider
     */
    public function __construct(private ServerProvider $serverProvider)
    {
    }

    /**
     * Validate the access token.
     *
     * @see https://www.vultr.com/api/#auth_info
     *
     * @return bool
     */
    public function valid(): bool
    {
        try {
            $this->request('get', 'auth/info');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Create a new server.
     *
     * @see https://www.vultr.com/api/#server_create
     *
     * @param ServerModel $serverToBeCreated
     *
     * @return Server
     */
    public function create(ServerModel $serverToBeCreated): Server
    {
        $image  = ServerProviderImage::where('uuid', $this->getImageId())->firstOrFail();

        $response = $this->request('post', 'server/create', [
            'label'     => Str::slug($serverToBeCreated->name),
            'DCID'      => $serverToBeCreated->region->uuid,
            'VPSPLANID' => $serverToBeCreated->plan->uuid,
            'OSID'      => $image->uuid,
            'SSHKEYID'  => $this->findSecureShellKey((string) $serverToBeCreated->serverProvider->provider_key_id)->id,
        ]);

        return $this->server((int) $response['SUBID']);
    }

    /**
     * Retrieve the server for the given ID.
     *
     * @see https://www.vultr.com/api/#server_server_list
     *
     * @param int $id
     *
     * @return Server
     */
    public function server(int $id): Server
    {
        $serverList = $this->request('get', 'server/list');

        if (! array_key_exists($id, $serverList)) {
            throw new ServerNotFound();
        }

        $server = $serverList[$id];

        $remoteAddress = Arr::get($server, 'main_ip');

        return new Server([
            'id'            => Arr::get($server, 'SUBID'),
            'name'          => Arr::get($server, 'label'),
            'plan'          => Arr::get($server, 'VPSPLANID'),
            'memory'        => (int) filter_var(Arr::get($server, 'ram'), FILTER_SANITIZE_NUMBER_INT),
            'cores'         => (int) filter_var(Arr::get($server, 'vcpu_count'), FILTER_SANITIZE_NUMBER_INT),
            'disk'          => (int) filter_var(Arr::get($server, 'disk'), FILTER_SANITIZE_NUMBER_INT),
            'region'        => Arr::get($server, 'location'),
            'status'        => Arr::get($server, 'status'),
            'remoteAddress' => $remoteAddress === '0.0.0.0' ? null : $remoteAddress,
            'image'         => Arr::get($server, 'os'),
        ]);
    }

    /**
     * Delete the server for the given ID.
     *
     * @see https://www.vultr.com/api/#server_destroy
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->request('post', 'server/destroy', ['SUBID' => $id]);

        return true;
    }

    /**
     * Rename the server for the given ID.
     *
     * @see https://www.vultr.com/api/#server_label_set
     *
     * @param int    $id
     * @param string $name
     *
     * @return bool
     */
    public function rename(int $id, string $name): bool
    {
        $this->request('post', 'server/label_set', [
            'SUBID' => $id,
            'label' => $name,
        ]);

        return true;
    }

    /**
     * Start the server for the given ID.
     *
     * @see https://www.vultr.com/api/#server_start
     *
     * @param int $id
     *
     * @return bool
     */
    public function start(int $id): bool
    {
        $this->request('post', 'server/start', ['SUBID' => $id]);

        return true;
    }

    /**
     * Stop the server for the given ID.
     *
     * @see https://www.vultr.com/api/#server_halt
     *
     * @param int $id
     *
     * @return bool
     */
    public function stop(int $id): bool
    {
        $this->request('post', 'server/halt', ['SUBID' => $id]);

        return true;
    }

    /**
     * Reboot the server for the given ID.
     *
     * @see https://www.vultr.com/api/#server_reboot
     *
     * @param int $id
     *
     * @return bool
     */
    public function reboot(int $id): bool
    {
        $this->request('post', 'server/reboot', ['SUBID' => $id]);

        return true;
    }

    /**
     * Get all available plans.
     *
     * @see https://www.vultr.com/api/#plans_plan_list
     *
     * @throws UnknownProperties
     */
    public function plans(): PlanCollection
    {
        $plans = $this->request('get', 'plans/list');

        return new PlanCollection(
            items: collect($plans)
                ->transform(fn ($plan): Plan => new Plan([
                    'id'      => $plan['VPSPLANID'],
                    'disk'    => (int) $plan['disk'],
                    'memory'  => (int) $plan['ram'],
                    'cores'   => (int) $plan['vcpu_count'],
                    'regions' => $plan['available_locations'],
                ]))->toArray()
        );
    }

    /**
     * Get all available regions.
     *
     * @see https://www.vultr.com/api/#regions_region_list
     *
     * @throws UnknownProperties
     */
    public function regions(): RegionCollection
    {
        $regions = $this->request('get', 'regions/list');

        return new RegionCollection(
            items: collect($regions)
                ->transform(fn ($region): Region => new Region([
                    'id'   => $region['DCID'],
                    'name' => $region['name'],
                ]))->toArray()
        );
    }

    /**
     * Get all available images.
     *
     * @see https://www.vultr.com/api/#os_os_list
     *
     * @throws UnknownProperties
     */
    public function images(): ImageCollection
    {
        $images = $this->request('get', 'os/list');

        return new ImageCollection(
            items: collect($images)
                ->transform(fn ($image): Image => new Image([
                    'id'   => $image['OSID'],
                    'name' => $image['name'],
                ]))->toArray()
        );
    }

    /**
     * Add an SSH key to the account.
     *
     * @see https://www.vultr.com/api/#sshkey_create
     *
     * @param string $name
     * @param string $publicKey
     *
     * @return SecureShellKey
     */
    public function createSecureShellKey(string $name, string $publicKey): SecureShellKey
    {
        $response = $this->request('post', '/sshkey/create', [
            'name'    => $name,
            'ssh_key' => $publicKey,
        ]);

        return new SecureShellKey([
            'id'        => $response['SSHKEYID'],
            'publicKey' => null,
        ]);
    }

    /**
     * Attempt to find an SSH key on the account.
     *
     * @see https://www.vultr.com/api/#sshkey_sshkey_list
     *
     * @param string $id
     *
     * @return SecureShellKey
     */
    public function findSecureShellKey(string $id): SecureShellKey
    {
        $response = $this->request('get', '/sshkey/list');
        $response = collect(array_values($response))->first(fn ($key) => $key['SSHKEYID'] === trim($id));

        return new SecureShellKey([
            'id'        => $response['SSHKEYID'],
            'publicKey' => $response['ssh_key'],
        ]);
    }

    /**
     * Remove an SSH key from the account.
     *
     * @see https://www.vultr.com/api/#sshkey_destroy
     *
     * @param string $id
     *
     * @return bool
     */
    public function deleteSecureShellKey(string $id): bool
    {
        $this->request('post', '/sshkey/destroy', ['SSHKEYID' => $id]);

        return true;
    }

    /**
     * Get the image identifier.
     *
     * @return string
     */
    public function getImageId(): string
    {
        return '270'; //'Ubuntu 22.04 x64';
    }

    /**
     * Make an HTTP request to Vultr.
     *
     * @param  string  $method
     * @param  string  $path
     * @param  array  $parameters
     *
     * @return array
     */
    public function request(string $method, string $path, array $parameters = []): array
    {
        try {
            $response = Http::withHeaders(['API-Key' => $this->token()])
                ->send($method, 'https://api.vultr.com/v1/'.ltrim($path, '/'), ['form_params' => $parameters]);

            $response->throw();

            return $response->json();
        } catch (RequestException $exception) {
            return VultrExceptionHandler::new($exception)->handle();
        }
    }

    /**
     * Returns an instantiable Rule class for validate the server name.
     *
     * @return string
     */
    public static function nameValidator(): string
    {
        return ValidVultrServerName::class;
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
