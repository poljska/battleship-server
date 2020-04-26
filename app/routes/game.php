<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games/87b7efd3-ff7b-42cf-977b-3b672021649c/last-fire -H 'X-Auth: UGxheWVyMTpiMWViYjc2ZjViNzRhYjI4NjFiNzAyNzIwNTFhZGRlMzdiMjAzM2EyOTQ0NjgzOGYxZWVmMDk0ZjhlNTY2Yzk1MGVjODYyOTJiOTI5MzI0OWE3OWIzOGExZWJhODNjNjk3YmY5ZDU3NGQ5NWI3YzBkMTZlNjUyMzllZjQ0NDZiOA=='
 *
 * @apiHeader (Headers) {String} X-Auth Authentication token.
 * @apiHeaderExample {String} Headers (example):
 *      X-Auth: UGxheWVyMTpiMWViYjc2ZjViNzRhYjI4NjFiNzAyNzIwNTFhZGRlMzdiMjAzM2EyOTQ0NjgzOGYxZWVmMDk0ZjhlNTY2Yzk1MGVjODYyOTJiOTI5MzI0OWE3OWIzOGExZWJhODNjNjk3YmY5ZDU3NGQ5NWI3YzBkMTZlNjUyMzllZjQ0NDZiOA==
 *
 * @apiParam (Parameters) {String} :id Game ID
 *
 * @apiSuccess (Success) {String} player Last player's name
 * @apiSuccess (Success) {Shot} last_shot Last shot
 * @apiSuccessExample {json} Success response (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "player":"Player2",
 *        "last_shot":[[2, 6], false]
 *      }
 *
 * @apiErrorExample {json} Error response (bad client request):
 *      HTTP/1.1 400 Bad Request
 * @apiErrorExample {json} Error response (not an ongoing game or wrong X-Auth header):
 *      HTTP/1.1 403 Forbidden
 * @apiErrorExample {json} Error response (game does not exist):
 *      HTTP/1.1 404 Not Found
 * @apiErrorExample {json} Error response (server error):
 *      HTTP/1.1 500 Internal Server Error
 */
$app->get('/games/{id}/last-fire', function (Request $request, Response $response, array $args) {
    try {
        $g = new Game($args['id']);
        if (!$g->isInProgress()) throw new ForbiddenOperation('This game is not in progress.');
        if (!$request->hasHeader('X-Auth')) throw new ForbiddenOperation('X-Auth HTTP header is no set.');
        $auth = base64_decode($request->getHeader('X-Auth')[0]);
        if (!$auth) throw new ForbiddenOperation('X-Auth HTTP header is incorrect.');
        $auth = explode(':', $auth);
        if ($auth[1] !== hash('sha3-512', $args['id'].':'.$auth[0].':'.getenv('PRIVILEGED')))
            throw new ForbiddenOperation('X-Auth HTTP header is incorrect.');

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
