<?php

$envVars = @parse_ini_file('.env');
$port = isset($envVars['PORT']) && $envVars['PORT'] !== "" ? $envVars['PORT'] : "9090";

startWebSocketServer($port);

function startWebSocketServer($port = 9090)
{
    require 'vendor/autoload.php';

    $server = Ratchet\Server\IoServer::factory(
        new Ratchet\Http\HttpServer(
            new Ratchet\WebSocket\WsServer(
                new BCRM\Broadcast($port)
            )
        ),
        $port
    );
    $server->run();
}