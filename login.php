<?php require 'db_connect.php' ?>
<?php require 'security.php' ?>

<!DOCTYPE html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>



<html lang="en">
    
    <body>
        <h2>Login</h2>
        
        <form method="POST" action="login.php">
            <label for="username">Username:</label><br>
            <input type="text" name="userEntry"><br /><br />
            <label for="password">Password:</label><br>
            <input type="password" name="pwEntry"><br /><br />

            <input type="hidden" id="attemptLogin" name="attemptLogin">
            <input type="submit" value="Submit">
        </form>
        
        <br>
        <h3>Don't have an account?</h3>
        <button onclick="window.location.href='register.php'">Register</button>
        
        <?php
            
            function attemptLogin() {
                
                global $config;
                global $db_conn;
                $usernameExists = FALSE;
        
                if ($db_conn) {
                    debugAlertMessage("Database is Connected");
                    echo "Connection Successful<br>";

                } else {
                    debugAlertMessage("Cannot connect to Database");
                    $e = OCI_Error(); // For oci_connect errors pass no handle
                    echo htmlentities($e['message']);
                    return;
                }
                
                $uname = $_POST['userEntry'];
		        $pw   = $_POST['pwEntry'];
                $sql_cmd = "SELECT userID, userName, password FROM users WHERE userName = '{$uname}'";

                $stid = oci_parse($db_conn, $sql_cmd);
                if(!$stid) {
                    echo "An error occurred while parsing the sql string.\n"; 
                    exit; 
                }
                
                
                oci_execute($stid);


                while (oci_fetch($stid)) {
                    $usernameExists = TRUE;
                    if ($pw == oci_result($stid, 'PASSWORD')) {
                        session_start();
                        
                        $_SESSION['userid'] = oci_result($stid, 'USERID');
                        $_SESSION['userName'] = oci_result($stid, 'USERNAME');
                        $_SESSION['logged_in'] = TRUE;

                        $_POST['userEntry'] = NULL;
                        $_POST['pwEntry'] = NULL;
                        header('Location: home.php');
                        exit();
                    }
                }

                oci_free_statement($stid);

                if ($_POST['userEntry'] == "" || $_POST['pwEntry'] == "") {
                    echo "Login failed: Please fill out all fields.";
                } else if (!$usernameExists) {
                    echo "Login failed: Username doesn't exist.";
                } else {
                    echo "Login failed: Password is incorrect.";
                }
                
            }

            session_start();
            redirectIfLoggedIn();

            if ((isset($_POST['userEntry'])) && (isset($_POST['pwEntry']))) {
                if (sanitizeUserInputCheck($_POST['userEntry']) || sanitizeUserInputCheck($_POST['pwEntry'])) {
                    echo "<br> Login failed: Code injection attempt detected.";
                } else {
                    attemptLogin();
                }

            } else {

                if (isset($_POST['attemptLogin'])) {
                    echo "<br> Login failed: Please fill out all fields.";
                }
            }
            
            unset($_POST['userEntry']);
            unset($_POST['pwEntry']);


            // if ($_POST['loginSuccessful']) {
            //     echo "Registration successful!";
            //     $_POST['loginSuccessful'] = FALSE;
            //     unset($_POST['loginSuccessful']);
            // }
        ?>
    
    
    </body>
</html>
