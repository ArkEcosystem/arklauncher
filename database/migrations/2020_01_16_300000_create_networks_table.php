<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateNetworksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('networks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('token_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('networks');
    }
}
