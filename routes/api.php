<?php
/**
 * API
 */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/info', function () use ($router) {
    return phpinfo();
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
