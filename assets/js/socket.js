var yes = true;
var currentCardId = null;
window.addEventListener('DOMContentLoaded', function () {

    var socket;
    function showMessage(message) {
        console.log(message);
    }

    if(window.location.href.indexOf("board") > -1) {
        // новое соединение открываем, если старое соединение закрыто
        if (socket === undefined || socket.readyState !== 1) {
            socket = new WebSocket("ws://hoe-server.herokuapp.com");
        } else {
            showMessage('Надо закрыть уже имеющееся соединение');
        }

        socket.onmessage = function (event) { // при получении данных от сервера
            let data = event.data;
            document.getElementsByClassName('searchScreen')[0].style.display = "none";
            document.getElementById('mainGame').style.display = "block";
            let players = parseToObj(data);
            //console.log(players);
            if(yes) {
                render(players);
                yes = false;
            }
            else {
                let item = document.querySelector('.player_handInner');
                while (item.firstChild)
                    item.removeChild(item.firstChild);

                item = document.querySelector('.player_deck');
                while (item.firstChild)
                    item.removeChild(item.firstChild);

                item = document.querySelector('.enemy_deck');
                while (item.firstChild)
                    item.removeChild(item.firstChild);

                item = document.querySelector('.enemy_handInner');
                while (item.firstChild)
                    item.removeChild(item.firstChild);
                
                item = document.querySelector('head');
                item.removeChild(item.lastChild);

                render(players);
            }
        }
        socket.onopen = function () { // при установке соединения с сервером
            let login =  document.cookie.replace(/(?:(?:^|.*;\s*)login\s*\=\s*([^;]*).*$)|^.*$/, "$1");
            let avatar =  document.cookie.replace(/(?:(?:^|.*;\s*)avatar\s*\=\s*([^;]*).*$)|^.*$/, "$1");

            socket.send("{\"playerName\":\"" + login + "\",\"avatar\":\"" + avatar + "\"}");
            showMessage('Соединение с сервером установлено');
        }
        socket.onerror = function(error) { // если произошла какая-то ошибка
            showMessage('Произошла ошибка: ' + error.message);
        };
        socket.onclose = function(event) { // при закрытии соединения с сервером
            window.location.replace("/lobby");
            showMessage('Соединение с сервером закрыто');
            if (event.wasClean) {
                showMessage('Соединение закрыто чисто');
            } else {
                showMessage('Обрыв соединения'); // например, «убит» процесс сервера
            }
            showMessage('Код: ' + event.code + ', причина: ' + event.reason);
        };
    }
    else {
        if (socket !== undefined && socket.readyState === 1) {
            socket.close();
            showMessage('Соединение с сервером закрыто');
        } else {
            showMessage('Соединение с сервером уже было закрыто');
        }
    }

    document.getElementById('endturn').onclick = function () {
        if (socket !== undefined && socket.readyState === 1) {
            let message = changeTurn();
            socket.send(message);
            showMessage('Отправлено сообщение серверу: ' + message);
        } else {
            showMessage('Невозможно отправить сообщение, нет соединения');
        }
    };

    document.getElementById('enemyAvatar').onclick = function () {
        if(currentCardId != null) {
            if (socket !== undefined && socket.readyState === 1) {
                let message = attackPlayer(currentCardId);
                socket.send(message);
                showMessage('Отправлено сообщение серверу: ' + message);
            }
            else {
                showMessage('Невозможно отправить сообщение, нет соединения');
            }
        }
    };

    function render(players) {
        if(players[0].canPlay == false) {
            document.getElementById('endturn').style.backgroundImage = "url('/assets/images/enemyTurn.png')";
            document.getElementsByClassName('player_board')[0].style.filter = "brightness(0.7)";
            document.getElementsByClassName('player_board')[0].style.webkitFilter = "brightness(0.7)";
        }
        else {
            document.getElementById('endturn').style.backgroundImage = "url('/assets/images/endturn.png')";
            document.getElementsByClassName('player_board')[0].style.filter = "brightness(1)";
            document.getElementsByClassName('player_board')[0].style.webkitFilter = "brightness(1)";
        }

        currentCardId = null;
        document.getElementById('enemyUsername').innerHTML = players[1].playerName;
        document.getElementById('enemyAvatar').src = "/assets/images/avatars/" + players[1].avatar +".jpeg";
        document.getElementById('enemy_currentStones').innerHTML = players[1].mana;
        document.getElementById('enemy_currentHealth').innerHTML = players[1].health;
        for(let i = 0; i < players[1].cards.length; i++) {
            let card = `<div class="enemy_handCard"></div>`;
            document.getElementsByClassName('enemy_handInner')[0].insertAdjacentHTML('beforeend',card);
        }
        for(let i = 0; i < players[1].usedCards.length; i++) {
            let card = `<div class="entity_card enemyUsedCards" value=`+i+`>
            <img src="/assets/images/cards/`+players[1].usedCards[i].images+`" alt="enemyCard">
            <span id="card_currentAttack">`+players[1].usedCards[i].attack+`</span>
            <span id="card_currentDefense">`+players[1].usedCards[i].defence+`</span>
            </div>`;
            document.getElementsByClassName('enemy_deck')[0].insertAdjacentHTML('beforeend',card);
            document.getElementsByClassName('enemyUsedCards')[i].onclick = function () {
                let enemyCard = document.getElementsByClassName('enemyUsedCards')[i].getAttribute("value");
                if(currentCardId != null) {
                    if (socket !== undefined && socket.readyState === 1) {
                        let message = attackCard(currentCardId, enemyCard);
                        socket.send(message);
                        showMessage('Отправлено сообщение серверу: ' + message);
                    }
                    else {
                        showMessage('Невозможно отправить сообщение, нет соединения');
                    }
                }
            };
        }
        for(let i = 0; i < players[0].usedCards.length; i++) {
            let canAttack = " class='cardCanNotAttack' ";
            if(players[0].usedCards[i].canAtack == true && players[0].canPlay == true) 
                canAttack = " class='cardCanAttack' ";
            
            let card = `<div class="entity_card playerUsedCards" value=`+i+` data-tilt data-tilt-reverse="true" data-tilt-max="15" data-tilt-scale="1.2">
            <img `+canAttack+`src="/assets/images/cards/`+players[0].usedCards[i].images+`" alt="enemyCard">
            <span id="card_currentAttack">`+players[0].usedCards[i].attack+`</span>
            <span id="card_currentDefense">`+players[0].usedCards[i].defence+`</span>
            </div>`;
            document.getElementsByClassName('player_deck')[0].insertAdjacentHTML('beforeend',card);
            document.getElementsByClassName('playerUsedCards')[i].onclick = function () {
                if(players[0].usedCards[i].canAtack == true) {
                    currentCardId = document.getElementsByClassName('playerUsedCards')[i].getAttribute("value");
                    document.getElementsByClassName('playerUsedCards')[currentCardId].classList.add("cardSelected");
                }
            };
        }
        for(let i = 0; i < players[0].cards.length; i++) {
            let card = `<div class="player_handCard" value=`+i+` data-tilt data-tilt-reverse="true" data-tilt-max="20" data-tilt-scale="1.3">
            <img src="/assets/images/cards/`+players[0].cards[i].images+`">
            <label id="cardDefense">`+players[0].cards[i].defence+`</label>
            <span id="cardAttack">`+players[0].cards[i].attack+`</span>
            <span id="cardCost">`+players[0].cards[i].cost+`</span>
            </div>`;
            document.getElementsByClassName('player_handInner')[0].insertAdjacentHTML('beforeend',card);
            document.getElementsByClassName('player_handCard')[i].onclick = function () {
                if (socket !== undefined && socket.readyState === 1) {
                    let message = dropCard(document.getElementsByClassName('player_handCard')[i].getAttribute("value"));
                    console.log(message);
                    socket.send(message);
                    showMessage('Отправлено сообщение серверу: ' + message);
                } else {
                    showMessage('Невозможно отправить сообщение, нет соединения');
                }
            };
        }
        document.getElementById('player_currentStones').innerHTML = players[0].mana;
        document.getElementById('player_currentHealth').innerHTML = players[0].health;

        const script = document.createElement('script');
        script.src = '/assets/js/vanilla-tilt.js';
        document.head.append(script);
    }
});
