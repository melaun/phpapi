<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



require '../vendor/autoload.php';
//require './classes/Order.php';

$config = [
    'settings' => [
        'displayErrorDetails' => true,

        'logger' => [
            'name' => 'slim-app',
//            'level' => Monolog\Logger::DEBUG,
//            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];


$app = new \Slim\App($config);

$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Vítej v API");

    return $response;
});
$app->get('/testik', function (Request $request, Response $response,$args){
   $response->getBody()->write('args:');
   return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
     $this->logger->addInfo("Something interesting happened");
    $response->getBody()->write("Hello, $name");

    return $response;
});
$app->post('/test', function (Request $request, Response $response) {
//     $this->logger->addInfo("Something interesting happened");
    $response->getBody()->write("Hello, BITCH");

    return $response;
});

$app->post('/order', OrderController::class.":makeOrder");
$app->run();