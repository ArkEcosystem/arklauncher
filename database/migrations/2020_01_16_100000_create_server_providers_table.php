<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateServerProvidersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('server_providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('token_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->unique(['token_id', 'name']);
            $table->string('provider_key_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
        });

        Schema::create('server_provider_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid');
            $table->unsignedInteger('disk');
            $table->unsignedInteger('memory');
            $table->unsignedInteger('cores');
            $table->json('regions');
            $table->timestamps();

            $table->unique(['uuid']);
        });

        Schema::create('server_provider_server_provider_plan', function (Blueprint $table) {
            $table->foreignId('server_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_provider_plan_id')->constrained()->cascadeOnDelete();

            $table->unique(['server_provider_id', 'server_provider_plan_id']);
        });

        Schema::create('server_provider_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid');
            $table->string('name');
            $table->timestamps();

            $table->unique(['uuid']);
        });

        Schema::create('server_provider_server_provider_region', function (Blueprint $table) {
            $table->foreignId('server_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_provider_region_id')->constrained()->cascadeOnDelete();

            $table->unique(['server_provider_id', 'server_provider_region_id']);
        });

        Schema::create('server_provider_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid');
            $table->string('name');
            $table->timestamps();

            $table->unique(['uuid']);
        });

        Schema::create('server_provider_server_provider_image', function (Blueprint $table) {
            $table->foreignId('server_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_provider_image_id')->constrained()->cascadeOnDelete();

            $table->unique(['server_provider_id', 'server_provider_image_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('server_provider_server_provider_plan');
        Schema::dropIfExists('server_provider_server_provider_region');
        Schema::dropIfExists('server_provider_server_provider_image');
        Schema::dropIfExists('server_provider_plans');
        Schema::dropIfExists('server_provider_regions');
        Schema::dropIfExists('server_provider_images');
        Schema::dropIfExists('server_providers');
    }
}
