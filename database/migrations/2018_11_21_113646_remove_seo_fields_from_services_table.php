<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSeoFieldsFromServicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('seo_title');
            $table->dropColumn('seo_description');
            $table->dropForeign(['seo_image_file_id']);
            $table->dropColumn('seo_image_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('seo_title')->after('referral_url');
            $table->string('seo_description')->after('seo_title');
            $table->uuid('seo_image_file_id')->nullable()->after('seo_description');
            $table->foreign('seo_image_file_id')->references('id')->on('files');
        });
    }
}
