<?php
// Broadcast.php
namespace BCRM;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Broadcast implements MessageComponentInterface
{
    protected $clients;

    public function __construct($port)
    {
        $this->clients = new \SplObjectStorage;
        echo "Server is running on port $port";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $ipAddress = $conn->remoteAddress;

        // เมื่อเชื่อมต่อเปิดให้เก็บข้อมูลเกี่ยวกับแชนเนลของผู้ใช้
        $requestUri = $conn->httpRequest->getUri()->getQuery(); // เช่น "/?channel=my_channel"
        $queryParams = [];
        parse_str($requestUri, $queryParams);
        $channel = isset($queryParams['channel']) ? $queryParams['channel'] : 'default'; // ชื่อช่องเริ่มต้น

        $conn->channel = $channel; // เก็บชื่อช่องใน connection object
        echo "New connection! ({$conn->resourceId}) Channel: ({$channel}) from IP: ({$ipAddress})\n";
    }



    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (isset($data['channel']) && isset($data['event'])) {
            $channel = $data['channel'];
            $event = $data['event'];
            // $data = $data['data'];

            foreach ($this->clients as $client) {
                if ($client !== $from && $client->channel === $channel) {
                    // $client->send(json_encode(['channel' => $channel, 'event' => $event, 'data' => $data]));
                    $client->send(json_encode($data));
                }
            }
        }
    }
}
