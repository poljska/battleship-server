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
 * @apiDescription
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games
 *
 * @apiSuccess (Success response body) {Object[]} games List of all games
 * @apiSuccess (Success response body) {Object} games.game Game summary
 * @apiSuccess (Success response body) {String} games.game.game_id Game ID
 * @apiSuccess (Success response body) {Date='YYYY-mm-dd HH:ii:ss'}} games.game.timestamp Creation timestamp
 * @apiSuccess (Success response body) {Object} games.game.status Additional data
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
 *            "status":"New",
 *            "nbPlayers": 0
 *          }
 *        }
 *      ]
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
 * @apiDescription
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X POST <domain>/games
 *
 * @apiSuccess (Success response body) {String} game_id Game ID
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 201 Created
 *      {
 *        "game_id":"9543fa9c-14a1-4494-83ac-d81196415c6d"
 *      }
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
 * @apiPermission none
 *
 * @apiDescription
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X DELETE <domain>/games
 *
 * @apiSuccessExample {json} Success response:
 *      HTTP/1.1 200 OK
 */
$app->delete('/games', function (Request $request, Response $response, array $args) {
    try {
        $db = new Database();
        $db->execute('DELETE FROM games');
        $status = 200;
    } catch (\Throwable $th) {
        $status = 500;
    }

    $payload = '';
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
});

/**
 * @api {delete} /games/:id Delete a single game
 * @apiName DeleteSingleGame
 * @apiGroup General
 * @apiPermission none
 *
 * @apiDescription
 * Will raise a `400 Bad Request` error if called with bad parameters.
 *
 * Will raise a `404 Not Found` error if the specified game does not exists.
 *
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X DELETE <domain>/games/<:id>
 *
 * @apiParam (URL parameters) {String} :id Game ID
 *
 * @apiSuccessExample {json} Success response:
 *      HTTP/1.1 200 OK
 */
$app->delete('/games/{id}', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        $g->delete();
        $status = 200;
    } catch (\ClientException $th) {
        $status = 400;
    } catch (\InvalidGame $th) {
        $status = 404;
    } catch (\Throwable $th) {
        $status = 500;
    }

    $payload = '';
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
});

/**
 * @api {post} /games/:id/join Join a game
 * @apiName JoinGame
 * @apiGroup General
 * @apiPermission none
 *
 * @apiDescription
 * Will raise a `400 Bad Request` error if called with bad parameters.
 *
 * Will raise a `403 Forbidden` error if the specified game isn't a new game.
 *
 * Will raise a `404 Not Found` error if the specified game does not exists.
 *
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X POST <domain>/games/<:id>/join
 *
 * @apiParam (URL parameters) {String} :id Game ID
 *
 * @apiSuccess (Success response body) {String} X-Auth Authentication token
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "X-Auth":"UGxheWVyMTo1MzllOGNlOGJkNGZmZWM4MGI3MWEyZTZmNTI3N2QzZGQ0NWE2ZjU1MmI4NDRmMTYyNWNlNjI5MGQ2NWFhMTliZjIxZjc0ZWJiNGYyOTU0NTE1ODI4MjQyMWQ0YjIwMjc0MWViNzZhODY0YjRkOWQ0ZWJmNDYyMzcyZjFhMDM1YQ=="
 *      }
 */
$app->post('/games/{id}/join', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        $player = $g->addNewPlayer();
        $g->save();
        $auth = base64_encode($player.':'.hash('sha3-512', $args['id'].':'.$player.':'.getenv('PRIVILEGED')));
        $payload = json_encode(array('X-Auth' => $auth));
        $status = 200;
    } catch (\ClientException $th) {
        $status = 400;
        $payload = '';
    } catch (\ForbiddenOperation $th) {
        $status = 403;
        $payload = '';
    } catch (\InvalidGame $th) {
        $status = 404;
        $payload = '';
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
 * @api {get} /games/:id Request data of a finished game
 * @apiName GetGame
 * @apiGroup General
 * @apiPermission none
 *
 * @apiDescription
 * Will raise a `400 Bad Request` error if called with bad parameters.
 *
 * Will raise a `403 Forbidden` error if the specified game isn't finished.
 *
 * Will raise a `404 Not Found` error if the specified game does not exists.
 *
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games/<:id>
 *
 * @apiParam (URL parameters) {String} :id Game ID
 *
 * @apiSuccess (Success response body) {Object} game Game data
 * @apiSuccess (Success response body) {String} game.game_id Game ID
 * @apiSuccess (Success response body) {Date='YYYY-mm-dd HH:ii:ss'}} game.timestamp Creation timestamp
 * @apiSuccess (Success response body) {Object} game.player_1_ships Position of player 1's ships
 * @apiSuccess (Success response body) {Shot[]} game.player_1_shots Positions targeted by player 1
 * @apiSuccess (Success response body) {Object} game.player_2_ships Position of player 2's ships
 * @apiSuccess (Success response body) {Shot[]} game.player_2_shots Positions targeted by player 2
 * @apiSuccess (Success response body) {Object} game.status Additional data
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "game_id":"f38ab6af-3412-4c4d-ad2a-3c8941ebeb7d",
 *        "timestamp":"2020-04-25 21:06:44",
 *        "player_1_ships":{
 *          "carrier":[[2, 1], [3, 1], [4, 1], [5, 1], [6, 1]],
 *          "destroyer":[[4, 7], [4, 5], [4, 6]],
 *          "submarine":[[4, 9], [2, 9], [3, 9]],
 *          "battleship":[[1, 5], [1, 6], [1, 7], [1, 8]],
 *          "patrol_boat":[[6, 9], [5, 9]]
 *        },
 *        "player_1_shots":[
 *          [[2, 1], true],
 *          [[3, 1], true],
 *          [[4, 1], true],
 *          [[5, 1], true],
 *          [[6, 1], true],
 *          [[1, 5], true],
 *          [[1, 6], true],
 *          [[1, 7], true],
 *          [[1, 8], true],
 *          [[4, 7], true],
 *          [[4, 5], true],
 *          [[4, 6], true],
 *          [[4, 9], true],
 *          [[2, 9], true],
 *          [[3, 9], true],
 *          [[6, 9], true],
 *          [[5, 9], true]
 *        ],
 *        "player_2_ships":{
 *          "carrier":[[2, 1], [3, 1], [4, 1], [5, 1], [6, 1]],
 *          "destroyer":[[4, 7], [4, 5], [4, 6]],
 *          "submarine":[[4, 9], [2, 9], [3, 9]],
 *          "battleship":[[1, 5], [1, 6], [1, 7], [1, 8]],
 *          "patrol_boat":[[6, 9], [5, 9]]
 *        },
 *        "player_2_shots":[
 *          [[1, 1], false],
 *          [[1, 2], false],
 *          [[1, 3], false],
 *          [[1, 4], false],
 *          [[1, 5], true],
 *          [[1, 6], true],
 *          [[1, 7], true],
 *          [[1, 8], true],
 *          [[1, 9], false],
 *          [[1, 10], false],
 *          [[2, 1], true],
 *          [[2, 2], false],
 *          [[2, 3], false],
 *          [[2, 4], false],
 *          [[2, 5], false],
 *          [[2, 6], false]
 *        ],
 *        "status":{
 *          "status":"Finished",
 *          "winner":"Player1"
 *        }
 *      }
 */
$app->get('/games/{id}', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        $data = $g->getGame();
        if ($data['status']['status'] !== 'Finished') throw new ForbiddenOperation('Game is not finished.');
        $status = 200;
        $payload = json_encode($data);
    } catch (\ClientException $th) {
        $status = 400;
        $payload = '';
    } catch (\ForbiddenOperation $th) {
        $status = 403;
        $payload = '';
    } catch (\InvalidGame $th) {
        $status = 404;
        $payload = '';
    } catch (\Throwable $th) {
        $status = 500;
        $payload = '';
    }

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
});
