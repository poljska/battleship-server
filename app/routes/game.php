<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api {get} /games/:id/current Request data of a ongoing game
 * @apiName GetCurrentGame
 * @apiGroup Game
 */
$app->get('/games/{id}/current', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'GetCurrentGame', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {patch} /games/:id/set-ships Set ships positions
 * @apiName SetShips
 * @apiGroup Game
 */
$app->patch('/games/{id}/set-ships', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'SetShips', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {patch} /games/:id/fire Fire at opponent
 * @apiName Fire
 * @apiGroup Game
 */
$app->patch('/games/{id}/fire', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'Fire', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/**
 * @api {get} /games/:id/last-fire Request last fire
 * @apiName LastFire
 * @apiGroup Game
 */
$app->get('/games/{id}/last-fire', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'LastFire', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
