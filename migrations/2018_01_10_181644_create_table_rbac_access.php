<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRbacAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 角色权限关联表
        Schema::create('rbac_access', function (Blueprint $table) {
            $table->unsignedInteger('role_id'); // 角色id
            $table->unsignedInteger('node_id'); // 功能节点id

            $table->unique(['role_id', 'node_id']); // 唯一索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rbac_access');
    }
}
