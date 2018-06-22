# 資料庫時間間隔查詢器

##使用情境
此 package 之使用情境為，假設今有一 Table movies：

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
    
    假設今天日期是 2018-04-16，那麼我們會發現，在資料庫中
    id=1 的與神同行的 "status" 應該被更新為"已下檔"
    id=3 的明天過後的 "status" 應該被更新為"上映中"
    
    這個 package 的功能就是為你找出那些 "應該被更新的 row id"
    你可以依據不同的輸入來做限制，類似的使用有文章定時發佈與隱藏等...
    再配合 Laravel 的排程使用，便可以使程式依自已所需要的頻率，"定期的自動更新維護 table 資訊"

## 安裝
因為此套件是公開版控庫，但是因為沒有上傳至 packgist，如果要安裝此 package 的專案，必需在自己的 composer.json 先定義版控庫來源

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

    composer require Ariby/Time-Interval-Select
    
並在config/app.php加上Provider

    'providers' => [
        ...
        Ariby\TimeIntervalSelect\TimeIntervalServiceProvider::class,
    ],    
    
然後在要使用的地方上方，加上下方程式碼做 include

	use Ariby\TimeIntervalSelect\TimeIntervalSelect;

## 使用方法

目前的 Method 一共有 3 個，以查詢的時間來做區分<br>
分別是 checkBefore、checkBetween、checkAfter 且都必須傳入四個參數，依序分別是：<br>

	$tableName => 欲查詢的table名稱，以上例就是"movies"

	$tag => Array，欲做比對的欄位與其值，以movis為例 => ['status' => '上映中']
	['status' => ['上映中', '已下檔']]` // 相當於 `status != "上映中" && status != "已下檔"

	$columnName => Array，存入table中的column欄位名稱，實際格式如下
	['primaryKey' => "table中欲回傳的欄位名稱，如id",
	'start_at'   => 'table中記錄判斷起始時間的欄位名稱',
	'end_at'     => 'table中記錄判斷結束時間的欄位名稱'
	]`
其中：<br>
**checkBefore 可不傳入 end_at**<br>
**checkAfter 可不傳入 start_at**<br>
**checkBetween 則start_at與end_at皆必須傳入**

$function => **為一閉包函式，應接收一陣列參數，會包含查詢結果，可執行使用者想做之事，比如更新欄位**

以下為以 movies 為使用案例的程式

#### checkBefore

    TimeIntervalSelect::checkBefore("movies",
    ['status' => '未上映'], 
    array('primaryKey'=>'id', 'start_at'=>'startTime'),
    function($array){ ...do something what you want to do });

在 function 結束時，程式會自動將查詢結果帶入閉包函式執行，回傳結果如下：

    array(); // 以上表 movies 為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將 status 欄位修改為「未上映」，以 Laravel ORM 為例

    Movies::whereIn('id', $array)->update['status'=>'未上映'];

#### checkBetween

    $result = TimeIntervalSelect::checkBetween("movies",
    ['status' => '上映中'], 
    array('primaryKey'=>'id', 'start_at'=>'startTime', 'end_at' => 'endTime'),
    function($array){ ...do something what you want to do });

在 function 結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(3); // 以上表 movies 為例，id=3 之資料應該被修改

使用者應在閉包函式做欲做之事，如將 status 欄位修改為「上映中」，以 Laravel ORM 為例

    Movies::whereIn('id', $array)->update['status'=>'上映中'];
    
#### checkAfter

    $result = TimeIntervalSelect::checkAfter("movies",
    ['status' => '已下檔'], 
    array('primaryKey'=>'id', 'end_at' => 'endTime'),
    function($array){ ...do something what you want to do });

在 function 結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(1); // 以上表 movies 為例，id=3 之資料應該做修改

使用者應在閉包函式做欲做之事，如將 status 欄位修改為「上映中」，以 Laravel ORM 為例

    Movies::whereIn('id', $array)->update['status'=>'已下檔'];
    
## 回傳格式

function 會自動執行閉包函式，並將查詢結果之 id 陣列以參數傳入<br>
當執行發生錯誤時，呼叫函式實際回傳格式如下：

    array(
        'ok'   => 'false',     // 回傳"true" or "false" 代表是否發生錯誤
        'msg'  => 'error msg', // 錯誤訊息，比如參數錯誤、table不存在..etc
        'data' => array()      // 查詢結果之id陣列
    )

可以下方程式來判斷是否發生錯誤

    if( $returnArray['ok'] != 'true' ){
        // error
    }else{
        // right
    }
    
## 擴充案例

若你的案例可能會有「沒有預定結束時間」的狀況 (即 end_at === null)，且你使用的是 Laravel 專案，可以在 Model 加上以下程式碼：

    public function setPublishEndAtAttribute($publish_end_at){
        if(is_null($publish_end_at)){
            $this->attributes['publish_end_at'] = '9999-12-31 23:59:59';
        }else{
            $this->attributes['publish_end_at'] = $publish_end_at;
        }
    }

    public function getPublishEndAtAttribute($publish_end_at){
        if($publish_end_at == '9999-12-31 23:59:59'){
            return null;
        }else{
            return $publish_end_at;
        }
    }
    
## 使用Artisan命令列做查詢

在config/app.php加上服務提供者後，可直接以php artisan使用

    \\ php artisan可看到
    TimeSelect:getSatisfyIDs    {tableName : The name of table.}
                           {method : 1:before, 2:now, 3:after}
                           {primaryKey : The column you want to return after search.}
                           {beforeTimeKey : The column name in the database.}
                           {afterTimeKey : The column name in the database.}
                           {tagArray* : The rule that you want to set. The first parameter is key, and after is value. e.g. send "status false tag error" means ["staus" => "false", "tag" => "error"]}
    
以下為使用範例：
    
    // 輸入指令
    php artisan TimeSelect:getSatisfyIDs movies 2 id startTime endTime status 未上映
    // 回傳Number為滿足條件的row數量、result為滿足的id們，以" ,"為分隔符
    Number: 1
    result: 3
    
## 配合Laravel排程使用
可以參考 [Laravel 官方文件](https://docs.laravel-dojo.com/laravel/5.5/scheduling)<br>

以下是範例程式<br>
`App/console/kerner.php`

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
    
`App\Console\Commands建立updateMoviesStatus.php`
    
    <?php
    
    namespace App\Console\Commands;
    
    use Illuminate\Console\Command;
    
    use App\Models\Movies;
    use Ariby\TimeIntervalSelect\TimeIntervalSelect;
    
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
            TimeIntervalSelect::checkBetween("movies",['status' => '上映中'], array('primaryKey'=>'id', 'start_at'=>'startTime', 'end_at'=>'endTime'), function($array){
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
    
接著執行`php artisan schedule:run`，便可使排程每分鐘檢查資料庫中電影狀態並更新<br>
<br>
**注意：若你是 windows ，執行 `php artisan schedule:run` 可能會遇到 ( windows 跑 Laravel 排程的問題)**

    artisan" update:movies > "NUL" 2>&1
    
可參考 https://github.com/laravel/framework/issues/7868 或其他方式解決