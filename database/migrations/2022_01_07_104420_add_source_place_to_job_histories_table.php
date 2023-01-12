<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourcePlaceToJobHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_histories', function (Blueprint $table) {
            //
            $table->text('source_place')->nullable()->after('vendor_id');
            $table->foreignUuid('source_vendor_id')->nullable()->references('id')->on('vendors')->comment('foreign key connected vendors table')->after('vendor_id');
            $table->renameColumn('vendor_id', 'assign_vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_histories', function (Blueprint $table) {
            $table->dropColumn('source_place');
            $table->dropColumn('source_vendor_id');
            $table->renameColumn('assign_vendor_id', 'vendor_id');
        });
    }
}
