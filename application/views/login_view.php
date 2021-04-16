<style>@import url('/assets/css/auth.css');</style>
<style>@import url('/assets/css/animations.css');</style>
<?php if(isset($error)) 
    echo('
    <div class="window message error unselectable">
        <label>'.$error.'</label>
    </div>
    ');
?>
<div class="window">
    <div class="logo">
        <img class="unselectable" src="/assets/images/logo.png">
    </div>
    <form id="loginForm" method="post" style="--order: 1">
        <input style="--order: 2" type="text" name="username" id="username" placeholder="Username" pattern="^[A-Za-z0-9]+$" required>
        <input style="--order: 3" type="password" name="password" id="password" placeholder="Password" pattern='^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$' required>
        <div class="submit" style="--order: 4"><input type="submit" name="log" value="Log in"></div>
    </form>
    <div class="other">
        <a style="--order: 5" class="unselectable" href="/registration">Need an account?</a>
        <a style="--order: 6" class="unselectable" href="/reminder">Forgot password?</a>
    </div>
</div>