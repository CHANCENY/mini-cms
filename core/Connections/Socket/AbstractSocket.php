<?php

namespace Mini\Cms\Connections\Socket;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;

class AbstractSocket
{

    protected $socket;
    protected array $clients = [];

    protected array $userConnections = [];

    public function __construct($host, $port)
    {
        // Create a TCP/IP socket
        $this->socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        \socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        if(\socket_bind($this->socket, $host, $port)){
            \socket_listen($this->socket);
            echo "Server started at ws://{$host}:{$port}\n";
        }else {
            throw new \RuntimeException('Socket could not be opened.');
        }
    }

    public function run()
    {
        while (true) {
            $read = array_merge([$this->socket], $this->clients);
            $write = $except = null;

            \socket_select($read, $write, $except, 1); // Timeout of 1 second for polling

            if (in_array($this->socket, $read)) {
                // Accept new client
                $clientSocket = \socket_accept($this->socket);
                $this->clients[] = $clientSocket;

                // Perform handshake and store the user connection
                $this->performHandshake($clientSocket);
                $this->associateUserConnection($clientSocket);
                echo "connected\n";
            }

            // Handle client messages
            foreach ($read as $clientSocket) {
                if ($clientSocket !== $this->socket) {
                    $data = @\socket_recv($clientSocket, $buffer, 2048, 0);

                    if (!$data) {
                        $this->disconnect($clientSocket);
                        continue;
                    }

                    $message = $this->decode($buffer);
                    echo "Received: $message\n";
                    $this->runCustomListen($clientSocket, $message);
                }
            }
        }
    }

    protected function performHandshake($client): void
    {
        echo "Handshake received:\n";
        $headers = \socket_read($client, 2048);
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches)) {
            $key = trim($matches[1]);
            $acceptKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
            \socket_write($client, $response, strlen($response));
            echo "Done handshake received: $response\n";
        }
    }


    protected function decode($data): string
    {
        $length = ord($data[1]) & 127;

        if ($length === 126) {
            $masks = substr($data, 4, 4);
            $payload = substr($data, 8);
        } elseif ($length === 127) {
            $masks = substr($data, 10, 4);
            $payload = substr($data, 14);
        } else {
            $masks = substr($data, 2, 4);
            $payload = substr($data, 6);
        }

        $decoded = '';
        for ($i = 0; $i < strlen($payload); ++$i) {
            $decoded .= $payload[$i] ^ $masks[$i % 4];
        }

        return $decoded;
    }


    protected function encode($data): string
    {
        $length = strlen($data);
        $header = chr(129); // 129 = 0x81 for text frame
        if ($length <= 125) {
            $header .= chr($length);
        } else {
            $header .= chr(126) . pack('n', $length);
        }
        return $header . $data;
    }


    protected function send($client, $message): void
    {
        $encodedMessage = $this->encode($message);
        \socket_write($client, $encodedMessage, strlen($encodedMessage));
    }


    protected function disconnect($client): void
    {
        $key = array_search($client, $this->clients);
        unset($this->clients[$key]);
        \socket_close($client);
        echo "Client disconnected\n";
    }

    protected function associateUserConnection($client): void {
        $user = new CurrentUser();
        $time = time();
        if($user->id()) {
            $this->userConnections[$user->id()] = $client;
        }else {
            $this->userConnections[$time] = $client;
        }
        $this->send($client, json_encode([
            'user' => $user->id() ?? $time,
            'status' => 'connected',
            'message' => 'Connection established successfully',
            'notice' => 'always send your data in '.ContentType::APPLICATION_JSON->value. ' with mandatory key user values with this user value provided'
        ], JSON_PRETTY_PRINT));
    }

    protected function runCustomListen($socket, $data): void
    {
        $active_modules = Extensions::activeModules();
        if(empty($active_modules)) {
            $this->send($socket, "Data received successfully but no active modules found.");
            return;
        }
        $results = [];
        Extensions::runHooks('_socket_connection_handle', [$data, &$results]);
        if(empty($results)) {
            $this->send($socket, json_encode([
                'status' => 'connected',
                'message' => 'not data found',
                'code' => 404,
            ]));
            return;
        }
        $this->send($socket, json_encode([
            'status' => 'connected',
            'code' => 200,
            'data' => $results,
            'message' => '',
        ]));
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }
}