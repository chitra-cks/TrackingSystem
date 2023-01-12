<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('job phase Primary Key');
            $table->string('title')->comment('Job phase name or title');
            $table->string('status', 20)->comment('Job status enable or disable');
            $table->timestamps();
            $table->softDeletes();
            $table->string('ip_address',50)->comment('User last login ip'); 
            $table->uuid('created_by')->nullable()->comment('Record created by. 0 for self');
            $table->uuid('updated_by')->nullable()->comment('Record updated by. 0 for self');
        });

        DB::statement('ALTER TABLE jobs ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
