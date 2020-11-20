<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-04-16
 * @version 1.0
 */

namespace App\Models;


use App\Service\Status\LoanStatus;
use App\Service\Status\State;
use App\Service\Status\TakeStatus;

class FinanceBillModel extends BaseModel {

    protected $table = 'tf_finance_bill';

    protected $primaryKey = 'Id';

    //自动维护更新时间字段
    public $timestamps = true;
    const CREATED_AT = 'CreatedTime';
    const UPDATED_AT = 'UpdatedTime';
    /**
     * 模型的日期字段的存储格式
     *
     * @var string
     */
    protected $dateFormat = 'U';

    const BASE_COLUMN_STR = 'Id,
    BillNumber,
    TakeCId,
    TakeCName,
    GetFundSourceId,
    GetFundSourceName,
    TakeDate,
    TakeTon,
    TakeAccountMoney,
    AttachFile,
    Remark,
    IsPicked,
    TakeType,
    TakeStatus';

    const BASE_COLUMN_STR_SIMPLE = 'Id,
    BillNumber';


    public function getListWithPickBillIdList($PickBillIdList){
        $list = $this->from('tf_finance_bill AS bill')
            ->selectRaw(self::BASE_COLUMN_STR_SIMPLE)
            ->whereIn('bill.Id', $PickBillIdList)
            ->get();
        return $list;
    }

    /**
     * @param $PickBillIdList
     * @param $whereSqlArrForUpBill
     * @return mixed
     */
    public function getListWithUpBillIdList($PickBillIdList, $whereSqlArrForUpBill){
        $list = $this->from('tf_finance_bill AS bill')
            ->selectRaw(self::BASE_COLUMN_STR)
            ->where($whereSqlArrForUpBill)
            ->whereIn('bill.Id', $PickBillIdList)
            ->get()
            ->toArray();
        return $list;
    }

    /**
     * @param $PickBillIdList
     * @param $whereSqlArrForUpBill
     * @return mixed
     */
    public function getListWithPickBillIdListForTailSub($PickBillIdList, $whereSqlArrForUpBill){
        $list = $this->from('tf_finance_bill AS bill')
            ->selectRaw(self::BASE_COLUMN_STR)
            ->where($whereSqlArrForUpBill)
            ->whereIn('bill.Id', $PickBillIdList)
            ->get();

        //格式化
        if($list){
            foreach ($list as &$item){
                $item->TakeDateStr = date('Y-m-d', $item->TakeDate);
                $item->TakeStatusCn = TakeStatus::MAPPS[$item->TakeStatus];
            }
        }
        return $list;
    }

    public function deleteById($Id){
        $ToUpdate = array(
            'State' => State::DISABLE,
            'DeletedTime' => time()
        );
        return $this->updateData($ToUpdate, ['Id' => $Id]);
    }

    /**
     * @param $Id
     * @return int
     */
    public function deleteByIdByHardDelete($Id){
        return  $this->deleteData(['Id' => $Id]);
    }

    public function auditById($Id){
        $ToUpdate = array(
            'TakeStatus' => TakeStatus::TO_PUBLISH,
            'UpdatedTime' => time()
        );
        return $this->updateData($ToUpdate, ['Id' => $Id]);
    }

    public function modifyById($Params, $Id){
        unset($Params['Id']);
        $ToUpdate = array(
            'TakeCId' => $Params['TakeCId'],
            'TakeCName' => $Params['TakeCName'],
            'GetFundSourceId' => $Params['GetFundSourceId'],
            'GetFundSourceName' => $Params['GetFundSourceName'],
            'BillNumber' => $Params['BillNumber'],
            'TakeDate' => $Params['TakeDate'],
            'TakeTon' => $Params['TakeTon'],
            'TakeAccountMoney' => $Params['TakeAccountMoney'],
            'AttachFile' => $Params['AttachFile'],
            'Remark' => $Params['Remark'],
            'UpdatedTime' => time()
        );
        return $this->updateData($ToUpdate, ['Id' => $Id]);
    }


    /**
     * @param $BillNumber
     */
    public function isExistBillNumberForBill($BillNumber){
         return $this->from('tf_finance_bill AS bill')
            ->where('bill.BillNumber', $BillNumber)
            ->count();
    }

    /**
     * @param $BillNumber
     */
    public function isExistBillNumberForBillTail($BillNumber){
        return $this->from('tf_finance_bill_tail AS tail')
            ->where('tail.BillNumber', $BillNumber)
            ->count();
    }

    /**
     * 查询发票号根据UoBillId
     * @param $UpBillIdList
     * @return mixed
     */
    public function getUpBillNumberListByIdList($UpBillIdList){
        $list = $this->selectRaw(self::BASE_COLUMN_STR)
            ->whereIn('Id', $UpBillIdList)
            ->pluck('BillNumber')
            ->values()
            ->toArray();
        return $list;
    }

    /**
     * @param $UpBillIdList
     * @return mixed
     */
    public function getTotalTonWithUpBillIdList($UpBillIdList){
        $val = $this->from('tf_finance_bill')
            ->whereIn('Id', $UpBillIdList)
            ->pluck('TakeTon')
            ->values()
            ->sum();
        return $val;
    }

    /**
     * @param $UpBillIdList
     * @return mixed
     */
    public function getSumTakeAccountMoneyWithUpBillIdList($UpBillIdList){
        $val = $this->from('tf_finance_bill')
            ->whereIn('Id', $UpBillIdList)
            ->pluck('TakeAccountMoney')
            ->values()
            ->sum();
        return $val;
    }


    const BASE_COLUMN_STR_FOR_CODE_LEFT_ORDER_DRIVER = 'link.Id as LinkId,
    link.BillNumber as LinkBillNumber,
    link.BillTailNumber as LinkBillTailNumber,
    bill.TakeCId as TakeCId';
    /**
     * 查询终端所属开票列表
     * @param $BillNumberList
     * @return mixed
     */
    public function getListByTailNumberList($TailNumberList){
        $WhereSqlArr[] = ['link.BillTailNumber', $TailNumberList];

        $list = $this->from('tf_finance_bill_link AS link')
            ->selectRaw(self::BASE_COLUMN_STR_FOR_CODE_LEFT_ORDER_DRIVER)
            ->leftJoin('tf_finance_bill AS bill', 'link.BillNumber', '=', 'bill.BillNumber')
            ->where($WhereSqlArr)
            ->orderBy('link.Id', 'DESC')
            ->get()
            ->toArray();

        return $list;
    }

    /**
     * @param $LoginUserTakeCId
     * @return mixed
     */
    public function getLinkBillTailNumberListByLoginUserTakeCId($LoginUserTakeCId){
        $BillTailNumberList = array();//默认

        $list = $this->from('tf_finance_bill AS bill')
            ->selectRaw(self::BASE_COLUMN_STR_SIMPLE)
            ->where('bill.TakeCId', $LoginUserTakeCId)
            ->get()
            ->toArray();

        if($list){
            $UpBillIdList = array_column($list, 'Id');

            $linkList = $this->from('tf_finance_bill_link AS link')
                ->selectRaw('BillNumber,BillTailNumber,BillId')
                ->whereIn('link.BillId', $UpBillIdList)
                ->get()
                ->toArray();

            if($linkList){
                $BillTailNumberList = array_column($linkList, 'BillTailNumber');
            }
        }
        return $BillTailNumberList;
    }








}