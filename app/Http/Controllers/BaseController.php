<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-07-01
 * @version 1.0
 */

namespace App\Http\Controllers;

use App\ConstDir\BaseConst;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class BaseController extends Controller
{

    /**
     * 登录用户钉钉信息
     * @var UserDDInfoService
     */
    public $userDDInfo;
    protected $microRangeStart;
    public $page = '';
    public $pageNum = '';

    //
    public function __construct(Request $request)
    {
        // 子类依赖注入方法di
        if (method_exists($this, 'di')) {
            app()->call([$this, 'di']);
        }

        // 获取用户信息
        //$this->userDDInfo = app()->make(UserDDInfoService::class);

        // 分页参数
        $this->page = $request->input('page',1);
        $this->pageNum = $request->input('pageNum',BaseConst::PAGE_NUM);

        //接口统计
        $this->microRangeStart = _nowMicroTime();
    }

    /**
     * 记录接口请求时间过长的API
     * @param $requestUrl
     * @param $microRange
     */
    protected function _recordLongApiForApiStatistics($requestUrl, $microRange){
        $apiUrl = $requestUrl;
        //次数记录
        //$rds = Redis::connection();
        $diffMicroTime = intval($microRange['end'] - $microRange['start']);
        if($diffMicroTime>500){
           $this->writeLog('api_statistics', '天眼报表接口:'.$apiUrl.',请求耗时（毫秒）:'.$diffMicroTime.'。', 'api_statistics');
        }
    }


    /**
     * @param $channel
     * @param string $message
     * @param string $moduleType
     * @param string $logLevel
     * @return bool
     * @throws \Exception
     */
    protected function writeLog($channel, $message = '', $moduleType = 'business', $logLevel = 'info')
    {
        $output = "[%datetime%] %channel%.%level_name%: %message%\n";
        $Logger = new Logger($channel);
        $formatter = new LineFormatter($output, null, true);
        $Logger->pushHandler(
            (new StreamHandler(storage_path() . '/logs/' . $moduleType . '-' . date('Y-m-d') . '.log', Logger::INFO))->setFormatter($formatter)
        );
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message;
        return $Logger->log($logLevel, $message);
    }

    /**
     * @return array
     */
    protected function _getThisWeekRange(){
        $thisCell = [
            //本周一
            'monday' => date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
            //本周日
            'sunday' => date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600)),
        ];
        return $thisCell;
    }

    /**
     * @return array
     */
    protected function getHeadersSpec(){
        $headersSpec = [
            'Access-Control-Allow-Origin'=>'*',
            'Access-Control-Allow-Headers' => 'Origin,No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With, token',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ];
        return $headersSpec;
    }


    /**
     * @param $AttachFile excel文件对象
     */
    public function _toolsAttachFileAnalyze($AttachFile){
        $str = '';
        if(!empty($AttachFile)){
            $str = json_encode($AttachFile);
        }
        return $str;
    }
}