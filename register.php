<?php require 'db_connect.php' ?>
<?php require 'security.php' ?>

<html>
    <head>
        <title>Register</title>
    </head>

    <body>
        <h2>Register</h2>
        
        <form method="POST" action="register.php">
            <label for="username">Username: *</label><br>
            <input type="text" name="username"> <br /><br />
            
            <!-- <label for="email">Email:</label><br>
            <input type="text" name="email"> <br /><br /> -->
            
            <!-- <label for="confirmEmail">Confirm Email:</label><br>
            <input type="text" name="confirmEmail"> <br /><br /> -->
            
            <label for="phoneNumber">PhoneNumber:</label><br>
            <input type="number" id="phoneNumber" name="phoneNumber" min="1000000000" max="9999999999"> <br /><br />
            
            <label for="address">Address:</label><br>
            <input type="text" name="address"> <br /><br />

            <label for="postalCode">Postal Code:</label><br>
            <input type="text" name="postalCode"> <br /><br />
            
            <label for="password">Password: *</label><br>
            <input type="password" name="pwEntry"> <br /><br />
            
            <label for="password">Confirm Password: *</label><br>
            <input type="password" name="confirmPwEntry"> <br /><br />

            <input type="hidden" id="attemptRegister" name="attemptRegister">

            
            <input type="submit" value="Submit">
        </form>
        <h3> </h3>
        <h3>Already have an account?</h3>
        <button onclick="window.location.href='login.php'">Login</button>
        
	</body>

    <?php

     $uname = $_POST['username'];
     $phoneNumber = $_POST['phoneNumber'];
     $address = $_POST['address'];
     $pc = $_POST['postalCode'];
     $pw   = $_POST['pwEntry'];
     $cpw = $_POST['confirmPwEntry'];


    function attemptRegister() {

        global $uname;
        global $phoneNumber;
        global $address;
        global $pc;
        global $pw;
        global $cpw; 
        global $db_conn;

        $sql_cmd = "SELECT userName FROM users";

        $stid = oci_parse($db_conn, $sql_cmd);

        oci_execute($stid);

        while (oci_fetch($stid)) {
            if ($uname == oci_result($stid, 'USERNAME')) {
                echo "<br> Username already exists.";
                return;
            }
        }

        oci_free_statement($stid);

        if ($pw == $cpw) {

            $stid = NULL;
            $sql_cmd = "INSERT INTO users(userName, password, phoneNumber, address, postalCode) VALUES ('{$uname}', '{$pw}', '{$phoneNumber}', '{$address}','{$pc}')";
            $stid = oci_parse($db_conn, $sql_cmd);
            oci_execute($stid);
            //$_POST['loginSuccessful'] = TRUE;
            echo "<br>Account registration success!";
	    oci_free_statement($stid);
            return;
	
        } else {
            echo "<br>Registration failed: Passwords are not identical.";
            return;
        }

        oci_free_statement($stid);
    }

    if ((!($uname == "")) && (!($pw == "")) && (!($cpw == ""))) {
        if (sanitizeUserInputCheck($uname) || sanitizeUserInputCheck($pw) || sanitizeUserInputCheck($cpw) ||
        sanitizeUserInputCheck($address) || sanitizeUserInputCheck($pc) || sanitizeUserInputCheck($phoneNumber)) {
            echo "<br>Registration failed: Code injection attempt detected.";
        } else {
            attemptRegister();
        }
    } else {
        if (isset($_POST['attemptRegister'])) {
            echo "<br>Registration failed: Please fill out all required fields.";
        }
    }
       
    ?>
</html>
