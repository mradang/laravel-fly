<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRbacRoleUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 角色用户关联表
        Schema::create('rbac_role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id'); // 角色id
            $table->unsignedInteger('user_id'); // 用户id

            $table->unique(['role_id', 'user_id']); // 唯一索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rbac_role_user');
    }
}
