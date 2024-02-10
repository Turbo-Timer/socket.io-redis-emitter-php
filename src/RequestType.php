<?php

namespace TurboTimer\SocketIO;

enum RequestType: int
{
    case RemoteJoin = 2;
    case RemoteLeave = 3;
    case RemoteDisconnect = 4;
    case ServerEmit = 6;
}
