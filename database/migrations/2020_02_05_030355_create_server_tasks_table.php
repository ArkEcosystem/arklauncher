<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateServerTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('user');
            $table->integer('exit_code')->nullable();
            $table->longText('script');
            $table->longText('output');
            $table->json('options');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_tasks');
    }
}
