<h1 align="center">Marvel Heroes of the Earth-19999</h1>
<p align="center">The last and biggest project of UCODE Academy Half Marathon Web!<br>
The main goal of this challenge was to create a card game similar to <b>Hearthstone</b> but in <b>Marvel</b> universe!</p>
<h2>:card_index_dividers:Architecture</h2>
<img width="200px" align="left" src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/MVC-Process.svg/800px-MVC-Process.svg.png">
<p>This application works only because of <code>PHP</code>. Seriously, there is literally no .html files there. <br>
The client application runs on <code>Model–view–controller (MVC) pattern</code>, which, I think, is really comfortable to work with. The client receives user interactions and sends data to the server via <code>WebSocket protocol</code>. The server, which is also written with PHP, works with data and sends back the result of user actions, this could be a card drop or a victory.</p>
<br><br><br>
<h2>:joystick:Getting started</h2>
<p><i>If you reeeally want to play this game with somebody, you can ask <a href="https://web.telegram.org/#/im?p=@paxanddos">me</a> to host a game! I always have source files and hosting prepared and maybe I would play with you :wink:</i><br>Next steps will show you how to start a game on your own, you gotta make sure you have PHP 7.0+ installed.<br></p>
<h3>Starting the server</h3>
<p>To start a server first thing that you need to do is go to the <code>server</code> directory and open the <code>Server.php</code> file. Check line 7 if your address is correct: <code>$sock = new SocketServer('localhost', 5656, 20);</code>. You can use your own IP address, <code>0.0.0.0</code> and <code>localhost</code> refers to your computer that you are using right now.<br>Then just simply open the Terminal/Command line, go again to the <code>server</code> directory, and type <code>php -f Sever.php</code>. Now you have a working server!</p>
<h3>Starting the application</h3>
<p>That's also an easy one. But again you need to be sure that client connects to YOUR sever. Go to the <code>assets/js</code> directory and open <code>socket.js</code> file. You need to check out line 13 which should be: <code>socket = new WebSocket("ws://localhost:5656");</code>. Then open the Terminal/Command line and go to root directory, where <code>index.php</code> file is located and type: <code>php -S localhost:3000</code>. That will start the client!</p>
<p><b>Now you can open your browser and type <code>localhost:3000</code> in the address line and play! Just one note here: your account and progress won't be saved until the database is started.</b></p>
<h3>Starting the database</h3>
<p>If you are a pro and know how to work with databases then you may start the database! For users that are gonna play this game a few times and delete I recommend skipping this one, you may bring trash to your computer which will be unused databases and programs.<br>
You need to have MySQL installed. I use XAMPP to host both client and database. When your program is ready you need to execute db.sql file which is located at <code>application</code> folder. Once it's done you need to change <code>Model_database.php</code> which is located here: <code>application/models</code>. Change properties to where you database is located: <code>$this->connection = new DatabaseConnection('localhost', null, 'marvel', 'securepass', 'marvel_heroes');</code>, where first parameter is IP address, second - port, third - user, fourth - password, fifth - database name.</p>
<h2>:framed_picture:Screenshots</h2>
<i>Upcoming...</i>
<h2>:heavy_check_mark:Change log and downloads</h2>
<ul>
  <li><a href="https://github.com/PAXANDDOS/Heroes-of-the-Earth-199999/archive/refs/tags/1.0.zip">v1.0 (19.04.2021)</a> - New game logic. A lot of animations and visual stuff.</li>
  <li><a href="https://github.com/PAXANDDOS/Heroes-of-the-Earth-199999/releases/download/0.8/v0.8.zip">v0.8 (15.04.2021)</a> - Basic functions released, still no gameplay. Lazy design at the board. Lot of bugs.</li>
</ul>
