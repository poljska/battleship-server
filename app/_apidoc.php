<?php
/**
 * @api {patch} /games/:id/fire Fire at opponent
 * @apiName Fire
 * @apiGroup Game
 * @apiPermission player
 * @apiVersion 1.0.0
 *
 * @apiDescription
 * Will return a `403 Forbidden` error if the specified game isn't in progress.
 *
 * @apiExample {curl} Example usage:
 *      curl -X PATCH <domain>/games/<:id>/fire -H 'X-Auth: <:token>' -H 'Content-Type: application/json' -d '<:body>'
 *
 * @apiHeader (Request headers) {String} X-Auth Authentication token
 * @apiHeader (Request headers) {String} Content-Type Body MIME type
 *
 * @apiParam (URL parameters) {String} :id Game ID
 * @apiParam (Body parameters) {Position} :position Targeted position
 * @apiParamExample {text} JSON body (example):
 *      [1, 9]
 *
 * @apiSuccess (Success response body) {Shot} shot Shot result
 * @apiSuccess (Success response body) {Object} status Additional data
 * @apiSuccessExample {text} Success response (example):
 *      HTTP/1.1 200 OK
 *      {
 *        "shot": [[1, 9], false],
 *        "status": {
 *          "turn": "Player2",
 *          "status": "InProgress"
 *        }
 *      }
 *
 */