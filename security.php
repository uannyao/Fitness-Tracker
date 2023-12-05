<?php

    // Checks to see if user input does not seem to be code injection

    //no smicolon; empty str
    function sanitizeUserInputCheck($str) {
        $pattern[0] = "/.*SELECT(\s)*(\s|\w)*(\s)*FROM.*/i";
        $pattern[1] = "/.*INSERT(\s)*INTO.*VALUES(\s)*\(.*\).*/i";
        $pattern[2] = "/.*UPDATE.*SET./i";
        $pattern[3] = "/.*DELETE.*FROM.*/i";
        $pattern[4] = "/.*CREATE(\s)*TABLE.*\(.*\).*/i";
        $pattern[5] = "/.*DROP(\s)*TABLE.*/i";
        $pattern[6] = "/.*ALTER(\s)*TABLE.*/i";
        $pattern[7] = "/.*CREATE(\s)*DATABASE.*/i";
        $pattern[8] = "/.*DROP(\s)*DATABASE.*/i";
        $pattern[9] = "/.*BACKUP(\s)*DATABASE.*/i";
	$pattern[10] = "/.*;.*/i";

        for ($i = 0; $i < 11; $i++) {
	        if (preg_match($pattern[$i], $str)) {
                return true;
            }
        }

        return false;
    }

    function redirectIfLoggedIn() {
        session_start();
        if (isset($_SESSION['userid'])){
            header('Location: home.php');
        }
    }

    
    function redirectIfNotLoggedIn() {
        session_start();
        if (!isset($_SESSION['userid'])){
            header('Location: login.php');
        }
    }

?>
