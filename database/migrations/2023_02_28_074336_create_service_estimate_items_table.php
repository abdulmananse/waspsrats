<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceEstimateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_estimate_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('service_id');
            $table->bigInteger('item_id');
            $table->float('cost')->default(0)->nullable();
            $table->tinyInteger('quantity')->default(0)->nullable();
            $table->bigInteger('tax_id_1')->default(0)->nullable();
            $table->bigInteger('tax_id_2')->default(0)->nullable();
            $table->float('total_cost')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_estimate_items');
    }
}
