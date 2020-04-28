### Custom types

#### Position

Specific position on the playing grid.

This type is an array of exactly two (2) numbers between 1 and 10 inclusive. The format is `[line, column]`.

```json
# Position example
[4, 7]
```

#### Shot

Result of a shot on a given position.

This type is an array containing a `position` and a boolean indicating its result (if a ship was successfully hit or not).

```json
# Shot example
[[1, 9], false]
```

### Authentication

Authentication is done with the use of an custom HTTP header, `X-Auth`.
This header must be send **in each request** for all endpoint requiring the `player` permission.

A valid header shoud be requested with [this endpoint](#api-General-JoinGame).

```raw
# Header example
X-Auth: UGxheWVyMTpiMWViYjc2ZjViNzRhYjI4NjFiNzAyNzIwNTFhZGRlMzdiMjAzM2EyOTQ0NjgzOGYxZWVmMDk0ZjhlNTY2Yzk1MGVjODYyOTJiOTI5MzI0OWE3OWIzOGExZWJhODNjNjk3YmY5ZDU3NGQ5NWI3YzBkMTZlNjUyMzllZjQ0NDZiOA==
```

### Body parameters

For all endpoints requiring body parameters, the `Content-Type` HTTP header must be set by the client.
The only officially supported MIME type is `application/json`.

```raw
# JSON MIME type header
Content-Type: application/json
```

These other MIME types _may_ work but this is not guaranteed:

* `application/x-www-form-urlencoded`
* `application/xml`
* `text/xml`

### General errors

These errors may be returned by all endpoints:

* `400 Bad Request`
  * Endpoint called with missing or bad parameters
* `401 Unauthorized`
  * Request do not include a `X-Auth` header
  * `X-Auth` header is invalid for this game
* `403 Forbidden`
  * Multiple reasons, see endpoints descriptions
* `404 Not Found`
  * Requested URL do not exists
  * Requested game do not exist
* `405 Method Not Allowed`
  * Endpoint do not accept this method
* `500 Internal Server Error`
  * Internal error
