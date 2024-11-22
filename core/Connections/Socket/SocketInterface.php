<?php

namespace Mini\Cms\Connections\Socket;

interface SocketInterface
{
    public function __construct($host, $port);

    public function run();

    /**
     * Handing with incoming connection.
     * @param $client
     * @return mixed
     */
    function performHandshake($client);

    /**
     * Decoding received data.
     * @param $data
     * @return mixed
     */
    function decode($data);

    /**
     * Encoding data to send in response.
     * @param $data
     * @return mixed
     */
    function encode($data);

    /**
     * Send response to a client.
     * @param $client
     * @param $message
     * @return mixed
     */
    function send($client, $message);

    /**
     * Disconnect client connected to.
     * @param $client
     * @return mixed
     */
    function disconnect($client);

    public function __destruct();
}