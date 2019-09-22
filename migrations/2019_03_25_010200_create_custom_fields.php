<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 定制字段表
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model'); // 模型名
            $table->unsignedInteger('group_id'); // 字段分组 ID
            $table->string('name'); // 字段名
            $table->unsignedTinyInteger('type'); // 字段类型（1文本，2选项，3时间，4图片）
            $table->text('options'); // 选项列表，JSON
            $table->unsignedTinyInteger('required'); // 是否必填（1必填，0选填）
            $table->unsignedInteger('sort'); // 排序
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
        Schema::dropIfExists('custom_fields');
    }
}
