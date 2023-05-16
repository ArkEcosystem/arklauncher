<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface LogsActivity
{
    public function subject() : Model;

    public function description() : string;

    public function causer() : ?Model;

    public function payload() : array;
}
