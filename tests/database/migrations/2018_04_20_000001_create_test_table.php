<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_table', function (Blueprint $table) {

            // === 欄位 ===
            // 資料識別碼
            $table->char('id', 12)
                ->comment('資料識別碼');

            // 資料名稱(電影名稱)
            $table->string('name')->default('')
                ->comment('資料名稱(電影名稱)');

            // 資料判別欄位1(上映時間)
            $table->datetime('start_at')
                ->comment('資料判別欄位1(上映時間)');

            // 資料判別欄位2(下映時間)
            $table->datetime('end_at')
                ->comment('資料判別欄位2(下映時間)');

            // 測試欄位1
            $table->string('test1')->default('')
                ->comment('測試欄位1');

            // 測試欄位2
            $table->string('test2')->default('')
                ->comment('測試欄位2');

            // 測試欄位3
            $table->string('test3')->default('')
                ->comment('測試欄位3');

            // 當前狀態 (未上映、上映中、已上映)
            $table->string('status')->default('')
                ->comment('當前狀態 (未上映、上映中、已上映)');

            // 當前階段 (before、now、after)
            $table->string('stage')->default('')
                ->comment('當前階段 (before、now、after)');

            // 建立時間
            $table->datetime('created_at')
                ->comment('建立時間');

            // 最後更新
            $table->datetime('updated_at')
                ->comment('最後更新');
        });

        // ----
        //   定義索引與約束資料
        // ----
        Schema::table('test_table', function ($table) {

            // 指定主鍵
            $table->primary(['id']);

            // 軟刪除
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_table');
    }
}
