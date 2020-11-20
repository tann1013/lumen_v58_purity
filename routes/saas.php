<?php
/**
 * API
 */
$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->group(['middleware' => ['authTokenForSaas']], function () use ($router) {
    $router->post('/saas/finance/common/getCustomerList', 'SaasCommonController@getCustomerList');//客户列表
});

//测试
$router->get('/saas/finance/report/getBillListExport', 'SaasFinanceBillReportController@getBillListExport');