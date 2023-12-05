<?php require 'db_connect.php' ?>
<?php require 'security.php' ?>

<html>
    <head>
        <title>Change Username</title>
    </head>

    <body>
        <h2>Change Username</h2>
        <?php
		
	    session_start();
	    redirectIfNotLoggedIn();
            $uname = $_POST['newUsername'];
            $pw   = $_POST['pwEntry'];

            $unfilledFields = false;
            $usernameSuccessChange = false;
            $usernameAlreadyExists = false;
            $passwordIncorrect = false;
            $didNotPassSecurityCheck = false;
        

            if (sanitizeUserInputCheck($uname) || sanitizeUserInputCheck($pw)) {
                $didNotPassSecurityCheck = true;
            } else {
                if (array_key_exists('attemptChange', $_POST)) {
                    if ((!($currPW == "")) && (!($newPW == "")) && (!($confNewPW == ""))) {
                        attemptUpdateUsername();
                    } else {
                        $unfilledFields = true;
                    }
                }
            }


            echo getUsername();
        ?>
        <form method="POST" action="change-username.php">
            <br>
            <label for="newUsername">New Username:</label><br>
            <input type="text" name="newUsername"> <br /><br />
            
            <label for="password">Password:</label><br>
            <input type="password" name="pwEntry"> <br /><br />
            <input type="hidden" id="attemptChange" name="attemptChange">
            <input type="submit" value="Change">
        </form>
        <h3> </h3>
        <br>
        <button onclick="window.location.href='account-menu-settings.php'">Return to Change Account Settings</button> <br /><br />
        <br>
        <button onclick="window.location.href='home.php'">Return to Home</button> <br>
        
	</body>

    <?php


        

        function getUsername() {
            
            global $db_conn;
            $sql_cmd = "SELECT username FROM users WHERE userID = '{$_SESSION['userid']}'";
            //$sql_cmd = "SELECT username FROM users WHERE userID = 1";
            $stid = oci_parse($db_conn, $sql_cmd);
            oci_execute($stid);

            oci_fetch($stid);
            $currentUName = oci_result($stid, 'USERNAME');
            return "Current Username: {$currentUName}";

        }

        function isPasswordCorrect() {
            global $pw;
            global $db_conn;
            //$db_conn = oci_connect("ora_massad", "a54398540", "dbhost.students.cs.ubc.ca:1522/stu");
            $sql_cmd = "SELECT password SET users WHERE userID = '{$_SESSION['userid']}'";
            //$sql_cmd = "SELECT password FROM users WHERE userid = 1";
            $stid = oci_parse($db_conn, $sql_cmd);
            oci_execute($stid);

            while (oci_fetch($stid)) {
                
                if ($pw == oci_result($stid, 'PASSWORD')) {

                    $passwordIncorrect = true;
                    oci_free_statement($stid);
                    return true;
                }
            }

            oci_free_statement($stid);
            return false;
        }

        function doesUsernameExist() {

            global $uname;
            global $db_conn;
            global $usernameAlreadyExists;

            //$db_conn = oci_connect("ora_massad", "a54398540", "dbhost.students.cs.ubc.ca:1522/stu");
            $sql_cmd = "SELECT userName, password FROM users";
            $stid = oci_parse($db_conn, $sql_cmd);

            oci_execute($stid);

            while (oci_fetch($stid)) {
                
                if ($uname == oci_result($stid, 'USERNAME')) {

                    $usernameAlreadyExists = true;
                    oci_free_statement($stid);
                    return true;
                }
            }
            oci_free_statement($stid);
            return false;
        }

        function attemptUpdateUsername() {

            global $uname;
            global $pw;

            global $unfilledFields;
            global $usernameSuccessChange;
            global $usernameAlreadyExists;
            global $passwordIncorrect;
            global $db_conn;

            if (!isPasswordCorrect()) {
                $passwordIncorrect = true;
                return;
            }

            if (doesUsernameExist()) {
                return;
            }

            $sql_cmd = "UPDATE users SET username = '{$uname}' WHERE userID = '{$_SESSION['userid']}'";
            $stid = oci_parse($db_conn, $sql_cmd);

            if (oci_execute($stid)) {
                $usernameSuccessChange = true;
            }
            oci_free_statement($stid);
        }

        function provideUserFeedback() {

            global $unfilledFields;
            global $usernameSuccessChange;
            global $usernameAlreadyExists;
            global $passwordIncorrect;
            global $didNotPassSecurityCheck;
            

            if ($didNotPassSecurityCheck) {
                echo "<br> Username Change Failed: Code injection attempt detected.";
            } else if ($unfilledFields) {
                echo "<br> Username Change Failed: Please fill out all fields.";
            } else if ($passwordIncorrect) {
                echo "<br> Username Change Failed: Password is incorrect.";
            } else if ($usernameAlreadyExists) {
                echo "<br> Username Change Failed: Username already exists.";
            } else if ($usernameSuccessChange) {
                echo "<br> Username has been successfully changed.";
            }

        }

        function resetAllValues() {
            global $unfilledFields;
            global $usernameSuccessChange;
            global $usernameAlreadyExists;
            global $passwordIncorrect;
            global $uname;
            global $pw;
            global $db_conn;

            $unfilledFields = false;
            $usernameSuccessChange = false;
            $usernameAlreadyExists = false;
            $passwordIncorrect = false;
            $pw = NULL;
            $uname = NULL;
            $db_conn = NULL;

            $_POST['newUsername'] = NULL;
            $_POST['pwEntry'] = NULL;
            $_POST['attemptChange'] = NULL;

        }

        provideUserFeedback();
        resetAllValues();
    
    ?>
</html>
