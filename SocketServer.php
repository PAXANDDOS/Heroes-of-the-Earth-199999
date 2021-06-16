<?php
include_once "Coroutine.php";
include_once "GameSession.php";

function createSession($clients)
{
    $gameSession = new GameSession($clients[0], $clients[1]);
    $gameSession->initSession();
}


class SocketServer
{
    private $socket;
    private $socketBind;
    private $clients = array();

    private $connCount;

    public function __construct(string $host, int $port, int $conection_count)
    {
        $this->connCount = $conection_count;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");

        $this->socketBind = socket_bind($this->socket, $host, $port) or die("Could not bind to socket\n");
    }

    public function listen()
    {
        $this->socketBind = socket_listen($this->socket) or die("Could not set up socket listener\n");
        socket_set_nonblock($this->socket);
    }

    public function connectClients()
    {
        while(count($this->clients) < $this->connCount)
        {
            if(($newc = $this->accept()) !== false)
            {
                socket_set_block($newc);
                
                if($this->handshake($newc, 1024))
                {
                    socket_set_nonblock($newc);

                    var_dump($newc);

                    $this->clients[] = $newc;
                    
                    if(count($this->clients) >= 2)
                    {
                        $arrClient = [];
                        foreach($this->clients as $index => $client)
                        {
                            $arrClient[] = $client;
                            unset($this->clients[$index]);
                        }
                        $gameSession = new GameSession($arrClient[0], $arrClient[1]);
                        
                        $gameSession->initSession();

                        yield newTask($gameSession->SessionLoop());
                    }
                }
            }
            yield;
        }
    }

    private function accept()
    {
        return socket_accept($this->socket);
    }

    public function read($client) : string
    {
        return socket_read($client, 1024);
    }

    public function write($client, string $output)
    {
        socket_write($client, $output, strlen ($output)) or die("Could not write output\n"); 
    }

    public function close()
    {
        for($i = 0; $i < $this->connCount; $i++)
            socket_close($this->clients[$i]);

        socket_close($this->socket);
    }

    public function getClientByIndex(int $index)
    {
        return $this->clients[$index];
    }

    private function handshake($connect) {

        $info = array();

        $data = socket_read($connect, 1000);

        $lines = explode("\r\n", $data);
        foreach ($lines as $i => $line) {
            if ($i) {
                if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                    $info[$matches[1]] = $matches[2];
                }
            } else {
                $header = explode(' ', $line);
                $info['method'] = $header[0];
                $info['uri'] = $header[1];
            }
            if (empty(trim($line)))
            {
                 break;
                }
        }

        // получаем адрес клиента
        $ip = $port = null;
        if ( ! socket_getpeername($connect, $ip, $port)) {
            return false;
        }
        $info['ip'] = $ip;
        $info['port'] = $port;

        var_dump($info);
        if (empty($info['Sec-WebSocket-Key'])) {
            return false;
        }

        // отправляем заголовок согласно протоколу вебсокета
        $SecWebSocketAccept = 
            base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n";
        socket_write($connect, $upgrade);

        return true;

    }

    public static function encode($payload, $type = 'text', $masked = false) {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;
            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;
            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;
            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    /**
     * Для декодирования сообщений, полученных от клиента
     */
    public static function decode($data) {
        if ( ! strlen($data)) {
            return false;
        }

        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        // unmasked frame is received:
        if (!$isMasked) {
            return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
        }

        switch ($opcode) {
            // text frame:
            case 1:
                $decodedData['type'] = 'text';
                break;
            case 2:
                $decodedData['type'] = 'binary';
                break;
            // connection close frame:
            case 8:
                $decodedData['type'] = 'close';
                break;
            // ping frame:
            case 9:
                $decodedData['type'] = 'ping';
                break;
            // pong frame:
            case 10:
                $decodedData['type'] = 'pong';
                break;
            default:
                return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) {
            return false;
        }

        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }

}
