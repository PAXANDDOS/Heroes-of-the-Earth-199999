<?php

include_once "GameCLasses.php";
include_once 'application/controllers/controller_database.php';

class GameSession
{
    private $client0;
    private $client1;

    private $player0;
    private $player1;

    private $deck;
    private $maxPlayerCardsCount = 5;

    public function __construct($client0, $client1)
    {
        $this->client0 = $client0;
        $this->client1 = $client1;
    }

    public function initSession()
    {
        socket_set_block($this->client0);
        socket_set_block($this->client1);

        $data = SocketServer::decode(socket_read($this->client0, 1024));
        $this->player0 = new Player(json_decode($data['payload'])->playerName, json_decode($data['payload'])->avatar);

        $data = SocketServer::decode(socket_read($this->client1, 1024));
        $this->player1 = new Player(json_decode($data['payload'])->playerName, json_decode($data['payload'])->avatar);

        $this->deck = new Deck();
        $this->deck->createDeck();
        $this->deck->shuffleDeck();

        $this->player0->cards = array_slice($this->deck->cards, 0, 10);
        $this->player1->cards = array_slice($this->deck->cards, 10, 20);

        $this->RandomizeTurn();
        
        socket_set_nonblock($this->client0);
        socket_set_nonblock($this->client1);
        $this->SendPlayersInfoToBothClients();
    }

    public function SessionLoop()
    {        
        while($this->player0->health > 0 || $this->player1->health > 0)
        {
            if($this->player0->canPlay == true)
            {
                $currentPlayer = $this->player0;
                $enemyPlayer = $this->player1;

                $currentClient = $this->client0;
                $enemyClient = $this->client1;
            }
            else
            {
                $currentPlayer = $this->player1;
                $enemyPlayer = $this->player0;

                $currentClient = $this->client1;
                $enemyClient = $this->client0;
            }

            $sockStr = socket_read($enemyClient, 1024);
            if($sockStr === "") 
            {
                $this->closeClients(); 
                break;
            }

            $sockStr = socket_read($currentClient, 1024);
            if($sockStr === false) 
                yield;
            
            if($sockStr === "") 
            {
                $this->closeClients();
                break;
            }

            $data = SocketServer::decode($sockStr);
            if(!isset($data['payload'])) 
                yield;

            $json = json_decode($data['payload']);
            if(!isset($json->nameOfOperation)) 
                yield;

            $nameOfOperation = $json->nameOfOperation;
            if($nameOfOperation == 'changeTurn')
            {
                $this->ChangeTurn($currentPlayer, $enemyPlayer);

                $this->SendPlayersInfoToBothClients();
                yield;
            }
            if($nameOfOperation == 'dropCard')
            {
                $cardIndex = (json_decode($data['payload']))->indexOfCard;
                $currentPlayer->DropCard($cardIndex);

                $this->SendPlayersInfoToBothClients();
                yield;
            }
            if($nameOfOperation == 'attackPlayer')
            {
                $cardIndex = (json_decode($data['payload']))->indexOfCard;
                $currentPlayer->AtackPlayer($cardIndex, $enemyPlayer);

                if($enemyPlayer->status == 'lose')
                {
                    $this->HandleWin($currentPlayer, $enemyPlayer);
                    $this->closeClients();
                    break;
                }
                $this->SendPlayersInfoToBothClients();
                yield;
            }
            if($nameOfOperation == 'attackCard')
            {
                $cardIndex = (json_decode($data['payload']))->indexOfCard;
                $cardToAtackIndex = (json_decode($data['payload']))->indexCardToAttack;
                $currentPlayer->TryToAtackCard($cardIndex, $cardToAtackIndex, $enemyPlayer);

                $this->SendPlayersInfoToBothClients();
                yield;
            }
            yield;
        }
    }

    private function HandleWin($currentPlayer, $enemyPlayer)
    {
        $user = new User();

        $user->incrementTotalGames($currentPlayer->playerName);
        $user->incrementTotalGames($enemyPlayer->playerName);

        $user->incrementTotalWins($currentPlayer->playerName);
        $user->incrementTotalLoses($enemyPlayer->playerName);
    }

    private function ChangeTurn($currentPlayer, $enemyPlayer)
    {
        $currentPlayer->canPlay = false;
        $enemyPlayer->canPlay = true;

        $currentPlayer->UpdateManaCount();
        $currentPlayer->UpdateCardAttack();
        $currentPlayer->TryPickCardsFromDeck();
    }

    private function RandomizeTurn()
    {
        $turn = rand(0, 2);

        if($turn == 0)
            $this->player1->canPlay = false;
        
        else
            $this->player0->canPlay = false;
    }

    private function SendPlayersInfoToBothClients()
    {
        $this->SendPlayersInfoToClient0();
        $this->SendPlayersInfoToClient1();
    }

    private function SendPlayersInfoToClient0()
    {
        $playerInfo = array();
        $playerInfo[] = $this->player0;
        $playerInfo[] = $this->player1;

        socket_write($this->client0, SocketServer::encode(json_encode($playerInfo)));
    }

    private function SendPlayersInfoToClient1()
    {
        $playerInfo = array();
        $playerInfo[] = $this->player1;
        $playerInfo[] = $this->player0;

        socket_write($this->client1, SocketServer::encode(json_encode($playerInfo)));
    }

    private function closeClients()
    {
        socket_shutdown($this->client0);
        socket_shutdown($this->client1);
    }
}
