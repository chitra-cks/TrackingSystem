<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('User Primary Key');
            $table->foreignUuid('role_id')->references('id')->on('roles')->comment('foreign key connected roles table');
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->string('mobile', 50)->comment('Vendor address');
            $table->text('address')->nullable()->comment('Vendor address');
            $table->char('gender', 1);
            $table->string('email', 150)->unique()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status', 20);
            $table->rememberToken();
            $table->string('resetpassword_token', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('ip_address', 50)->comment('User last login ip');
            $table->uuid('created_by')->nullable()->comment('Record created by. 0 for self');
            $table->uuid('updated_by')->nullable()->comment('Record updated by. 0 for self');
        });

        DB::statement('ALTER TABLE users ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
