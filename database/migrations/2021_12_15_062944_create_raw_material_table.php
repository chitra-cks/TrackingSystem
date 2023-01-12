<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('design Primary Key');
            $table->string('quantity')->comment('Design name or title');
            $table->string('source')->comment('Design Number');
            $table->double('price', 10, 2)->comment('Price per piece');
            $table->string('voucher_number', 200)->comment('Voucher number');
            $table->string('voucher', 200)->nullable()->comment('Voucher image/PDF name');
            $table->string('voucher_mimetype', 100)->nullable()->comment('Mime type to identify the type');
            $table->string('LR_number', 200)->comment('LR number');
            $table->string('LR', 200)->nullable()->comment('LR image/PDF name');
            $table->string('LR_mimetype', 100)->nullable()->comment('Mime type to identify the type');
            $table->string('status', 20)->comment('Design status enable or disable');
            $table->date('pickup_date')->comment('Pickup date');
            $table->timestamps();
            $table->softDeletes();
            $table->string('ip_address', 50)->comment('User last login ip');
            $table->uuid('created_by')->nullable()->comment('Record created by. 0 for self');
            $table->uuid('updated_by')->nullable()->comment('Record updated by. 0 for self');
        });

        DB::statement('ALTER TABLE raw_material ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('raw_material');
    }
}
