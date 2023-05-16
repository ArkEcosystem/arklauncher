<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Models\Token;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TokenPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        Token::factory()->count(10)->create()->each(function ($token): void {
            TokenCreated::dispatch($token);

            $token->setStatus(TokenStatusEnum::FINISHED);
        });
    }
}
