<?php 
    session_start();
    session_unset();
    if(!session_destroy()) {
        echo "Log out failed.";
        header('Location: home.php');
        
    } else {
        echo "Logged out successfully";
        header('Location: login.php');
    }
?>

<html lang="en">
    
    <body>
        <h2>Log Out</h2>
        <br>
        <br>
        <button onclick="window.location.href='login.php'">Return To Login</button>
    </body>
</html>