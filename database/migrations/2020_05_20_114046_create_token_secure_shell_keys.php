<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateTokenSecureShellKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secure_shell_key_token', function (Blueprint $table) {
            $table->foreignId('token_id')->constrained()->cascadeOnDelete();
            $table->foreignId('secure_shell_key_id')->constrained()->cascadeOnDelete();

            $table->unique(['token_id', 'secure_shell_key_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secure_shell_key_token');
    }
}
