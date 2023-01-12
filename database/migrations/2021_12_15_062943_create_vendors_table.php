<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('design Primary Key');
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->string('mobile', 50);
            $table->double('price', 10, 2)->comment('Price per piece');
            $table->foreignUuid('job_id')->references('id')->on('jobs')->comment('foreign key connected jobs table');
            $table->string('status', 20)->comment('Vendor status enable or disable');
            $table->timestamps();
            $table->softDeletes();
            $table->string('ip_address',50)->comment('User last login ip'); 
            $table->uuid('created_by')->nullable()->comment('Record created by. 0 for self');
            $table->uuid('updated_by')->nullable()->comment('Record updated by. 0 for self');
        });

        DB::statement('ALTER TABLE vendors ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}
