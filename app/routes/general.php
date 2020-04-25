<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require_once 'app/classes/Game.php';

/**
 * @api {get} /games Request a summary for all games
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
 * @apiPermission none
 *
 * @apiExample {curl} Example usage:
 *      curl -X POST <domain>/games
 *
 * @apiSuccess (Success) {String} gameId Game ID
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 201 Created
 *      {"gameId":"9543fa9c-14a1-4494-83ac-d81196415c6d"}
 *
 * @apiErrorExample {json} Error response (example):
 *      HTTP/1.1 500 Internal Server Error
 */
$app->post('/games', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game();
        $g->save();
        $gameId = $g->getGameId();
        $status = 201;
        $payload = json_encode(array('gameId' => $gameId));
    } catch (\Throwable $th) {
        $status = 500;
        $payload = '';
    }

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
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
