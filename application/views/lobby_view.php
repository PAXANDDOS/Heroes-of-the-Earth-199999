<style>@import url('/assets/css/lobby.css');</style>
<style>@import url('/assets/css/animations.css');</style>
<section class="window" style="--order:0">
    <h1 class="unselectable">Welcome to <br>Marvel <b>Heroes of the Earth-199999<b>!</h1>
    <div class="profile">
        <div class="avatarBox">
            <img src="/assets/images/avatars/<?php echo $avatar ?>.jpeg">
        </div>
        <div class="buttons unselectable">
            <form method="post">
                <input style="--order:1" name="battleButton" type="submit" id="battleButton" value='Battle!'>
                <input style="--order:2" name="avatarButton" type="submit" id="avatarButton" value='Change avatar'>
                <input style="--order:3" name="logoutButton" type="submit" id="logoutButton" value='Log out'>
            </form>
        </div>
    </div>
</section>
<section class="window stats" style="--order:2">
    <h1 class="unselectable"><b><?php echo $_SESSION['login']?></b>'s stats:</h1>
    <div class="content">
        <div class="headers unselectable">
            <label style="--order:1">Total games:</label>
            <label style="--order:2">Total wins:</label>
            <label style="--order:3">Total loses:</label>
        </div>
        <div class="data">
            <label style="--order:1"><?php echo $totalGames ?></label>
            <label style="--order:2"><?php echo $totalWins ?></label>
            <label style="--order:3"><?php echo $totalLoses ?></label>
        </div>
    </div>
</section>
