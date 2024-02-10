# Socket.IO Redis Emitter in PHP

A Redis Emitter implementation for PHP >=8.3.
This package is not dependent on a specific Redis interface.

## Installation

```bash
composer require turbo-timer/socket.io-redis-emitter-php
```

## Usage

Setup:

```php
$redis = new Redis(...) // some Redis implementation.

$onPublish = fn (string $channel, string $message) => $redis->publish($channel, $message);
$emitter = new \TurboTimer\SocketIO\Emitter($onPublish);
```

Emitting events:

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->emit(
    event: 'my_custom_event', 
    data: ['a' => 1, 'b' => 'c'],
);
```

## Examples

Most of the following functions allow you to exclude specific rooms.

Emit event to specific rooms

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->emit(
    event: 'my_custom_event',
    rooms: ['my_room']
);
```

Emit event to every room, except one

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->emit(
    event: 'my_custom_event',
    exceptRooms: ['my_room']
);
```

Make socket join a room

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->joinRooms(
    roomsToJoin: ['my_new_room'],
    rooms: ['socket_id_or_room'],
);
```

Make socket leave a room

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->leaveRooms(
    roomsToLeave: ['my_old_room'],
    rooms: ['socket_id_or_room'],
);
```

Disconnect sockets

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->disconnectSockets(
    rooms: ['socket_id_or_room'],
);
```

Emit server-side messages

```php
/** @var \TurboTimer\SocketIO\Emitter $emitter **/
$emitter->emitToServers(
    data: ['server_time' => time()]
);
```