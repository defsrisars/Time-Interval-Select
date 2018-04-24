# 資料庫時間間隔查詢器

##使用情境
此package之使用情境為，假設今有一Table movies：

    /*
    |--------------------------------------------------------------------------
    | movies
    |--------------------------------------------------------------------------
    |
    | 記錄目前所有電影的資訊，欄位如下：
    |
    */
    |--------------------------------------------------------------------------
    | id  |   name   |   status    |   startTime    |    endTime ...
    |--------------------------------------------------------------------------
    |  0  | 哈利波特  |    已下檔    |  2018-02-01    |   2018-02-01 ...
    |--------------------------------------------------------------------------
    |  1  | 與神同行  |    上映中    |  2018-04-01    |   2018-04-15 ...
    |--------------------------------------------------------------------------
    |  2  | 玩命關頭  |    上映中    |  2018-04-10    |   2018-04-30 ...
    |--------------------------------------------------------------------------
    |  3  | 全面啟動  |    未上映    |  2018-04-16    |   2018-05-30 ...
    |--------------------------------------------------------------------------
    |  4  | 明天過後  |    未上映    |  2018-06-01    |   2018-08-01 ...
    |--------------------------------------------------------------------------
    | ... |   ...    |    ...       |       ...     |    ...
    
    假設今天日期是2018-04-16，那麼我們會發現，在資料庫中
    id=1的與神同行的"status"應該被更新為"已下檔"
    id=3的明天過後的"status"應該被更新為"上映中"
    
    這個package的功能就是為你找出那些應該被更新的row id
    你可以依據不同的輸入來做限制
    類似的使用有文章定時發佈與隱藏等...
    
    再配合Laravel的排程使用，即可使程式依自已所需要的頻率自動定期更新維護table資訊

## 安裝
因為此套件是私有版控庫，如果要安裝此 package 的專案，必需在自己的 composer.json 先定義版控庫來源

    // composer.json
    
    ...(略)...
    "repositories": [
      {
        "type": "git",
        "url": "https://github.com/defsrisars/Time-Interval-Select.git"
      }
    ],
    ...(略)...

接著就可以透過下列指令進行安裝

    composer require ariby/Time-Interval-Select
    
然後在要使用的地方最上方加上

    use ariby\TimeIntervalSelect\TimeIntervalSelect;
    
來做include

## 使用方法

目前的Method一共有3個，以查詢的時間來做區分<br>
分別是checkBefore、checkNow、checkAfter<br>
且都必須傳入四個參數，依序分別是：<br>

$tableName => 欲查詢的table名稱，以上例就是"movies"<br>

$tag => Array，欲做比對的欄位與其值，以movis為例如下<br>
['status' => '上映中']<br>
則會加入status != "上映中"的條件，若呼叫checkBefore便可找出時間應該在"上映中"，
其值卻不為"上映中"的row之primaryKey<br>
且參數值可為Array，便可對單一鍵值做複合查詢，如：<br>
['status' => ['上映中', '已下檔']]，相當於 status != "上映中" && status != "已下檔"

$columnName => Array，存入table中的column欄位名稱，實際格式如下<br>
['primaryKey' => "table中欲回傳的欄位名稱，如id",<br>
 'start_at'   => 'table中記錄判斷起始時間的欄位名稱',<br>
 'end_at'     => 'table中記錄判斷結束時間的欄位名稱'
]<br>
其中checkBefore可不傳入end_at、checkAfter可不傳入start_at<br>
checkNow則兩者皆必須傳入

$function => 為一閉包函式，應接收一陣列參數，會包含查詢結果，可執行使用者想做之事

以下為以movies為使用案例的程式

#### checkBefore
=
=SELECT $primaryKey FROM $tableName WHERE start_at > now() AND $tag[$key] != $tag[$key]->value...

    $result = TimeIntervalSelect::checkBefore("movies",
    ['status' => '未上映'], 
    array('primaryKey'=>'id', 'start_at'=>'startTime'),
    function($array){ ...do something });

在function結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(); // 以上表movies為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將status欄位修改為「未上映」，以Laravel為例

    Movies::whereIn('id', $array)->update['status'=>'未上映'];

即可將符合時間條件與tag條件之資料做修改

#### checkNow
=
=SELECT $primaryKey FROM $tableName WHERE start_at < now() AND end_at > NOW() AND $tag[$key] != $tag[$key]->value...

    $result = TimeIntervalSelect::checkNow("movies",
    ['status' => '上映中'], 
    array('primaryKey'=>'id', 'start_at'=>'startTime', 'end_at' => 'endTime'),
    function($array){ ...do something });

在function結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(3); // 以上表movies為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將status欄位修改為「上映中」，以Laravel為例

    Movies::whereIn('id', $array)->update['status'=>'上映中'];

即可將符合時間條件與tag條件之資料做修改

    
#### checkAfter
=
=SELECT $primaryKey FROM $tableName WHERE end_at < NOW() AND $tag[$key] != $tag[$key]->value...

    $result = TimeIntervalSelect::checkAfter("movies",
    ['status' => '已下檔'], 
    array('primaryKey'=>'id', 'end_at' => 'endTime'),
    function($array){ ...do something });

在function結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(1); // 以上表movies為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將status欄位修改為「上映中」，以Laravel為例

    Movies::whereIn('id', $array)->update['status'=>'已下檔'];

即可將符合時間條件與tag條件之資料做修改
    
## 回傳格式

function會自動執行閉包函式，並將查詢結果之id陣列以參數傳入<br>
當執行發生錯誤時，回傳格式如下：

    array(
        'ok'   => 'false',     // 回傳"true" or "false" 代表是否發生錯誤
        'msg'  => 'error msg', // 錯誤訊息，比如參數錯誤 
        'data' => array()      // 查詢結果之id陣列
    )

可以以

    if( $returnArray['ok'] != 'true' ){
        // error
    }else{
        // right
    }
    
來判斷是否發生錯誤。
    
## 配合Laravel排程使用
=
首先可以參考官方文件
https://docs.laravel-dojo.com/laravel/5.5/scheduling<br>

以下是範例程式<br>
在App/console/kerner.php

    // console/Kernel.php
        
        ...
        protected $commands = [
            \App\Console\Commands\UpdateMoviesStatus::class,
        ];
        ...
        protected function schedule(Schedule $schedule)
        {
            $schedule->command('update:movies')->everyMinute();
        }
        ...
    
    // console/commands/updateMoviesStatus.php
    
在App\Console\Commands建立updateMoviesStatus.php
    
    <?php
    
    namespace App\Console\Commands;
    
    use Illuminate\Console\Command;
    
    use App\Model\Movies;
    use ariby\TimeIntervalSelect\TimeIntervalSelect;
    
    class UpdateMoviesStatus extends Command
    {
        // 命令名稱
        protected $signature = 'update:movies';
    
        // 說明文字
        protected $description = '[update] Movies status';
    
        public function __construct()
        {
            parent::__construct();
        }
    
        // Console 執行的程式
        public function handle()
        {
            /* before-檢查未上映的電影並更新 */
            TimeIntervalSelect::checkBefore("movies",['status' => '未上映'], array('primaryKey'=>'id', 'start_at'=>'startTime'), function($array){
                if(!is_null($array))
                    Movies::whereIn('id', $array)->update(['status' => '未上映', 'stage' => 'Before']);
            });
    
            /* now-檢查上映中的電影並更新 */
            TimeIntervalSelect::checkNow("movies",['status' => '上映中'], array('primaryKey'=>'id', 'start_at'=>'startTime', 'end_at'=>'endTime'), function($array){
                if(!is_null($array))
                    Movies::whereIn('id', $array)->update(['status' => '上映中', 'stage' => 'Now']);
            });
    
            /* after-檢查已下檔的電影並更新 */
            TimeIntervalSelect::checkAfter("movies",['status' => '已下檔'], array('primaryKey'=>'id', 'end_at'=>'endTime'), function($array){
                if(!is_null($array))
                    Movies::whereIn('id', $array)->update(['status' => '已下檔', 'stage' => 'After']);
            });
        }
    }
    
接著執行php artisan schedule:run，便可使排程每分鐘檢查資料庫中電影狀態並更新<br>
<br>
注意：若你是windows，執行php artisan schedule:run可能會遇到

    artisan" update:movies > "NUL" 2>&1
    
回傳(windows跑Laravel排程的問題)<br>
可參考 https://github.com/laravel/framework/issues/7868 或其他方式解決