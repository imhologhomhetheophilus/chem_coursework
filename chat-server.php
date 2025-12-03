<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use PDO;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $pdo;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        // Database connection
        $host = 'mysql-chemcoursework.alwaysdata.net';
        $db   = 'chemcoursework_chemcoursework';
        $user = '435841_chemuser';
        $pass = 'jobjacob123@';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);

        echo "WebSocket Chat Server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if(!$data) return;

        $username = $data['username'];
        $role = $data['role'];
        $message = $data['message'];

        // Save message to DB
        $stmt = $this->pdo->prepare("INSERT INTO chat (username, message) VALUES (:username, :message)");
        $stmt->execute(['username'=>$username, 'message'=>$message]);

        // Prepare HTML to send to all clients
        $stmt = $this->pdo->prepare("SELECT profile_pic FROM users WHERE username=:username");
        $stmt->execute(['username'=>$username]);
        $user = $stmt->fetch();

        $profilePic = $user['profile_pic'] ?? 'default.png';

        $msgHTML = "<div class='flex items-center gap-2 mb-2'>
                        <img src='uploads/".htmlspecialchars($profilePic)."' class='w-8 h-8 rounded-full'>
                        <p><strong>".htmlspecialchars($username);
        if($role === 'admin') $msgHTML .= " 🛡";
        if($role === 'group_leader') $msgHTML .= " ⭐";
        $msgHTML .= ":</strong> ".htmlspecialchars($message)."</p>
                    </div>";

        foreach($this->clients as $client){
            $client->send($msgHTML);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
