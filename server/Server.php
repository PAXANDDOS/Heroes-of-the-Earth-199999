<?php
include_once "SocketServer.php";
include_once "GameSession.php";
include_once "Coroutine.php";

set_time_limit(0);
$sock = new SocketServer('0.0.0.0', 5656, 20);


$sock->listen();

$scheduler = new Scheduler;
$scheduler->newTask($sock->connectClients());


$scheduler->run();

