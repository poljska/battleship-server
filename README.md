# Battleship REST API

PHP REST API for the battleship game.

## Table of Contents

1. [Install for developpement](#install-for-developpement)
1. [Database schema](#database-schema)
1. [Generate documentation](#generate-documentation)

## Install for developpement

```sh
# In project root directory
composer install
npm install       # Optional, only if you wish to be able to generate the documentation
```

## Database schema

Database schema used by the API.

```sql
-- PostgreSQL
-- Battleship server database schema

CREATE TABLE Games (
  id SERIAL PRIMARY KEY,          -- Internal ID
  game_id TEXT UNIQUE NOT NULL,   -- UUID v4 identifier
  creation_ts TIMESTAMP NOT NULL, -- Timestamp of the game's creation
  player_1_ships JSONB NOT NULL,  -- Position of player 1's ships
  player_1_shots JSONB NOT NULL,  -- Positions targeted by player 1
  player_2_ships JSONB NOT NULL,  -- Position of player 2's ships
  player_2_shots JSONB NOT NULL,  -- Positions targeted by player 2
  status JSONB NOT NULL           -- Additional informations (status / turn / number of players / winner)
);
```

## Generate documentation

To generate the documentation from source with [apiDoc](https://apidocjs.com/), use the following command. Generated files will be placed in the `/doc` directory.

```sh
# In project root directory
npm run doc
```
