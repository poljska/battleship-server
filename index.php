<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(TRUE, TRUE, TRUE);

require 'app/routes/general.php';
require 'app/routes/game.php';
require 'app/routes/cheat.php';

$app->run();
