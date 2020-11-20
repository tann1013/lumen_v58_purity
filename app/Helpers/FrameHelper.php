<?php
/**
 * 格式化返回
 */
if (!function_exists('wmResponse')) {
    /**
     * Return a new response from the application.
     *
     * @param  string $content
     * @param  int    $status
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|\App\Libraries\WmApiResponse
     */
    function wmResponse($content = '', $status = 200, array $headers = [])
    {
        $factory = new \App\Libraries\WmApiResponse();

        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->make($content, $status, $headers);
    }
}


/**
 * 当前毫秒
 */
if (!function_exists('_nowMicroTime')) {
    function _nowMicroTime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}

if (!function_exists('numberTwoPoint')) {
    /**
     * Return a new response from the application.
     *
     * @param  string $content
     * @param  int    $status
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|\App\Libraries\WmApiResponse
     */
    function numberTwoPoint($amount)
    {
        $number = sprintf("%.2f",$amount);
        return (float) $number;
    }
}

/**
 * 数组arr按照zm分组
 * $arr = array(
 * [ 'zm' => 'AD',  'name' => '奥迪A4L'],
 * [ 'zm' => 'FLL', 'name' => '法拉利511'],
 * [ 'zm' => 'FLL', 'name' => '法拉利911']
 * );
 */
if (!function_exists('_arrayGroupByCellKey')) {
    function _arrayGroupByCellKey($arr, $pickKey)
    {
        $result = array();
        foreach ($arr as $k => $v) {
            $result[$v[$pickKey]][] = $v;
        }
        return $result;
    }
}

/**
 * 2019-10-13 -> 2019-10-13 00:00:00
 * 2019-10-14 -> 2019-10-14 23:59:59
 * @param $startDate
 * @param $endDate
 * @return array
 */
if (!function_exists('_date2dateHis')) {
    function _date2dateHis($startDate, $endDate){
        $startDate = trim($startDate) .' 00:00:00';
        $endDate = trim($endDate) .' 23:59:59';
        return array(
            'StartDate' => $startDate,
            'EndDate'   => $endDate
        );
    }
}

//是否包含某个字符串
if (!function_exists('_strIsHasP')) {
    function _strIsHasP($str, $p){
        if(strpos($str, $p) !== false){
            return true;
        }else{
            return false;
        }
    }
}
