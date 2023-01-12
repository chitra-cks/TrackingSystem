<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_histories', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('job history Primary Key');
            $table->foreignUuid('vendor_id')->references('id')->on('vendors')->comment('foreign key connected vendors table');
            $table->foreignUuid('job_id')->references('id')->on('jobs')->comment('foreign key connected jobs table');
            $table->foreignUuid('design_id')->references('id')->on('designs')->comment('foreign key connected designs table');
            $table->integer('quantity');
            $table->string('voucher_number', 200);
            $table->string('voucher_bill', 200)->nullable();
            $table->string('voucher_bill_mimetype', 100)->nullable();
            $table->string('status', 20)->comment('job status inprocess, return, cancel, completed');
            $table->date('pickup_date')->comment('Pickup date');
            $table->date('job_start_date')->comment('Pickup date');
            $table->date('job_end_date')->comment('Pickup date');
            $table->timestamps();
            $table->softDeletes();
            $table->string('ip_address', 50)->comment('User last login ip');
            $table->uuid('created_by')->nullable()->comment('Record created by. 0 for self');
            $table->uuid('updated_by')->nullable()->comment('Record updated by. 0 for self');
        });

        DB::statement('ALTER TABLE job_histories ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_histories');
    }
}
