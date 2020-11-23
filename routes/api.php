<?php
/**
 * API
 */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/info', function () use ($router) {
    echo phpinfo();die;
    //var_dump(22);die;
    //$openList = \Illuminate\Support\Facades\DB::connection('oracle')->select("SELECT * FROM HR.JOBS");
    //Call to undefined function Yajra\Pdo\oci_connect() @2020-11-23 确认接口形式给数据，暂时不研究
    //var_dump($openList);

});
$router->group(['middleware' => ['authToken']], function () use ($router) {
    /**
     * BOS
     */
    //1、资金模块
    $router->post('/api/finance/loan/create', 'FinanceLoanController@create');
});

/**
 * 测试接口
 */
$router->get('/api/finance/test/getExport', 'TestController@getExport');
