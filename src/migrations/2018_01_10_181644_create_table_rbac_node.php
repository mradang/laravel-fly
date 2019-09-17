<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRbacNode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 功能节点表
        Schema::create('rbac_node', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name'); // 功能节点
            $table->string('description')->nullable(); // 功能说明
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rbac_node');
    }
}
