<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @api {get} /games/:id/cheat Cheat
 * @apiName Cheat
 * @apiGroup Game
 * @apiPrivate
 * @apiPermission privileged
 * @apiVersion 1.1.0
 *
 * @apiExample {curl} Example usage:
 *      curl -X GET <domain>/games/<:id>/cheat -H 'X-Auth: <:token>'
 *
 * @apiHeader (Request headers) {String} X-Auth Authentication token
 *
 * @apiParam (URL parameters) {String} :id Game ID
 */
$app->get('/games/{id}/cheat', function (Request $request, Response $response, array $args) {
    try {
        if (!$request->hasHeader('X-Auth'))
            throw new InvalidAuth();  // X-Auth HTTP header is not set
        $header = $request->getHeader('X-Auth')[0];
        $auth = base64_decode($header, TRUE);
        if (!$auth)
            throw new InvalidAuth($header);  // Incorrect base64
        if ($auth !== hash('sha3-512', $args['id'].':'.getenv('SERVER_KEY')))
            throw new InvalidAuth($header);  // Incorrect X-Auth HTTP header

        $g = new Game($args['id']);
        $payload = json_encode($g->getGame());
        $status = 200;
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
