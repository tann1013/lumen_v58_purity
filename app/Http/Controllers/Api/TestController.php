<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-04-22
 * @version 1.0
 */

namespace App\Http\Controllers\Api;


use App\Exports\FlatAccountMultipleExport;
use App\Exports\InvoicesExport;
use App\Exports\UsersExport;
use App\Http\Controllers\BaseController;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends BaseController
{
    public function getExport()
    {
//        $intId = '29';//加引号可以解决excel显示科学计数法问题
//        //$intId = (string) $intId;
//
//        $cellData = array(
//            array('姓名','昵称','年龄','学习过的课程'),
//            array('张三','三', "$intId", '语文'),
//            array('','', '', '数学'),
//            array('','', '', '英语'),
//        );
//        //return Excel::download(new UsersExport(), 'users.xlsx');
//        //return Excel::download($cellData, 'users.xlsx');
//
//        $export = new InvoicesExport($cellData);
//        $dataStr = date('Ymdis', time());
//        $bool = Excel::download($export, '资金报表'.$dataStr.'.xlsx');
//        return $bool;


//        $cellData = array(
//            0,1,2,3
//        );
//        $export = new FlatAccountMultipleExport($cellData);
//        $dataStr = date('Ymdis', time());
//        $bool = Excel::download($export, '资金报表'.$dataStr.'.xlsx');


        $cellData = [
            ['学号','姓名','成绩'],
            ['10001','AAAAA','99'],
            ['10002','BBBBB','92'],
            ['10003','CCCCC','95'],
            ['10004','DDDDD','89'],
            ['10005','EEEEE','96'],
        ];
        Excel::create('学生成绩',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

}