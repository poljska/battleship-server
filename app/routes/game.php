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
 * @apiPermission player
 *
 * @apiDescription
 * Will raise a `400 Bad Request` error if called with bad parameters.
 *
 * Will raise a `403 Forbidden` error if the request do not include a valid X-Auth header.
 *
 * Will raise a `403 Forbidden` error if the specified game isn't ongoing.
 *
 * Will raise a `404 Not Found` error if the specified game does not exists.
 *
 * Will raise a `500 Internal Server Error` error if an internal error occurs.
 *
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games/<:id>/last-fire -H 'X-Auth: <:token>'
 *
 * @apiHeader (Request headers) {String} X-Auth Authentication token
 * @apiHeaderExample {String} Headers (example):
 *      X-Auth: UGxheWVyMTpiMWViYjc2ZjViNzRhYjI4NjFiNzAyNzIwNTFhZGRlMzdiMjAzM2EyOTQ0NjgzOGYxZWVmMDk0ZjhlNTY2Yzk1MGVjODYyOTJiOTI5MzI0OWE3OWIzOGExZWJhODNjNjk3YmY5ZDU3NGQ5NWI3YzBkMTZlNjUyMzllZjQ0NDZiOA==
 *
 * @apiParam (URL parameters) {String} :id Game ID
 *
 * @apiSuccess (Success response body) {String} player Last player's name
 * @apiSuccess (Success response body) {Shot} last_shot Last shot
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "player":"Player2",
 *        "last_shot":[[2, 6], false]
 *      }
 */
$app->get('/games/{id}/last-fire', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        if (!$g->isInProgress()) throw new ForbiddenOperation('This game is not in progress.');
        if (!validAuth($request, $args['id'])) throw new ForbiddenOperation('Incorrect X-Auth HTTP header.');
        $data = $g->getGame();
        switch ($g->getTurn()) {
            case 'Player1':
                $data = array(
                    'player' => 'Player2',
                    'last_shot' => end($data['player_2_shots'])
                );
                break;
            case 'Player2':
                $data = array(
                    'player' => 'Player1',
                    'last_shot' => end($data['player_1_shots'])
                );
                break;
        }
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

function validAuth(Request $request, $gameId) {
    if (!$request->hasHeader('X-Auth')) return FALSE;  // X-Auth HTTP header is no set
    $auth = base64_decode($request->getHeader('X-Auth')[0], TRUE);
    if (!$auth) return FALSE;  // Incorrect base64
    $auth = explode(':', $auth);  // [0] is the player's name ; [1] is the integrity hash
    if ($auth[1] !== hash('sha3-512', $gameId.':'.$auth[0].':'.getenv('PRIVILEGED'))) return FALSE;
        // Incorrect X-Auth HTTP header
    return TRUE;
}
