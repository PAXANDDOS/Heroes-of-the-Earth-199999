<style>@import url('/assets/css/auth.css');</style>
<style>@import url('/assets/css/animations.css');</style>
<?php if(isset($error)) echo('
    <div class="window message error unselectable">
        <label>'.$error.'</label>
    </div>
    ');
    if(isset($action)) echo('
    <div class="window message action unselectable">
        <label>'.$action.'</label>
    </div>
    ');
?>
<div class="window">
    <div class="logo">
        <img class="unselectable" src="/assets/images/logo.png">
    </div>
    <form id="remForm" method="post">
        <input style="--order: 1" type="email" name="email" id="email" placeholder="Your email" required>
        <div style="--order: 2" class="submit"><input type="submit" name="rem" value="Send new password"></div>
    </form>
    <div class="other">
        <a style="--order: 3"class="unselectable" href="/login">Back</a>
    </div>
</div>