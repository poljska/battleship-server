<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require_once 'app/classes/Game.php';
require_once 'app/classes/Exceptions.php';

/**
 * @apiDefine player Restricted to players
 * The client must be a player of the current game and send the X-Auth HTTP header accordingly.
 */

/**
 * @api {get} /games/:id/current Request data of a ongoing game
 * @apiName GetCurrentGame
 * @apiGroup Game
 * @apiPermission player
 *
 * @apiDescription
 * Will return a `400 Bad Request` error if called with bad parameters.
 *
 * Will return a `401 Unauthorized` error if the request do not include a valid X-Auth header.
 *
 * Will return a `404 Not Found` error if the specified game does not exists.
 *
 * Will return a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games/<:id>/current -H 'X-Auth: <:token>'
 *
 * @apiHeader (Request headers) {String} X-Auth Authentication token
 * @apiHeaderExample {String} Headers (example):
 *      X-Auth: UGxheWVyMTpiMWViYjc2ZjViNzRhYjI4NjFiNzAyNzIwNTFhZGRlMzdiMjAzM2EyOTQ0NjgzOGYxZWVmMDk0ZjhlNTY2Yzk1MGVjODYyOTJiOTI5MzI0OWE3OWIzOGExZWJhODNjNjk3YmY5ZDU3NGQ5NWI3YzBkMTZlNjUyMzllZjQ0NDZiOA==
 *
 * @apiParam (URL parameters) {String} :id Game ID
 *
 * @apiSuccess (Success response body) {String} game_id Game ID
 * @apiSuccess (Success response body) {Date='YYYY-mm-dd HH:ii:ss'}} timestamp Creation timestamp
 * @apiSuccess (Success response body) {Object} [player_N_ships] Position of current player's ships
 * @apiSuccess (Success response body) {Shot[]} [player_1_shots] Positions targeted by player 1
 * @apiSuccess (Success response body) {Shot[]} [player_2_shots] Positions targeted by player 2
 * @apiSuccess (Success response body) {Object} status Additional data
 * @apiSuccessExample {json} Success response [new game] (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "game_id":"76f70f6c-884b-4747-a54e-d3b2f887c114",
 *        "timestamp":"2020-04-27 21:33:51",
 *        "status":{
 *          "status":"New",
 *          "nbPlayers":2
 *        }
 *      }
 * @apiSuccessExample {json} Success response [ongoing game] (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "game_id":"e9d448cf-cfc0-4562-a646-9eb6aead8747",
 *        "timestamp":"2020-04-27 21:26:34",
 *        "player_1_ships":{
 *          "carrier":[[2,1], [3,1], [4,1], [5,1], [6,1]],
 *          "destroyer":[[4,7], [4,5], [4,6]],
 *          "submarine":[[4,9], [2,9], [3,9]],
 *          "battleship":[[1,5], [1,6], [1,7], [1,8]],
 *          "patrol_boat":[[6,9], [5,9]]
 *        },
 *        "player_1_shots":[
 *          [[2,1], true],
 *          [[3,1], true]
 *        ],
 *        "player_2_shots":[
 *          [[1,1], false]
 *        ],
 *        "status":{
 *          "turn":"Player2",
 *          "status":"InProgress"
 *        }
 *      }
 */
$app->get('/games/{id}/current', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        $player = validAuth($request, $args['id']);
        $data = $g->getPlayerView($player);
        $status = 200;
        $payload = json_encode($data);
    } catch (\ClientException $th) {
        $status = 400;
        $payload = '';
    } catch (\InvalidAuth $th) {
        $status = 401;
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
 * @api {get} /games/:id/last-shot Request last shot
 * @apiName LastShot
 * @apiGroup Game
 * @apiPermission player
 *
 * @apiDescription
 * Will return a `400 Bad Request` error if called with bad parameters.
 *
 * Will return a `401 Unauthorized` error if the request do not include a valid X-Auth header.
 *
 * Will return a `403 Forbidden` error if the specified game is neither ongoing nor finished.
 *
 * Will return a `404 Not Found` error if the specified game does not exists.
 *
 * Will return a `500 Internal Server Error` error if an internal error occurs.
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games/<:id>/last-shot -H 'X-Auth: <:token>'
 *
 * @apiHeader (Request headers) {String} X-Auth Authentication token
 * @apiHeaderExample {String} Headers (example):
 *      X-Auth: UGxheWVyMTpiMWViYjc2ZjViNzRhYjI4NjFiNzAyNzIwNTFhZGRlMzdiMjAzM2EyOTQ0NjgzOGYxZWVmMDk0ZjhlNTY2Yzk1MGVjODYyOTJiOTI5MzI0OWE3OWIzOGExZWJhODNjNjk3YmY5ZDU3NGQ5NWI3YzBkMTZlNjUyMzllZjQ0NDZiOA==
 *
 * @apiParam (URL parameters) {String} :id Game ID
 *
 * @apiSuccess (Success response body) {Object} last_shot Last shot informations
 * @apiSuccess (Success response body) {String} last_shot.player Last player's name
 * @apiSuccess (Success response body) {Shot} last_shot.shot Last shot
 * @apiSuccess (Success response body) {Object} status Additional data
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "last_shot": {
 *          "player": "Player1",
 *          "shot": [[5, 9], true]
 *        },
 *        "status": {
 *          "status": "Finished",
 *          "winner": "Player1"
 *        }
 *      }
 */
$app->get('/games/{id}/last-shot', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        validAuth($request, $args['id']);
        $gameData = $g->getGame();
        if ($gameData['status']['status'] === 'Finished')
            $player = $gameData['status']['winner'];
        elseif ($gameData['status']['status'] === 'InProgress')
            $player = $gameData['status']['turn'] === 'Player1' ? 'Player2' : 'Player1';
        else
            throw new ForbiddenOperation('Game is neither ongoing nor finished.');
        $shot = $player === 'Player1'
                ? $gameData['player_1_shots']
                : $gameData['player_2_shots'];
        $shot = end($shot);
        $data = array(
            'last_shot' => array(
                'player' => $player,
                'shot' => $shot
            ),
            'status' => $gameData['status']
        );
        $status = 200;
        $payload = json_encode($data);
    } catch (\ClientException $th) {
        $status = 400;
        $payload = '';
    } catch (\InvalidAuth $th) {
        $status = 401;
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

function validAuth(Request $request, $gameId) {
    if (!$request->hasHeader('X-Auth')) throw new InvalidAuth();  // X-Auth HTTP header is not set
    $header = $request->getHeader('X-Auth')[0];
    $auth = base64_decode($header, TRUE);
    if (!$auth) throw new InvalidAuth($header);  // Incorrect base64
    $auth = explode(':', $auth);  // [0] is the player's name ; [1] is the integrity hash
    if ($auth[1] !== hash('sha3-512', $gameId.':'.$auth[0].':'.getenv('PRIVILEGED')))
        throw new InvalidAuth($header);  // Incorrect X-Auth HTTP header
    if (!Game::isPlayer($auth[0])) throw new InvalidPlayer($auth[0]);
    return $auth[0];
}
