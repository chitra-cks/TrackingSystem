<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('design Primary Key');
            $table->string('title')->comment('Design name or title');
            $table->string('design_no')->comment('Design Number');
            $table->string('status', 20)->comment('Design status enable or disable');
            $table->timestamps();
            $table->softDeletes();
            $table->string('ip_address',50)->comment('User last login ip'); 
            $table->uuid('created_by')->nullable()->comment('Record created by. 0 for self');
            $table->uuid('updated_by')->nullable()->comment('Record updated by. 0 for self');
        });

        DB::statement('ALTER TABLE designs ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('designs');
    }
}
