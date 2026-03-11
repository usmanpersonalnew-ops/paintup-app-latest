<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('project_billing_details', function (Blueprint $table) {
            $table->string('order_id')->nullable()->after('pincode');
        });
    }

    public function down()
    {
        Schema::table('project_billing_details', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }


};
