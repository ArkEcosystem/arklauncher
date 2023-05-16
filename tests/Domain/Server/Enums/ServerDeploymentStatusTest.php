<?php

declare(strict_types=1);

use Domain\Server\Enums\ServerDeploymentStatus;

it('a genesis deployment has states', function () {
    $serverDeploymentStatus = new ServerDeploymentStatus();
    $deploymentStates       = $serverDeploymentStatus->getGenesisStates();

    expect($deploymentStates)->toHaveKey('genesis');

    expect($deploymentStates['genesis'])->toHaveKey('server');
    expect($deploymentStates['genesis'])->toHaveKey('core');
    expect($deploymentStates['genesis'])->toHaveKey('explorer');
    expect($deploymentStates['genesis'])->toHaveKey('finalizing');

    expect(in_array('storing_config', $deploymentStates['genesis']['core'], true))->toBeTrue();
    expect(in_array('generating_network_configuration', $deploymentStates['genesis']['core'], true))->toBeTrue();
    expect(in_array('configuring_forger', $deploymentStates['genesis']['core'], true))->toBeFalse();
});

it('a seed deployment has states', function () {
    $serverDeploymentStatus = new ServerDeploymentStatus();
    $deploymentStates       = $serverDeploymentStatus->getSeedStates();

    expect($deploymentStates)->toHaveKey('seed');

    expect($deploymentStates['seed'])->toHaveKey('server');
    expect($deploymentStates['seed'])->toHaveKey('core');
    expect($deploymentStates['seed'])->toHaveKey('finalizing');

    expect(in_array('storing_config', $deploymentStates['seed']['core'], true))->toBeFalse();
    expect(in_array('generating_network_configuration', $deploymentStates['seed']['core'], true))->toBeFalse();
    expect(in_array('configuring_forger', $deploymentStates['seed']['core'], true))->toBeFalse();
});

it('a relay deployment has states', function () {
    $serverDeploymentStatus = new ServerDeploymentStatus();
    $deploymentStates       = $serverDeploymentStatus->getRelayStates();

    expect($deploymentStates)->toHaveKey('relay');

    expect($deploymentStates['relay'])->toHaveKey('server');
    expect($deploymentStates['relay'])->toHaveKey('core');
    expect($deploymentStates['relay'])->toHaveKey('finalizing');

    expect(in_array('storing_config', $deploymentStates['relay']['core'], true))->toBeFalse();
    expect(in_array('generating_network_configuration', $deploymentStates['relay']['core'], true))->toBeFalse();
    expect(in_array('configuring_forger', $deploymentStates['relay']['core'], true))->toBeFalse();
});

it('a forger deployment has states', function () {
    $serverDeploymentStatus = new ServerDeploymentStatus();
    $deploymentStates       = $serverDeploymentStatus->getForgerStates();

    expect($deploymentStates)->toHaveKey('forger');

    expect($deploymentStates['forger'])->toHaveKey('server');
    expect($deploymentStates['forger'])->toHaveKey('core');
    expect($deploymentStates['forger'])->toHaveKey('finalizing');

    expect(in_array('storing_config', $deploymentStates['forger']['core'], true))->toBeFalse();
    expect(in_array('generating_network_configuration', $deploymentStates['forger']['core'], true))->toBeFalse();
    expect(in_array('configuring_forger', $deploymentStates['forger']['core'], true))->toBeTrue();
});

it('an explorer deployment has states', function () {
    $serverDeploymentStatus = new ServerDeploymentStatus();
    $deploymentStates       = $serverDeploymentStatus->getExplorerStates();

    expect($deploymentStates)->toHaveKey('explorer');

    expect($deploymentStates['explorer'])->toHaveKey('server');
    expect($deploymentStates['explorer'])->toHaveKey('core');
    expect($deploymentStates['explorer'])->toHaveKey('explorer');
    expect($deploymentStates['explorer'])->toHaveKey('finalizing');

    expect(in_array('storing_config', $deploymentStates['explorer']['core'], true))->toBeFalse();
    expect(in_array('generating_network_configuration', $deploymentStates['explorer']['core'], true))->toBeFalse();
    expect(in_array('configuring_forger', $deploymentStates['explorer']['core'], true))->toBeFalse();
});

it('a group state holds all deployment states', function () {
    $serverDeploymentStatus = new ServerDeploymentStatus();
    $deploymentStates       = $serverDeploymentStatus->getGroupStates();

    expect($deploymentStates)->toHaveKey('genesis');
    expect($deploymentStates)->toHaveKey('seed');
    expect($deploymentStates)->toHaveKey('relay');
    expect($deploymentStates)->toHaveKey('forger');
    expect($deploymentStates)->toHaveKey('explorer');
});
