<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

 $app->withFacades();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);
$app->middleware([
    'cors' => App\Http\Middleware\Cors::class,//跨域中间件
]);

$app->routeMiddleware([
 //'auth' => App\Http\Middleware\Authenticate::class,
    'authToken' => App\Http\Middleware\AuthToken::class,
    'authTokenForSaas' => App\Http\Middleware\AuthTokenForSaas::class,
]);


/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

//---注册RedisServiceProvider
$app->register(Illuminate\Redis\RedisServiceProvider::class);
//--注册MongoDb
$app->register(Jenssegers\Mongodb\MongodbServiceProvider::class);
//新增Mongo的匿名映射#Mongo
//$app->alias(['Mongo'=>'Jenssegers\Mongodb\MongodbServiceProvider::class']);

//新增CommandsServiceProvider注册
$app->register(Wn\Generators\CommandsServiceProvider::class);

//新增maatwebsite/excel~3.1注册
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);




$app->withFacades();
//$app->withEloquent();

$app->withEloquent();
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers\Api',
], function ($router) {
    require __DIR__ . '/../routes/api.php';
});

$app->router->group([
    'namespace' => 'App\Http\Controllers\Saas',
], function ($router) {
    require __DIR__ . '/../routes/saas.php';
});

//加载配置文件
$app->configure('cnf_testdata');
$app->configure('cnf_companys');

return $app;
