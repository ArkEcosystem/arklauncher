<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Aws\Ec2\Ec2Client;
use Domain\Server\Contracts\ServerProviderClient;
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
use Domain\Server\Support\Rules\ValidAWSServerName;
use Illuminate\Support\Arr;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

final class AWS implements ServerProviderClient
{
    /**
     * The server provider client.
     *
     * @var Ec2Client
     */
    private Ec2Client $client;

    /**
     * Create a new AWS service instance.
     *
     * @param ServerProvider $serverProvider
     */
    public function __construct(private ServerProvider $serverProvider)
    {
        $this->client = new Ec2Client([
            'credentials' => [
                'key'    => $serverProvider->getMetaAttribute(ServerAttributeEnum::ACCESS_KEY),
                'secret' => $serverProvider->getMetaAttribute(ServerAttributeEnum::ACCESS_TOKEN),
            ],
            'region'  => config('aws.region'),
            'version' => config('aws.version'),
        ]);
    }

    /**
     * Validate the access token.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_DescribeInstances.html
     *
     * @return bool
     */
    public function valid(): bool
    {
        try {
            $this->client->describeInstances();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Create a new server.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_RunInstances.html
     *
     * @param  ServerModel  $serverToBeCreated
     *
     * @return Server
     */
    public function create(ServerModel $serverToBeCreated): Server
    {
        $server = $this->client->runInstances([
            'ImageId'      => config('aws.imageIds.ubuntu-18-04-lts'),
            'MinCount'     => 1,
            'MaxCount'     => 1,
            'InstanceType' => $serverToBeCreated->plan->uuid,
        ])->get('Instances');

        return $this->server($server['InstanceId']);
    }

    /**
     * Retrieve the server for the given ID.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_DescribeInstances.html
     *
     * @param int $id
     *
     * @return Server
     */
    public function server($id): Server
    {
        $server = $this->client->describeInstances([
            'Filter1.Name'  => 'instance-id',
            'Filter1.Value' => $id,
        ])->get('Reservations')[0]['Instances'][0];

        $instanceType = $this->client->describeInstanceTypes([
            'InstanceTypes' => [$server['InstanceType']],
        ])->get('InstanceTypes')[0];

        return new Server([
            'id'            => Arr::get($server, 'InstanceId'),
            'name'          => Arr::get($server, 'InstanceType'),
            'plan'          => Arr::get($server, 'InstanceType'),
            'memory'        => (int) Arr::get($instanceType, 'MemoryInfo.SizeInMiB'),
            'cores'         => (int) Arr::get($instanceType, 'VCpuInfo.DefaultVCpus'),
            'disk'          => (int) Arr::get($instanceType, 'InstanceStorageInfo.TotalSizeInGB') * 1024,
            'region'        => Arr::get($server, 'Placement.AvailabilityZone'),
            'status'        => Arr::get($server, 'State.Name'),
            'remoteAddress' => Arr::get($server, 'NetworkInterfaces.0.Association.PublicIp'),
        ]);
    }

    /**
     * Delete the server for the given ID.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_TerminateInstances.html
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $this->client->terminateInstances(['InstanceIds' => [$id]]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Rename the server for the given ID.
     *
     * @see @TODO
     *
     * @param int    $id
     * @param string $name
     *
     * @return bool
     */
    public function rename(int $id, string $name): bool
    {
        // @TODO: Implement this

        return true;
    }

    /**
     * Start the server for the given ID..
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_StartInstances.html
     *
     * @param int $id
     *
     * @return bool
     */
    public function start(int $id): bool
    {
        try {
            $this->client->startInstances(['InstanceIds' => [$id]]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Stop the server for the given ID.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_StopInstances.html
     *
     * @param int $id
     *
     * @return bool
     */
    public function stop(int $id): bool
    {
        try {
            $this->client->stopInstances(['InstanceIds' => [$id]]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Reboot the server for the given ID.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_RebootInstances.html
     *
     * @param int $id
     *
     * @return bool
     */
    public function reboot(int $id): bool
    {
        try {
            $this->client->rebootInstances(['InstanceIds' => [$id]]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get all available plans.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_DescribeAvailabilityZones.html
     *
     * @throws UnknownProperties
     */
    public function plans(): PlanCollection
    {
        $nextToken = null;

        $plans = [];

        do {
            $response = $this->client->describeInstanceTypes(array_filter(['NextToken' => $nextToken]));

            $plans = array_merge($plans, $response->get('InstanceTypes'));

            $nextToken = $response->get('NextToken');
        } while ($nextToken);

        return new PlanCollection(
            items: collect($plans)
                ->transform(fn ($plan): Plan => new Plan([
                    'id'      => $plan['InstanceType'],
                    'disk'    => (int) Arr::get($plan, 'InstanceStorageInfo.TotalSizeInGB') * 1024,
                    'memory'  => (int) Arr::get($plan, 'MemoryInfo.SizeInMiB'),
                    'cores'   => (int) Arr::get($plan, 'VCpuInfo.DefaultVCpus'),
                    'regions' => [config('aws.region')],
                ]))->toArray()
        );
    }

    /**
     * Get all available regions.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_DescribeRegions.html
     *
     * @throws UnknownProperties
     */
    public function regions(): RegionCollection
    {
        /** @var array<int, array<string, string>> */
        $array = $this->client->describeRegions()->get('Regions');

        $regions = collect($array)->where('OptInStatus', 'opt-in-not-required');

        return new RegionCollection(
            items: $regions->map(fn (array $region): Region => new Region([
                'id'   => $region['RegionName'],
                'name' => $region['Endpoint'],
            ]))->toArray()
        );
    }

    /**
     * Get all available images.
     *
     * @see https://docs.aws.amazon.com/AWSEC2/latest/APIReference/API_DescribeImages.html
     *
     * @throws UnknownProperties
     */
    public function images(): ImageCollection
    {
        // TODO : To implement
        return new ImageCollection([]);
    }

    /**
     * Add an SSH key to the account.
     *
     * @see TBD
     *
     * @param string $name
     * @param string $publicKey
     *
     * @return SecureShellKey
     */
    public function createSecureShellKey(string $name, string $publicKey): SecureShellKey
    {
        /* @phpstan-ignore-next-line  */
        return $this->client->request('post', '/account/keys', [
            'name'       => config('app.name'),
            'public_key' => $publicKey,
        ])['ssh_key']['id'];
    }

    /**
     * Attempt to find an SSH key on the account.
     *
     * @see https://developers.digitalocean.com/documentation/v2/#list-all-keys
     *
     * @return SecureShellKey
     */
    public function findSecureShellKey(string $publicKey): SecureShellKey
    {
        // @phpstan-ignore-next-line
        $response = $this->client->request('get', '/account/keys');

        // @phpstan-ignore-next-line
        $response = collect($response)->first(fn ($key) => $key['public_key'] === trim($publicKey))['ssh_key'];

        return new SecureShellKey([
            'id'   => $response['id'],
            'name' => $response['name'],
        ]);
    }

    /**
     * Remove an SSH key from the account.
     *
     * @see TBD
     *
     * @param string $id
     *
     * @return bool
     */
    public function deleteSecureShellKey(string $id): bool
    {
        try {
            /* @phpstan-ignore-next-line  */
            $this->client->request('delete', "/account/keys/{$this->serverProvider->provider_key_id}");

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get the image identifier.
     *
     * @return string
     */
    public function getImageId(): string
    {
        return 'Ubuntu 18.04 x64';
    }

    /**
     * Returns an instanciable Rule class for validate the server name.
     *
     * @return string
     */
    public static function nameValidator(): string
    {
        return ValidAWSServerName::class;
    }
}
