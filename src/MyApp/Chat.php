<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        
        // if ($this->clients->count() <=2 )
        // {
        //     $this->clients->attach($conn);
        // } else {
        //     echo "TOO MANY CONNECTIONS! ONLY 2 ALLOWED AT A TIME";
        // }
        
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $msgstring = $msg;
        echo $msgstring . "\n";
        $msgstart = strrpos($msgstring, "message:");
        $msgend = strrpos($msgstring, "}");
        
        $msgout = substr($msgstring, $msgstart, $msgend - $msgstart);
        echo "msgout: " . $msgout . "\n";

        $contents = explode(": ", $msgout);
        
        $msgcontent = $contents[1];
        
        echo $contents[1] . "\n";

        echo "msgcontent: ". $msgcontent . "\n";
        
        $badtag = '<script';
        $pos = strpos($msgcontent, $badtag);

        if ($pos === false) {
            echo "BAD TAG was not found in the string, delivering to all participants. \n";
            foreach ($this->clients as $client) {

                if ($from !== $client) {
                    // The sender is not the receiver, send to each client connected        
                    $client->send($msg);
                }
            }

        } else {
            echo "FOUND A BAD TAG!!! NOT SENDING THIS MESSAGE TO ANYBODY! \n";
        }

    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}