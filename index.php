<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$debug = getenv('DEBUG') === '1' ? TRUE : FALSE;
$app->addErrorMiddleware($debug, $debug, $debug);

require 'app/routes/general.php';
require 'app/routes/game.php';
require 'app/routes/cheat.php';

$app->run();
