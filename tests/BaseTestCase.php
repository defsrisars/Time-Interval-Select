<?php

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class BaseTestCase extends TestCase
{
    /**
     * @var ConsoleOutput 終端器輸出器
     */
    protected $console;

    /**
     * @var \Faker\Factory 假資料產生器
     */
    protected $faker;

    /**
     * Set-up 等同建構式
     */
    protected function setUp()
    {
        parent::setUp();

        $this->console = new ConsoleOutput();
        $this->faker = \Faker\Factory::create();
    }

    /**
     * 注入此套件所必要資料庫 migrations, seeds, factories
     */
    protected function injectDatabase()
    {

        // 避免 MySQL 版本過舊產生的問題
        Schema::defaultStringLength(191);

        // 載入測試用的 migrations 檔案
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/database/migrations'),
        ]);

        // 載入輔助資料產生的工廠類別
        $this->withFactories(__DIR__ . '/database/factories');
    }

    /**
     * 初始化 User Table
     */
    protected function initTestTable()
    {
        Schema::dropIfExists('test_table');

        // 測試時建立臨時的 table
        Schema::create('users', function ($table) {
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

        // 測試完畢後再刪掉臨時建立的使用者 table
        $this->beforeApplicationDestroyed(function () {
            Schema::dropIfExists('test_table');
        });
    }

    /**
     * 測試時的 Package Providers 設定
     *
     *  ( 等同於原 laravel 設定 config/app.php 的 Autoloaded Service Providers )
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Orchestra\Database\ConsoleServiceProvider::class,
        ];
    }

    /**
     * 測試時的 Class Aliases 設定
     *
     * ( 等同於原 laravel 中設定 config/app.php 的 Class Aliases )
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [

        ];
    }

    /**
     * 測試時的時區設定
     *
     * ( 等同於原 laravel 中設定 config/app.php 的 Application Timezone )
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'Asia/Taipei';
    }

    /**
     * 測試時使用的 HTTP Kernel
     *
     * ( 等同於原 laravel 中 app/HTTP/kernel.php )
     * ( 若需要用自訂時，把 Orchestra\Testbench\Http\Kernel 改成自己的 )
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel',
            'Orchestra\Testbench\Http\Kernel'
        );
    }

    /**
     * 測試時使用的 Console Kernel
     *
     * ( 等同於原 laravel 中 app/Console/kernel.php )
     * ( 若需要用自訂時，把 Orchestra\Testbench\Console\Kernel 改成自己的 )
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Console\Kernel',
            'Orchestra\Testbench\Console\Kernel'
        );
    }


    /**
     * 測試時的環境設定
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // 若有環境變數檔案，嘗試著讀取使用
        if (file_exists(dirname(__DIR__) . '/.env')) {
            $dotenv = new Dotenv\Dotenv(dirname(__DIR__));
            $dotenv->load();
        }

        // 定義測試時使用的資料庫
        $app['config']->set('database.connections.testing', [
            'driver' => env('TEST_DB_CONNECTION', 'sqlite'),
            'host' => env('TEST_DB_HOST', 'localhost'),
            'database' => env('TEST_DB_DATABASE', ':memory:'),
            'port' => env('TEST_DB_PORT'),
            'username' => env('TEST_DB_USERNAME'),
            'password' => env('TEST_DB_PASSWORD'),
            'prefix' => env('TEST_DB_PREFIX'),
        ]);
        $app['config']->set('database.default', 'testing');
    }
}
