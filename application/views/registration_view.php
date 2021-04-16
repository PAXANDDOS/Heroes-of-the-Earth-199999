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
    <form id="regForm" method="post">
        <input style="--order: 1" type="text" name="username" id="username" placeholder="Username" pattern="^[A-Za-z0-9]+$" required>
        <input style="--order: 2" type="text" name="name" id="name" placeholder="Name" pattern="^[A-Za-zА-Яа-яЁё\s]+$" required>
        <input style="--order: 3" type="email" name="email" id="email" placeholder="Email" required>
        <span style="--order: 4" class="unselectable">Minimum eight characters, at least one letter and one number</span>
        <input style="--order: 4" type="password" name="password" id="password" placeholder="Password" pattern='^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$' tooltip=" sd" required>
        <input style="--order: 5" type="password" name="conpassword" id="conpassword" placeholder="Confirm passwordd" pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" required>
        <div class="submit" style="--order: 6"><input type="submit" name="reg" value="Join"></div>
    </form>
    <div class="other">
        <a style="--order: 7" class="unselectable" href="/login">Already have an account?</a>
    </div>
</div>