<?php

declare(strict_types=1);

namespace Domain\SecureShell\Contracts;

interface Script
{
    /**
     * Get the name of the script.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the contents of the script.
     *
     * @return string
     */
    public function script(): string;

    /**
     * The user that the script should be run as.
     *
     * @return string
     */
    public function user(): string;

    /**
     * Get the timeout for the script.
     *
     * @return int
     */
    public function timeout(): int;
}
