<?php
include_once "SocketServer.php";
include_once "GameSession.php";
include_once "Coroutine.php";

set_time_limit(0);
$sock = new SocketServer('localhost', 80, 20);


$sock->listen();

$scheduler = new Scheduler;
$scheduler->newTask($sock->connectClients());


$scheduler->run();

