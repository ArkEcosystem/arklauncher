<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_provider_plan_id')->constrained();
            $table->foreignId('server_provider_region_id')->constrained();
            $table->foreignId('server_provider_image_id')->constrained();
            $table->string('name');
            $table->text('user_password')->nullable();
            $table->text('sudo_password')->nullable();
            $table->text('delegate_passphrase')->nullable();
            $table->text('delegate_password')->nullable();
            $table->unsignedInteger('provider_server_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('provisioning_job_dispatched_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->string('preset');
            $table->string('core_version')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}
