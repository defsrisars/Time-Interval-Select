<?php

use ariby\TimeIntervalSelect\Models\TestTable;
use ariby\TimeIntervalSelect\TimeIntervalSelect;

class TimeIntervalSelectTest extends BaseTestCase
{

    protected function setUp()
    {
        parent::setUp();

        // 初始化 User Table，測試需要的假 User 寫入此 Table
        $this->initTestTable();

        // 注入此套件會使用到的 database 相關東西
        $this->injectDatabase();
    }

    public function testCheckBeforeSuccess_FakeData()
    {
        /* 製作假資料 */
        factory(TestTable::class,30)->create();

        $result = TimeIntervalSelect::checkBefore("test_table",['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3'], array('primaryKey'=>'id', 'start_at'=>'start_at'), function($array){});

        $this->assertEquals($result['ok'], "true");
    }

    public function testCheckBeforeSuccess_RealData()
    {
        /* 製作真實資料驗證 */

        // should not be change
        $test_data1 = new TestTable;
        $test_data1->id = 1;
        $test_data1->name = "哈利波特—神秘的魔法石";
        $test_data1->status = "-";
        $test_data1->test1 = 't1';
        $test_data1->test2 = 't2';
        $test_data1->test3 = 't1';
        $test_data1->start_at = '2018-04-23 00:00:00';
        $test_data1->end_at = '2018-04-30 00:00:00';
        $test_data1->save();

        // should be change (because of TIME)
        $test_data2 = new TestTable;
        $test_data2->id = 2;
        $test_data2->name = "哈利波特—消失的密室";
        $test_data2->status = "-";
        $test_data2->test1 = '';
        $test_data2->test2 = '';
        $test_data2->test3 = '';
        $test_data2->start_at = '2018-04-20 00:00:00';
        $test_data2->end_at = '2018-04-21 00:00:00';
        $test_data2->save();

        $result = TimeIntervalSelect::checkBefore("test_table",['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3'], array('primaryKey'=>'id', 'start_at'=>'start_at','status'=>'status'), function($array){});

        $this->assertEquals($result['ok'], "true");
    }

    public function testCheckNowSuccess_FakeData()
    {
        /* 製作假資料 */
        factory(TestTable::class,30)->create();

        $result = TimeIntervalSelect::checkNow("test_table",['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3'], array('primaryKey'=>'id', 'start_at'=>'start_at', 'end_at' => 'end_at'), function($array){});

        $this->assertEquals($result['ok'], "true");
    }

    public function testCheckNowSuccess_RealData()
    {
        /* 製作真實資料驗證 */

        // should not be change
        $test_data1 = new TestTable;
        $test_data1->id = 1;
        $test_data1->name = "哈利波特—神秘的魔法石";
        $test_data1->status = "-";
        $test_data1->test1 = '';
        $test_data1->test2 = '';
        $test_data1->test3 = '';
        $test_data1->start_at = '2018-04-23 00:00:00';
        $test_data1->end_at = '2018-04-30 00:00:00';
        $test_data1->save();

        // should be change (because of TIME)
        $test_data2 = new TestTable;
        $test_data2->id = 2;
        $test_data2->name = "哈利波特—消失的密室";
        $test_data2->status = "-";
        $test_data2->test1 = '';
        $test_data2->test2 = '';
        $test_data2->test3 = '';
        $test_data2->start_at = '2018-04-20 00:00:00';
        $test_data2->end_at = '2018-04-21 00:00:00';
        $test_data2->save();

        $result = TimeIntervalSelect::checkNow("test_table",['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3'], array('primaryKey'=>'id', 'start_at'=>'start_at', 'end_at' => 'end_at'), function($array){});

        $this->assertEquals($result['ok'], "true");
        $this->assertArrayHasKey('1' ,array_flip($result['data']));
    }

    public function testCheckAfterSuccess_FakeData()
    {
        /* 製作假資料 */
        factory(TestTable::class,30)->create();

        $result = TimeIntervalSelect::checkAfter("test_table",['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3'], array('primaryKey'=>'id', 'end_at'=>'end_at'), function($array){});

        $this->assertEquals($result['ok'], "true");
    }

    public function testCheckAfterSuccess_RealData()
    {
        /* 製作真實資料驗證 */

        // should not be change
        $test_data1 = new TestTable;
        $test_data1->id = 1;
        $test_data1->name = "哈利波特—神秘的魔法石";
        $test_data1->status = "-";
        $test_data1->test1 = 't1';
        $test_data1->test2 = 't2';
        $test_data1->test3 = 't1';
        $test_data1->start_at = '2018-04-23 00:00:00';
        $test_data1->end_at = '2018-04-30 00:00:00';
        $test_data1->save();

        // should be change (because of TIME)
        $test_data2 = new TestTable;
        $test_data2->id = 2;
        $test_data2->name = "哈利波特—消失的密室";
        $test_data2->status = "-";
        $test_data2->test1 = '';
        $test_data2->test2 = '';
        $test_data2->test3 = '';
        $test_data2->start_at = '2018-04-10 00:00:00';
        $test_data2->end_at = '2018-04-11 00:00:00';
        $test_data2->save();

        $result = TimeIntervalSelect::checkAfter("test_table",['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3'], array('primaryKey'=>'id', 'end_at'=>'end_at','status'=>'status'), function($array){});

        $this->assertEquals($result['ok'], "true");
        $this->assertArrayHasKey('2' ,array_flip($result['data']));
    }
}
