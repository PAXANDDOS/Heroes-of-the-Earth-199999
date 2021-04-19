<?php
    class Card 
    {
        function __construct($alias, $attack, $defence, $cost, $images) 
        {
            $this->alias = $alias;
            $this->attack = $attack;
            $this->defence = $defence;
            $this->cost = $cost;
            $this->canAtack = false;
            $this->images = $images;
        }
    }
    
    class Deck 
    {
        public $cards;
        public function __construct() 
        {
            $this->cards = [];
        }
    
        public function createDeck() 
        {
            $alias = ['Black Widow', 'Captain America', 'Dr. Strange', 'Drax', 'Nick Fury', 'Gamora', 
            'Groot', 'Iron-man', 'Loki', 'Nebula', 'Deadpool', 'Rocket', 'Spider-man', 'Star-Lord', 'Thanos', 
            'Thor', 'Vision', 'Wanda Maximoff', 'Winter Soldier', 'Yondu'];
            $attack =   [1, 2, 3, 5, 2, 1, 3, 4, 3, 4, 1, 3, 5, 1, 6, 7, 4, 3, 5, 2];
            $defence =  [3, 3, 4, 4, 2, 3, 2, 9, 2, 1, 8, 7, 2, 4, 7, 4, 5, 3, 5, 5];
            $cost =     [1, 2, 3, 4, 1, 1, 2, 6, 2, 2, 4, 5, 3, 2, 6, 5, 4, 2, 4, 3];
            $images = ['blackWidow.png', 'captainAmerica.png', 'doctor.png', 'drax.png', 'fury.png', 'gamora.png',
            'groot.png', 'iron.png', 'loki.png', 'nebula.png', 'pool.png', 'rocket.png', 'spider.png', 'starLord.png',
            'thanos.png', 'thor.png', 'vision.png', 'wanda.png', 'winter.png', 'yondu.png'];
            for ($i = 0; $i < count($alias); $i++) 
                array_push($this->cards, new Card($alias[$i], $attack[$i], $defence[$i], $cost[$i], $images[$i]));
        }

        public function shuffleDeck() 
        {
            shuffle($this->cards);
        }

        public function PickCards($count)
        { 
            if($count <= count($this->cards))
            {
                $cards = array_slice($this->cards, count($this->cards) - $count - 2, $count);
                array_splice($this->cards, count($this->cards) - $count - 2, $count);

                return $cards;
            }
        }
    }

    class Player 
    {
        public $playerName;
        public $avatar;
        public $health; 
        public $mana;
        public $maxMana;
        public $cards = array();
        public $usedCards = array();
        public $maxPlayerCardsCount;

        public $status = 'play';
        public $canPlay;

        function __construct($name, $avatar) 
        {
            $this->playerName = $name;
            $this->health = 20;
            $this->mana = 1;
            $this->currentMana = 1;
            $this->usedCards = [];
            $this->canPlay = true;
            $this->maxMana = 6;
            $this->avatar = $avatar;
            $this->maxPlayerCardsCount = 5;
        }

        public function setCards($cards)
        {
            $this->cards = $cards;
        }

        public function DropCard($index)
        {
            if($this->cards[$index]->cost <= $this->mana)
            {
                $this->mana -= $this->cards[$index]->cost;
                $this->usedCards[] = $this->cards[$index];
                array_splice($this->cards, $index, 1);
            }
        }

        public function TryToAtackCard($index, $indexCardToAtack, $enemy)
        {
            $card = $this->usedCards[$index];
            if($card->canAtack)
            {
                $enemy->usedCards[$indexCardToAtack]->defence -= $card->attack;
                $card->defence -= $enemy->usedCards[$indexCardToAtack]->attack;

                $card->canAtack = false;

                if($this->usedCards[$index]->defence <= 0)
                    array_splice($this->usedCards, $index, 1);

                if($enemy->usedCards[$indexCardToAtack]->defence <= 0)
                    array_splice($enemy->usedCards, $indexCardToAtack, 1);
            }
        }

        public function AtackPlayer($index, $player)
        {
            $card = $this->usedCards[$index];
            if($card->canAtack)
            {
                $player->health -= $card->attack;
                if($player->health <= 0)
                {
                    $player->health = 0;
                    $player->status = 'lose';
                    $this->status = 'win';
                }

                $card->canAtack = false;
            }
        }

        public function UpdateManaCount()
        {
            if($this->currentMana < $this->maxMana)
                $this->currentMana++;
            $this->mana = $this->currentMana;
        }

        public function UpdateCardAttack()
        {
            foreach($this->usedCards as $card)
                $card->canAtack = true;
        }

        public function TryPickCardsFromDeck($deck)
        {
            $countOfNewCards = $this->maxPlayerCardsCount - count($this->cards);
            if($countOfNewCards !== 0)
                $newCards = $deck->PickCards($countOfNewCards);
            
            if(isset($newCards)) 
                $this->cards = array_merge($this->cards, $newCards);
        }
}
