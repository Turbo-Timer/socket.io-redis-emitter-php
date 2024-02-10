<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use TurboTimer\SocketIO\Emitter;
use TurboTimer\SocketIO\RequestType;

class EmitterTest extends TestCase
{
    private string $channel = '';
    private string $message = '';

    private readonly Emitter $emitter;

    protected function setUp(): void
    {
        parent::setUp();

        $fn = function (string $channel, string $message) {
            $this->channel = $channel;
            $this->message = $message;
        };

        $this->emitter = new Emitter($fn);
    }

    public function testEmit(): void
    {
        $this->emitter->emit(
            event: 'event',
            data: ['data' => 1],
            rooms: ['my_room', 'my_other_room'],
            exceptRooms: ['my_other_room'],
            namespace: 'custom_nsp',
        );

        self::assertSame('socket.io#custom_nsp#', $this->channel);
        self::assertNotEmpty($this->message);
    }

    public function testRemoteJoinPayload(): void
    {
        $this->emitter->joinRooms(['new_room'], ['existing_room'], ['except_this_room']);

        self::assertSame('socket.io-request#/#', $this->channel);
        self::assertSame([
            'type' => RequestType::RemoteJoin->value,
            'opts' => [
                'rooms' => ['existing_room'],
                'except' => ['except_this_room'],
            ],
            'rooms' => ['new_room'],
        ], json_decode($this->message, true));
    }

    public function testRemoteLeavePayload(): void
    {
        $this->emitter->leaveRooms(['inactive_room'], ['existing_room'], ['except_this_room']);

        self::assertSame('socket.io-request#/#', $this->channel);
        self::assertSame([
            'type' => RequestType::RemoteLeave->value,
            'opts' => [
                'rooms' => ['existing_room'],
                'except' => ['except_this_room'],
            ],
            'rooms' => ['inactive_room'],
        ], json_decode($this->message, true));
    }

    public function testDisconnectPayload(): void
    {
        $this->emitter->disconnectSockets(['existing_room'], ['except_this_room']);

        self::assertSame('socket.io-request#/#', $this->channel);
        self::assertSame([
            'type' => RequestType::RemoteDisconnect->value,
            'opts' => [
                'rooms' => ['existing_room'],
                'except' => ['except_this_room'],
            ],
            'close' => true,
        ], json_decode($this->message, true));
    }

    public function testServerEmitPayload(): void
    {
        $this->emitter->emitToServers(['a' => 'b']);

        self::assertSame('socket.io-request#/#', $this->channel);
        self::assertSame([
            'uid' => 'emitter',
            'type' => RequestType::ServerEmit->value,
            'data' => ['a' => 'b'],
        ], json_decode($this->message, true));
    }
}