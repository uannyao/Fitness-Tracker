<?php require 'db_connect.php' ?>
<?php require 'security.php' ?>


<html>
    <head>
        <title>Change Password</title>
    </head>

    <body>
        <h2>Change Password</h2>
        <?php
	    session_start();
	    redirectIfNotLoggedIn();

            $currPW = $_POST['currPassword'];
            $newPW = $_POST['newPassword'];
            $confNewPW = $_POST['confirmNewPassword'];

            $unfilledFields = false;
            $passwordSuccessChange = false;
            $passwordUnconfirmed = false;
            $passwordIncorrect = false;
            $didNotPassSecurityCheck = false;
            
            if (sanitizeUserInputCheck($currPW) || sanitizeUserInputCheck($newPW) || sanitizeUserInputCheck($confNewPW)) {
                $didNotPassSecurityCheck = true;
            } else {
                if (array_key_exists('attemptChange', $_POST)) {
                    if ((!($currPW == "")) && (!($newPW == "")) && (!($confNewPW == ""))) {
                        attemptChangePassword();
                    } else {
                        $unfilledFields = true;
                    }
                }
            }


        ?>
        <form method="POST" action="change-password.php">
            <br>
            <label for="currPassword">Current Password:</label><br>
            <input type="password" name="currPassword"> <br /><br />
            
            <label for="newPassword">New Password:</label><br>
            <input type="password" name="newPassword"> <br /><br />
            <label for="confirmNewPassword">Confirm New Password:</label><br>
            <input type="password" name="confirmNewPassword"> <br /><br />

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

        


        function isPasswordCorrect() {
            global $currPW;
            global $db_conn;
            $db_conn = oci_connect("ora_massad", "a54398540", "dbhost.students.cs.ubc.ca:1522/stu");
            $sql_cmd = "SELECT password SET users WHERE userID = '{$_SESSION['userid']}'";
            //$sql_cmd = "SELECT password FROM users WHERE userid = 1";
            $stid = oci_parse($db_conn, $sql_cmd);
            oci_execute($stid);

            while (oci_fetch($stid)) {
                
                if ($currPW == oci_result($stid, 'PASSWORD')) {

                    $passwordIncorrect = true;
                    oci_free_statement($stid);
                    return true;
                }
            }

            oci_free_statement($stid);
            return false;
        }

        function isNewPasswordConfirmed() {
            return ($_POST['newPassword'] == $_POST['confirmNewPassword']);
        }


        function attemptChangePassword() {


            global $unfilledFields;
            global $passwordSuccessChange;
            global $passwordUnconfirmed;
            global $passwordIncorrect;
            global $db_conn;
            global $newPW;

            if (!isPasswordCorrect()) {
                $passwordIncorrect = true;
                return;
            }

            if (!isNewPasswordConfirmed()) {
                $passwordUnconfirmed = true;
                return;
            }

            $sql_cmd = "UPDATE users SET password = '{$newPW}' WHERE userID = '{$_SESSION['userid']}'";
            $stid = oci_parse($db_conn, $sql_cmd);

            if (oci_execute($stid)) {
                $passwordSuccessChange = true;
            }
            oci_free_statement($stid);
        }

        function provideUserFeedback() {

            global $unfilledFields;
            global $passwordSuccessChange;
            global $passwordIncorrect;
            global $passwordUnconfirmed;
            global $didNotPassSecurityCheck;
            

            if ($didNotPassSecurityCheck) {
                echo "<br> Password change failed: Code injection attempt detected.";
            } else if ($unfilledFields) {
                echo "<br> Password change failed: Please fill out all fields.";
            } else if ($passwordIncorrect) {
                echo "<br> Password change failed: Password is incorrect.";
            } else if ($passwordUnconfirmed) {
                echo "<br> Password change failed: New password not same from confirm new password.";
            } else if ($passwordSuccessChange) {
                echo "<br> Password has been successfully changed.";
            }

        }

        function resetAllValues() {
            global $unfilledFields;
            global $passwordSuccessChange;
            global $passwordIncorrect;
            global $currPW;
            global $newPW;
            global $confNewPW;            
            global $db_conn;
            global $didNotPassSecurityCheck;

            $unfilledFields = false;
            $didNotPassSecurityCheck = false;
            $passwordSuccessChange = false;
            $passwordIncorrect = false;
            $passwordUnconfirmed = false;
            $currPW = NULL;
            $newPW = NULL;
            $confNewPW = NULL;
            $db_conn = NULL;

            unset($_POST['currPassword']);
            unset($_POST['newPassword']);;
            unset($_POST['confirmNewPassword']);

        }

        provideUserFeedback();
        resetAllValues();
    
    ?>
</html>
