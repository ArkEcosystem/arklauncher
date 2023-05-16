<?php

declare(strict_types=1);

namespace Tests\Analysis;

use GrahamCampbell\Analyzer\AnalysisTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class AnalysisTest extends TestCase
{
    use AnalysisTrait;

    public function getPaths(): array
    {
        return [
            __DIR__.'/../../app',
            __DIR__.'/../../database/factories',
            __DIR__.'/../../database/migrations',
            __DIR__.'/../../database/seeders',
            __DIR__.'/../../tests',
        ];
    }

    public function getIgnored(): array
    {
        return [
            'Facades\Domain\SecureShell\Services\ShellProcessRunner',
            'Facades\Support\Services\BIP39',
            'Laravel\Scout\Builder',
        ];
    }
}
