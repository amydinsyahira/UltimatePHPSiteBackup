<h1>Welcome!</h1>
<center>
    <form action="do.php?login=true{if isset($goAfterLogin)}&goto={$goAfterLogin}{/if}" method="POST">
        <input type="password" class="textinput" name="train" value="" />
        <input type="submit" value="Login" name="submit" />
    </form>
</center>