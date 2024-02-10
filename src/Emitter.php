<?php

declare(strict_types=1);

namespace TurboTimer\SocketIO;

use MessagePack\Packer;

readonly class Emitter
{
    private const string CHANNEL_PREFIX = 'socket.io';
    private const string UID = 'emitter';

    /** @var \Closure(string, string): void */
    private \Closure $onPublish;

    private Packer $packer;


    /**
     * @param \Closure(string, string): void $onPublish
     */
    public function __construct(\Closure $onPublish)
    {
        $this->onPublish = $onPublish;
        $this->packer = new Packer();
    }

    /**
     * @param string $event
     * @param string|array<string, mixed> $data
     * @param array<string> $rooms
     * @param array $exceptRooms
     * @param string $namespace
     * @param array $flags
     */
    public function emit(
        string $event,
        string|array|null $data = null,
        array $rooms = [],
        array $exceptRooms = [],
        string $namespace = '/',
        array $flags = [],
    ): void {
        $message = $this->packer->packArray([
            self::UID,
            [
                'type' => PacketType::Event->value,
                'data' => [$event, $data],
                'nsp' => $namespace,
            ],
            [
                'flags' => $flags,
                'rooms' => $rooms,
                'except' => $exceptRooms,
            ]
        ]);

        $this->publish($message, $namespace);
    }

    /**
     * @param array<string> $roomsToJoin
     * @param array<string> $rooms
     * @param array<string> $exceptRooms
     * @param string $namespace
     * @throws \JsonException
     */
    public function joinRooms(
        array $roomsToJoin,
        array $rooms = [],
        array $exceptRooms = [],
        string $namespace = '/'
    ): void {
        $this->publishRequest([
            'type' => RequestType::RemoteJoin->value,
            'opts' => [
                'rooms' => $rooms,
                'except' => $exceptRooms,
            ],
            'rooms' => $roomsToJoin,
        ], $namespace);
    }

    /**
     * @param array<string> $roomsToLeave
     * @param array<string> $rooms
     * @param array<string> $exceptRooms
     * @param string $namespace
     * @return void
     * @throws \JsonException
     */
    public function leaveRooms(
        array $roomsToLeave,
        array $rooms = [],
        array $exceptRooms = [],
        string $namespace = '/'
    ): void {
        $this->publishRequest([
            'type' => RequestType::RemoteLeave->value,
            'opts' => [
                'rooms' => $rooms,
                'except' => $exceptRooms,
            ],
            'rooms' => $roomsToLeave,
        ], $namespace);
    }

    /**
     * @param array<string> $rooms
     * @param array<string> $exceptRooms
     * @param string $namespace
     * @throws \JsonException
     */
    public function disconnectSockets(array $rooms = [], array $exceptRooms = [], string $namespace = '/'): void
    {
        $this->publishRequest([
            'type' => RequestType::RemoteDisconnect->value,
            'opts' => [
                'rooms' => $rooms,
                'except' => $exceptRooms,
            ],
            'close' => true,
        ], $namespace);
    }

    /**
     * @param array $data
     * @param string $namespace
     * @throws \JsonException
     */
    public function emitToServers(array $data, string $namespace = '/'): void
    {
        $this->publishRequest([
            'uid' => self::UID,
            'type' => RequestType::ServerEmit->value,
            'data' => $data,
        ], $namespace);
    }

    private function publish(string $data, string $namespace): void
    {
        $channel = self::getChannelName($namespace);

        call_user_func($this->onPublish, $channel, $data);
    }

    /**
     * @throws \JsonException
     */
    private function publishRequest(array $data, string $namespace): void
    {
        call_user_func(
            $this->onPublish,
            self::getRequestChannelName($namespace),
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    private static function getChannelName(string $namespace): string
    {
        return sprintf('%s#%s#', self::CHANNEL_PREFIX, $namespace);
    }

    private static function getRequestChannelName(string $namespace): string
    {
        return sprintf('%s-request#%s#', self::CHANNEL_PREFIX, $namespace);
    }
}
