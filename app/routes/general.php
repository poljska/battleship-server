<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api {get} /games Request all games
 * @apiName GetAllGames
 * @apiGroup General
 */
$app->get('/games', function (Request $request, Response $response, array $args) {
    $data = array('endpoint' => 'GetAllGames');
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {post} /games Create a new game
 * @apiName NewGame
 * @apiGroup General
 */
$app->post('/games', function (Request $request, Response $response, array $args) {
    $data = array('endpoint' => 'NewGame');
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {delete} /games Delete all games
 * @apiName DeleteAllGames
 * @apiGroup General
 */
$app->delete('/games', function (Request $request, Response $response, array $args) {
    $data = array('endpoint' => 'DeleteAllGames');
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {delete} /games/:id Delete a single game
 * @apiName DeleteSingleGame
 * @apiGroup General
 */
$app->delete('/games/{id}', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'DeleteSingleGame', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {post} /games/:id/join Join a game
 * @apiName JoinGame
 * @apiGroup General
 */
$app->post('/games/{id}/join', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'JoinGame', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {get} /games/:id Request data of a finished game
 * @apiName GetGame
 * @apiGroup General
 */
$app->get('/games/{id}', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'GetGame', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
