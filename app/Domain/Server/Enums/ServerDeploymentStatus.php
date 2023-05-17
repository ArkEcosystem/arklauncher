<?php

declare(strict_types=1);

namespace Domain\Server\Enums;

final class ServerDeploymentStatus
{
    public const PROVISIONING = 'provisioning';

    public const CONFIGURING_LOCALE = 'configuring_locale';

    public const INSTALLING_SYSTEM_DEPENDENCIES = 'installing_system_dependencies';

    public const INSTALLING_NODEJS = 'installing_nodejs';

    public const INSTALLING_YARN = 'installing_yarn';

    public const INSTALLING_PM2 = 'installing_pm2';

    public const INSTALLING_PROGRAM_DEPENDENCIES = 'installing_program_dependencies';

    public const INSTALLING_POSTGRESQL = 'installing_postgresql';

    public const INSTALLING_NTP = 'installing_ntp';

    public const INSTALLING_JEMALLOC = 'installing_jemalloc';

    public const UPDATING_SYSTEM = 'updating_system';

    public const SECURING_NODE = 'securing_node';

    public const GENERATING_NETWORK_CONFIGURATION = 'generating_network_configuration';

    public const STORE_CONFIG = 'storing_config';

    public const FETCH_CONFIG = 'fetching_config';

    public const CONFIGURING_FORGER = 'configuring_forger';

    public const INSTALLING_CORE = 'installing_core';

    public const CREATING_CORE_ALIAS = 'creating_core_alias';

    public const CONFIGURING_DATABASE = 'configuring_database';

    public const INSTALLING_DOCKER = 'installing_docker';

    public const CLONING_EXPLORER = 'cloning_explorer';

    public const CONFIGURING_EXPLORER = 'configuring_explorer';

    public const BUILDING_EXPLORER = 'building_explorer';

    public const CREATING_BOOT_SCRIPT = 'creating_boot_script';

    public const STARTING_PROCESSES = 'starting_processes';

    public const PROVISIONED = 'online';

    public const FAILED_BUILDING_EXPLORER = 'failed_building_explorer';

    public const FAILED_CLONING_EXPLORER = 'failed_cloning_explorer';

    public function getGenesisStates(): array
    {
        return [
            'genesis' => array_merge(
                $this->serverStates(),
                $this->coreStates(['genesis' => true]),
                $this->explorerStates(),
                $this->finalizingStates(),
            ),
        ];
    }

    public function getSeedStates(): array
    {
        return [
            'seed' => array_merge(
                $this->serverStates(),
                $this->coreStates(),
                $this->finalizingStates(),
            ),
        ];
    }

    public function getRelayStates(): array
    {
        return [
            'relay' => array_merge(
                $this->serverStates(),
                $this->coreStates(),
                $this->finalizingStates(),
            ),
        ];
    }

    public function getForgerStates(): array
    {
        return [
            'forger' => array_merge(
                $this->serverStates(),
                $this->coreStates(['forger' => true]),
                $this->finalizingStates(),
            ),
        ];
    }

    public function getExplorerStates(): array
    {
        return [
            'explorer' => array_merge(
                $this->serverStates(),
                $this->coreStates(),
                $this->explorerStates(),
                $this->finalizingStates(),
            ),
        ];
    }

    public function getGroupStates(): array
    {
        return array_merge(
            $this->getGenesisStates(),
            $this->getSeedStates(),
            $this->getRelayStates(),
            $this->getForgerStates(),
            $this->getExplorerStates(),
        );
    }

    private function serverStates(): array
    {
        return [
            'server' => [
                self::PROVISIONING,
                self::CONFIGURING_LOCALE,
                self::INSTALLING_SYSTEM_DEPENDENCIES,
                self::INSTALLING_NODEJS,
                self::INSTALLING_YARN,
                self::INSTALLING_PM2,
                self::INSTALLING_PROGRAM_DEPENDENCIES,
                self::INSTALLING_POSTGRESQL,
                self::INSTALLING_NTP,
                self::INSTALLING_JEMALLOC,
                self::UPDATING_SYSTEM,
                self::SECURING_NODE,
            ],
        ];
    }

    private function coreStates(array $options = []): array
    {
        $states = [
            self::INSTALLING_CORE,
            self::CREATING_CORE_ALIAS,
            self::GENERATING_NETWORK_CONFIGURATION,
            self::STORE_CONFIG,
            self::FETCH_CONFIG,
            self::CONFIGURING_DATABASE,
            self::CONFIGURING_FORGER,
        ];

        if (! array_key_exists('genesis', $options)) {
            $states = array_diff($states, [self::GENERATING_NETWORK_CONFIGURATION, self::STORE_CONFIG]);
        } else {
            $states = array_diff($states, [self::FETCH_CONFIG]);
        }

        if (! array_key_exists('forger', $options)) {
            $states = array_diff($states, [self::CONFIGURING_FORGER]);
        }

        return [
            'core' => $states,
        ];
    }

    private function explorerStates(): array
    {
        $states = [
            self::INSTALLING_DOCKER,
            self::CLONING_EXPLORER,
            self::CONFIGURING_EXPLORER,
            self::BUILDING_EXPLORER,
        ];

        return [
            'explorer' => $states,
        ];
    }

    private function finalizingStates(): array
    {
        return [
            'finalizing' => [
                self::STARTING_PROCESSES,
                self::CREATING_BOOT_SCRIPT,
                self::PROVISIONED,
            ],
        ];
    }
}
