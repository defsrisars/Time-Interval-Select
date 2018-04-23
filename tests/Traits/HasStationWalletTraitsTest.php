<?php

use Illuminate\Support\Facades\Schema;
use SuperPlatform\StationWallet\Models\StationWallet;
use Ulid\Ulid;

class HasStationWalletTraitsTest extends BaseTestCase
{
    /**
     * 測試前的初始動作
     */
    public function setUp()
    {
        parent::setUp();

        // 初始化 User Table，測試需要的假 User 寫入此 Table
        $this->initUserTable();

        // 注入此套件會使用到的 database 相關東西
        $this->injectDatabase();

        // 暫時忽略 StationWallet 白名單的檢查
        StationWallet::unguard();
    }

    /**
     * 初始化 User Table
     */
    protected function initUserTable()
    {
        // 測試時建立臨時的使用者 table
        Schema::create('users', function ($table) {
            $table->char('id', 26)->primary();
            $table->string('name');
            $table->datetime('created_at');
            $table->datetime('updated_at');
        });

        // 測試完畢後再刪掉臨時建立的使用者 table
        $this->beforeApplicationDestroyed(function () {
            Schema::dropIfExists('users');
        });
    }

    /**
     * 測試透過使用者_elquent_建立主錢包
     *
     * 測試對象：masterWallet、buildMasterWallets、StationWalletExistsException
     */
    public function test_測試透過使用者_elquent_建立主錢包()
    {
        // Arrange
        $users = [];
        $userAmount = 2;
        for ($i = 0; $i < $userAmount; $i++) {
            $user = new User();
            $user->id = (string) Ulid::generate();
            $user->name = $this->faker->name;
            $user->save();
            array_push($users, $user);
        }
        $user1 = $users[0];
        $user2 = $users[1];

        // Act
        $user1->buildMasterWallets();
        $user2Wallet = $user2->buildMasterWallets('freezing', 100);

        // Assert
        $this->expectException('SuperPlatform\StationWallet\Exceptions\StationWalletExistsException');
        $user1Wallet = $user1->buildMasterWallets();
        $this->assertEquals(0, $user1Wallet['balance']);
        $this->assertEquals('active', $user1Wallet['status']);
        $this->assertEquals(100, $user2Wallet['balance']);
        $this->assertEquals('freezing', $user2Wallet['status']);
    }

    /**
     * 測試透過使用者_elquent_建立所有遊戲站錢包資料，並預設全部錢包都啟用
     *
     * 測試對象：buildStationWallets
     */
    public function test_測試透過使用者_elquent_建立_所有_遊戲站錢包資料()
    {
        // Arrange
        // Users
        $users = [];
        $userAmount = 5;
        for ($i = 0; $i < $userAmount; $i++) {
            $user = new User();
            $user->id = (string) Ulid::generate();
            $user->name = $this->faker->name;
            $user->save();
            array_push($users, $user);
        }

        // Act
        // 建立錢包
        $wallets = [];
        foreach ($users as $user) {
            array_push($wallets, $user->buildStationWallets());
        }
        $user1 = $users[0];
        $user2 = $users[1];
        $user3 = $users[2];
        $user4 = $users[3];
        $user5 = $users[4];

        // Assert
        // 五位使用者
        // user 1
        $userWallets = collect($user1->wallets())->keyBy('station')->toArray();
        $this->assertEquals('active', $userWallets['all_bet']['status']);
        $this->assertEquals('active', $userWallets['bingo']['status']);
        $this->assertEquals('active', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 2
        $userWallets = collect($user2->wallets())->keyBy('station')->toArray();
        $this->assertEquals('active', $userWallets['all_bet']['status']);
        $this->assertEquals('active', $userWallets['bingo']['status']);
        $this->assertEquals('active', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 3
        $userWallets = collect($user3->wallets())->keyBy('station')->toArray();
        $this->assertEquals('active', $userWallets['all_bet']['status']);
        $this->assertEquals('active', $userWallets['bingo']['status']);
        $this->assertEquals('active', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 4
        $userWallets = collect($user4->wallets())->keyBy('station')->toArray();
        $this->assertEquals('active', $userWallets['all_bet']['status']);
        $this->assertEquals('active', $userWallets['bingo']['status']);
        $this->assertEquals('active', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 5
        $userWallets = collect($user5->wallets())->keyBy('station')->toArray();
        $this->assertEquals('active', $userWallets['all_bet']['status']);
        $this->assertEquals('active', $userWallets['bingo']['status']);
        $this->assertEquals('active', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);

    }

    /**
     * 測試透過使用者_elquent_建立部分遊戲站錢包資料，並預設全部錢包都啟用
     *
     * 測試對象：buildStationWallets
     */
    public function test_測試透過使用者_elquent_建立_部分_遊戲站錢包資料()
    {
        // Arrange
        // Users
        $users = [];
        $userAmount = 1;
        for ($i = 0; $i < $userAmount; $i++) {
            $user = new User();
            $user->id = (string) Ulid::generate();
            $user->name = $this->faker->name;
            $user->save();
            array_push($users, $user);
        }
        $users = collect($users);
        // Wallets
        $stations = [
            'bingo',
            'sa_gaming',
        ];

        // Act
        // 建立錢包
        $users->mapWithKeys(function ($item) use ($stations) {
            $user = User::where('id', '=', $item->id)->first();
            return $user->buildStationWallets($stations);
        });
        $user = $users->shift();

        // Assert
        // user
        $this->assertTrue(empty($user->wallets('all_bet')->toArray()));
        $this->assertTrue(empty($user->wallets('holdem')->toArray()));
        $this->assertTrue(!empty($user->wallets('bingo')->first()->toArray()));
        $this->assertTrue(!empty($user->wallets('sa_gaming')->first()->toArray()));
    }

    /**
     * 測試透過使用者_elquent_建立部分遊戲站錢包資料兩次，第二次僅建立未建立的錢包
     *
     * 測試對象：buildStationWallets
     */
    public function test_測試透過使用者_elquent_建立部分遊戲站錢包資料兩次_第二次僅建立未建立的錢包()
    {
        // Arrange
        $user = new User();
        $user->id = (string) Ulid::generate();
        $user->name = $this->faker->name;
        $user->save();
        // Wallets
        $stationsAtFirst = [
            'bingo',
            'sa_gaming',
        ];
        $stationsAtSecond = [
            'bingo',
            'sa_gaming',
            'all_bet',
            'holdem',
        ];

        // Act
        // 建立錢包
        $user = User::where('id', '=', $user->id)->first();
        $walletsAtFirst = $user->buildStationWallets($stationsAtFirst);
        $walletsAtSecond = $user->buildStationWallets($stationsAtSecond);

        // Assert
        // user
        $walletsAtFirst = $walletsAtFirst->toArray();
        $this->assertTrue(array_key_exists('bingo', $walletsAtFirst));
        $this->assertTrue(array_key_exists('sa_gaming', $walletsAtFirst));
        $walletsAtSecond = $walletsAtSecond->toArray();
        $this->assertTrue(array_key_exists('all_bet', $walletsAtSecond));
        $this->assertTrue(array_key_exists('holdem', $walletsAtSecond));
    }

    /**
     * 測試透過使用者_elquent_建立所有遊戲站錢包資料，並預設部分錢包凍結停用
     *
     * 測試對象：buildStationWallets
     */
    public function test_測試透過使用者_elquent_建立_所有_遊戲站錢包資料_並預設部分錢包凍結停用()
    {
        // Arrange
        // Users
        $users = [];
        $userAmount = 5;
        for ($i = 0; $i < $userAmount; $i++) {
            $user = new User();
            $user->id = (string) Ulid::generate();
            $user->name = $this->faker->name;
            $user->save();
            array_push($users, $user);
        }
        // 預設狀態
        $stations = [
            'all_bet',
            'bingo',
            'holdem',
            'sa_gaming',
        ];
        $status = [
            'all_bet' => 'freezing',
            'bingo' => 'freezing',
            'holdem' => 'freezing',
            'sa_gaming' => 'active',
        ];

        // Act
        // 建立錢包
        $wallets = [];
        foreach ($users as $user) {
            array_push($wallets, $user->buildStationWallets($stations, $status));
        }
        $user1 = $users[0];
        $user2 = $users[1];
        $user3 = $users[2];
        $user4 = $users[3];
        $user5 = $users[4];

        // Assert
        $this->assertEquals($userAmount, count($wallets));
        // 五位使用者
        // user 1
        $userWallets = collect($user1->wallets())->keyBy('station')->toArray();
        $this->assertEquals('freezing', $userWallets['all_bet']['status']);
        $this->assertEquals('freezing', $userWallets['bingo']['status']);
        $this->assertEquals('freezing', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 2
        $userWallets = collect($user2->wallets())->keyBy('station')->toArray();
        $this->assertEquals('freezing', $userWallets['all_bet']['status']);
        $this->assertEquals('freezing', $userWallets['bingo']['status']);
        $this->assertEquals('freezing', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 3
        $userWallets = collect($user3->wallets())->keyBy('station')->toArray();
        $this->assertEquals('freezing', $userWallets['all_bet']['status']);
        $this->assertEquals('freezing', $userWallets['bingo']['status']);
        $this->assertEquals('freezing', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 4
        $userWallets = collect($user4->wallets())->keyBy('station')->toArray();
        $this->assertEquals('freezing', $userWallets['all_bet']['status']);
        $this->assertEquals('freezing', $userWallets['bingo']['status']);
        $this->assertEquals('freezing', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
        // user 5
        $userWallets = collect($user5->wallets())->keyBy('station')->toArray();
        $this->assertEquals('freezing', $userWallets['all_bet']['status']);
        $this->assertEquals('freezing', $userWallets['bingo']['status']);
        $this->assertEquals('freezing', $userWallets['holdem']['status']);
        $this->assertEquals('active', $userWallets['sa_gaming']['status']);
    }

    /**
     * 測試透過使用者_elquent_建立所有錢包資料
     *
     * 測試對象：buildWallets
     */
    public function test_測試透過使用者_elquent_建立所有錢包資料()
    {
        // Arrange
        $user = new User();
        $user->id = (string) Ulid::generate();
        $user->name = $this->faker->name;
        $user->save();

        // Act
        $user->buildWallets();
        $wallets = $user->wallets()->keyBy('station')->toArray();

        // Assert
        $this->assertTrue(array_key_exists('bingo', $wallets));
        $this->assertTrue(array_key_exists('sa_gaming', $wallets));
        $this->assertTrue(array_key_exists('holdem', $wallets));
        $this->assertTrue(array_key_exists('all_bet', $wallets));
        $this->assertTrue(array_key_exists(config('station_wallet.master_wallet_name'), $wallets));
    }

    /**
     * 測試透過使用者_elquent_啟用所有錢包
     *
     * 測試對象：activeWallets
     */
    public function test_測試透過使用者_elquent_啟用所有錢包()
    {
        // Arrange
        $user = new User();
        $user->id = (string) Ulid::generate();
        $user->name = $this->faker->name;
        $user->save();
        $status = [
            'all_bet' => 'freezing',
            'bingo' => 'freezing',
            'holdem' => 'freezing',
            'sa_gaming' => 'freezing',
        ];

        // Act
        $beforeActiveWallets = $user->buildStationWallets(config('station_wallet.stations'), $status);
        $afterActiveWallets = $user->activeWallets();

        // Assert
        $this->assertEquals($beforeActiveWallets->count(), $afterActiveWallets->count());
        $this->assertEquals('freezing', ($beforeActiveWallets->get('all_bet'))['status']);
        $this->assertEquals('freezing', ($beforeActiveWallets->get('bingo'))['status']);
        $this->assertEquals('freezing', ($beforeActiveWallets->get('holdem'))['status']);
        $this->assertEquals('freezing', ($beforeActiveWallets->get('sa_gaming'))['status']);
        $this->assertEquals('active', ($afterActiveWallets->get('all_bet'))['status']);
        $this->assertEquals('active', ($afterActiveWallets->get('bingo'))['status']);
        $this->assertEquals('active', ($afterActiveWallets->get('holdem'))['status']);
        $this->assertEquals('active', ($afterActiveWallets->get('sa_gaming'))['status']);
    }

    /**
     * 測試透過使用者_elquent_停用所有錢包
     *
     * 測試對象：freezeWallets
     */
    public function test_測試透過使用者_elquent_停用所有錢包()
    {
        // Arrange
        $user = new User();
        $user->id = (string) Ulid::generate();
        $user->name = $this->faker->name;
        $user->save();
        $status = [
            'all_bet' => 'active',
            'bingo' => 'active',
            'holdem' => 'active',
            'sa_gaming' => 'active',
        ];

        // Act
        $beforeFreezingWallets = $user->buildStationWallets(config('station_wallet.stations'), $status);
        $afterFreezingWallets = $user->freezeWallets();

        // Assert
        $this->assertEquals($beforeFreezingWallets->count(), $afterFreezingWallets->count());
        $this->assertEquals('active', ($beforeFreezingWallets->get('all_bet'))['status']);
        $this->assertEquals('active', ($beforeFreezingWallets->get('bingo'))['status']);
        $this->assertEquals('active', ($beforeFreezingWallets->get('holdem'))['status']);
        $this->assertEquals('active', ($beforeFreezingWallets->get('sa_gaming'))['status']);
        $this->assertEquals('freezing', ($afterFreezingWallets->get('all_bet'))['status']);
        $this->assertEquals('freezing', ($afterFreezingWallets->get('bingo'))['status']);
        $this->assertEquals('freezing', ($afterFreezingWallets->get('holdem'))['status']);
        $this->assertEquals('freezing', ($afterFreezingWallets->get('sa_gaming'))['status']);
    }
}