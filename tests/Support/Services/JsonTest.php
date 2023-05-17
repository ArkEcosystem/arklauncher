<?php

declare(strict_types=1);

use Support\Services\Json;

it('can parse a deployer config', function () {
    $contents = $this->fixture('config');

    $expected                    = json_decode($contents, true);
    $expected['bridgechainPath'] = '\\'.$expected['bridgechainPath'];
    $expected['explorerPath']    = '\\'.$expected['explorerPath'];

    expect(Json::parseConfig($contents))->toBe($expected);
});
