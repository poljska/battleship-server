<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require_once 'app/classes/Game.php';
require_once 'app/classes/Database.php';
require_once 'app/classes/Exceptions.php';

/**
 * @api {get} /games Request a summary for all games
 * @apiName GetAllGames
 * @apiGroup General
 * @apiPermission none
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games
 *
 * @apiSuccess (Success) {Object[]} games List of all games
 * @apiSuccess (Success) {Object} games.game Game summary
 * @apiSuccess (Success) {String} games.game.game_id Game ID
 * @apiSuccess (Success) {Date='YYYY-mm-dd HH:ii:ss'}} games.game.timestamp Creation timestamp
 * @apiSuccess (Success) {Object} games.game.status Additional data
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 200 OK
 *      [
 *        {
 *          "game_id":"2e01d53a-efa4-46fa-8316-6dbc42d01fdf",
 *          "timestamp":"2020-04-25 19:56:58",
 *          "status":{
 *            "turn":"Player1",
 *            "status":"InProgress"
 *          }
 *        },
 *        {
 *          "game_id":"64f75e0e-162e-4c95-b909-87a959cffed2",
 *          "timestamp":"2020-04-25 19:57:13",
 *          "status":{
 *            "turn":"Player1",
 *            "status":"New"
 *          }
 *        }
 *      ]
 *
 * @apiErrorExample {json} Error response (example):
 *      HTTP/1.1 500 Internal Server Error
 */
$app->get('/games', function (Request $request, Response $response, array $args) {
    try {
        $data = array();
        $db = new Database();
        $sql = 'SELECT game_id FROM games';
        foreach ($db->execute($sql) as $row) {
            $g = new Game($row['game_id']);
            array_push($data, $g->getSummary());
        }
        $status = 200;
        $payload = json_encode($data);
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
 * @api {post} /games Create a new game
 * @apiName NewGame
 * @apiGroup General
 * @apiPermission none
 *
 * @apiExample {curl} Example usage:
 *      curl -X POST <domain>/games
 *
 * @apiSuccess (Success) {String} game_id Game ID
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 201 Created
 *      {
 *        "game_id":"9543fa9c-14a1-4494-83ac-d81196415c6d"
 *      }
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
        $payload = json_encode(array('game_id' => $gameId));
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
