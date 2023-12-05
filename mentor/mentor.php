<?php
session_start(); 
require '../db_connect.php'; 
require '../security.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mentorName = $_POST['mentorName'];
    if (!sanitizeUserInputCheck($mentorName)) {
        if (!empty($mentorName)) {
            $sql = "
            SELECT * FROM MENTOR WHERE NAME = :mentorName";
            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":mentorName", $mentorName);
            oci_define_by_name($stid, 'MENTORID', $mentorID);

            oci_execute_check($stid);
            $nrows = oci_fetch_all($stid, $res);

            if ($nrows == 1) {
                $_SESSION['mentorName'] = $mentorName;
                $_SESSION['mentorID'] = $mentorID;
                header("Location: mentor_page.php");
            } else {
                echo "Sorry, we couldn't find your username. ";
            }
        } else {
            echo "Mentor name is required.";
        }
    }else {
        echo "Invalid input for security purpose! ";
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mentor Login</title>
</head>
<body>

<h2>Mentor Login</h2>

<form action="mentor.php" method="POST">
    <label for="mentorName">Mentor Name:</label>
    <input type="text" id="mentorName" name="mentorName" required>
    <input type="submit" value="Login">
</form>

<nav>
        <ul>
            <li><a href="../login.php">Log in as user</a></li>
        </ul>
</nav>

</body>
</html>
