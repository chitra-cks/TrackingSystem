<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('Role Primary Key');
            $table->string('name');
            $table->json('permission')->nullable()->comment('Role permission');
            $table->string('ip_address', 50)->comment('User last login ip');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE roles ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
