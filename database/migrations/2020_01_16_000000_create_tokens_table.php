<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coin_id')->constrained()->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('slug')->nullable();
            $table->json('config')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->json('keypair')->nullable();
            $table->timestamp('onboarded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('token_users', function (Blueprint $table) {
            $table->foreignId('token_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->json('permissions');
            $table->timestamps();

            $table->unique(['token_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('token_users');
        Schema::dropIfExists('tokens');
    }
}
