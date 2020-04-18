<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api {get} /games/:id/cheat Cheat
 * @apiName Cheat
 * @apiGroup Game
 * @apiPrivate
 */
$app->get('/games/{id}/cheat', function (Request $request, Response $response, array $args) {
    $gameId = $args['id'];

    $data = array('endpoint' => 'Cheat', 'gameId' => $gameId);
    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
