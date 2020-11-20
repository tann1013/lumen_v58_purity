<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-10-10
 * @version 1.0
 */

namespace App\Logics;


use AlibabaCloud\Aegis\V20161111\DeleteDingTalk;
use AlibabaCloud\Rds\V20140815\DeleteDatabase;
use App\ConstDir\FinanceConst;
use App\Logics\Wls\TaskLogic;
use App\Models\FinanceBillModel;
use App\Models\LogisticscompanyModel;
use App\Models\VerifycodeModel;
use App\Service\Redis\UseRedisService;
use App\Service\Status\AccountSourceType;
use App\Service\Status\IsOwnCar;
use App\Service\Status\OrderType;
use App\Service\Status\PassPointAddressType;
use App\Service\Status\PickState;
use App\Service\Status\PlatformType;
use App\Service\Status\TailTakeStatus;
use App\Service\Status\TakeStatus;
use App\Service\Status\YdCheckedStatus;
use App\Service\Status\YdOrderStatus;
use App\Service\UserTagCode;

use foo\bar;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class BaseLogic
{
    /**
     * @param int $levels > 0
     */
    public function _getRecentMonthDateRange($levels = 3){
        $now = time();
        $levels = ( $levels>0) ?  $levels : 1;
        $factlevels = (int) ($levels - 1);
        $time = strtotime('-'.$factlevels.' month', $now);
        $beginTime = date('Y-m-d 00:00:00', mktime(0, 0,0, date('m', $time), 1, date('Y', $time)));
        $endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, date('m', $now), date('t', $now), date('Y', $now)));
        $DateRange =  array(
            'startDate' => $beginTime, 'endDate' => $endTime,
        );
        return $DateRange;
    }

    /**
     * 近一个月 -30*1
     * 近三个月 -30*3
     * 近六个月 -30*6
     * @param int $levels
     * @return array
     */
    public function _getRecentMonthDateRangeV2($levels = 1){
        $days = intval($levels * 30);
        $days = - $days;
        $beginTime = date('Y-m-d 00:00:00', strtotime("$days days"));
        $endTime = date('Y-m-d 23:39:59', time());
        $DateRange =  array(
            'startDate' => $beginTime, 'endDate' => $endTime,
        );
        return $DateRange;
    }

    /**
     * 使用说明：
     *  $this->writeLog('dispatchTask', 'sssss', 'dispatchTask/business');
     *  会在storage下创建目录dispatchTask
     *  storage/logs/dispatchTask/business-2019-10-17.log
    use Monolog\Formatter\LineFormatter;
    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;
     *
     * @param $channel
     * @param string $message
     * @param string $moduleType
     * @param string $logLevel
     * @return bool
     * @throws \Exception
     */
    protected function writeLogV2($channel, $message = '', $moduleType = 'business', $logLevel = 'info')
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
     * 通用过滤 by tj @2020-4-29
     * SAAS端会过滤登录用户
     *
     * @param $inputs
     * @param $whereSql
     * @param $users
     * @param $platform
     */
    protected function _toolsFilterListByUsersForUpBillList($inputs, &$whereSql, $users, $platform){
        if($platform==FinanceConst::PLATFORM_SAAS){
            //saas端只显示登录用户下上游发票
            $whereSql['GetFundSourceId'] = $users['Id'];
            //saas端已选中上游发票不显示
            if(!empty($inputs['TakeStatus'])){
            }else{
                //为空，则显示未被选中的
                //$whereSql['IsPicked'] = PickState::NOT_PICK;
            }
        }elseif($platform==FinanceConst::PLATFORM_BOS){
            //建祥查看全部，其他子公司则只显示开票方（登录子公司）数据
            $CurrCId = isset($users['CurrCId']) ? $users['CurrCId'] : '';
            if(!empty($CurrCId) && $CurrCId!=FinanceConst::HZJX){
                $whereSql['TakeCId'] = $CurrCId;
            }
        }
    }

    /**
     * @param $inputs
     * @param $whereSql
     * @param $users
     * @param $platform
     */
    protected function _toolsFilterListByUsersForTailBillList($inputs, &$whereSql, $users, $platform){

        if($platform==FinanceConst::PLATFORM_SAAS){
            //saas端只显示登录用户下终端发票
            $whereSql['TakeFundSourceId'] = $users['Id'];


        }elseif($platform==FinanceConst::PLATFORM_BOS){

            //查询当前登录子公司下的全部上游发票ID
            $CurrCId = $users['CurrCId'];
            if(!empty($CurrCId) && $CurrCId!=FinanceConst::HZJX){
                $model = new FinanceBillModel();
                //查询关联当前登录用户的终端发票号码
                $BillTailNumberList = $model->getLinkBillTailNumberListByLoginUserTakeCId($CurrCId);
                if(empty($BillTailNumberList)){
                    $whereSql['BillNumber'] = 'wwwww2222sss';
                }else{
                    $whereSql['BillNumber'] = $BillTailNumberList;
                }

            }
        }
    }

    /**
     * @param $inputs
     * @param $whereSqlArrForUpBill
     * @param $users
     * @param $platform
     */
    protected function _toolsFilterListByUsersForBillReportList($inputs, &$whereSqlArrForUpBill, &$whereSql, $users, $platform){

        if($platform==FinanceConst::PLATFORM_SAAS){
            //SAAS
            //saas只展示开票方为当前登录用户（金融机构）的终端发票
            if(isset($users['Id']) && $users['Id']>0){
                $whereSql['TakeFundSourceId'] = $users['Id'];
            }
        }elseif($platform==FinanceConst::PLATFORM_BOS){
            //BOS

            //建祥查看全部，其他子公司则只显示开票方（登录子公司）数据
            $CurrCId = isset($users['CurrCId']) ? $users['CurrCId'] : '';
            if(!empty($CurrCId) && $CurrCId!=FinanceConst::HZJX){
                $whereSqlArrForUpBill['TakeCId'] = $CurrCId;
            }

            //new 报表过滤终端发票（当前登录用户子公司关联过的上游发票）
            //查询当前登录子公司下的全部上游发票ID
            if(!empty($CurrCId) && $CurrCId!=FinanceConst::HZJX){
                $model = new FinanceBillModel();
                //1、查询关联当前登录用户的终端发票号码
                $BillTailNumberListForLogin = $model->getLinkBillTailNumberListByLoginUserTakeCId($CurrCId);

                //2、取并集
                if(isset($whereSql['BillNumber'])){
                    $BillTailNumberList = array_merge($BillTailNumberListForLogin, array($whereSql['BillNumber']));
                }else{
                    $BillTailNumberList = $BillTailNumberListForLogin;
                }

                //3
                if(!empty($BillTailNumberList)){
                    $whereSql['BillNumber'] = $BillTailNumberList;
                }
            }

        }

    }

    protected function _toolsFilterListByUsersForLoan($inputs, &$whereSql, $users, $platform){
        if($platform==FinanceConst::PLATFORM_SAAS){
            //
            $whereSql['ExtFundSourceId'] = $users['Id'];

        }elseif($platform==FinanceConst::PLATFORM_BOS){
            //
            //建祥查看全部，其他子公司则只显示开票方（登录子公司）数据
            $CurrCId = isset($users['CurrCId']) ? $users['CurrCId'] : '';
            if(!empty($CurrCId) && $CurrCId!=FinanceConst::HZJX){
                $whereSql['ExtCId'] = $CurrCId;
            }
        }
    }

    protected function _toolsFilterListByUsersForLoanRepay($inputs, &$whereSql, $users, $platform){
    }


    //附件详情特殊处理 by tj
    public function _toolsGetAttachFileList($AttachFile){
        $AttachFileList = array();
        if(!empty($AttachFile)){
            //1 解析维数组
            $AttachFileList = json_decode($AttachFile, TRUE);
        }
        return $AttachFileList;
    }



}