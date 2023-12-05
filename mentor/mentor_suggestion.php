
<?php
    session_start();

    if (!isset($_SESSION['mentorName'])) {
        header("Location: mentor.php");
        exit();
    }

    require '../db_connect.php'; 
    require '../security.php';

    global $mentorName;
    $mentorName = $_SESSION['mentorName'];
    global $user_id;
    $user_id = $_SESSION['user_id'];


    $sql = "
    select DATETIME, DESCRIPTION, DURATION, b.TYPENAME
    from ACTIVITYEVENT a, ACTIVITYTYPE b
    WHERE a.USERID = :user_ID and a.ACTIVITYID = b.ACTIVITYID";

    $stid = oci_parse($db_conn, $sql);
    oci_bind_by_name($stid, ":user_ID", $user_id);
    
    oci_execute_check($stid);


    echo "<h4>Selected User's recent activity Events: </h4>

    <table border='1'>
        <tr>
            <th>Datetime</th>
            <th>Description</th>
            <th>Duration</th>
            <th>Type Name</th>
        </tr>";

    while ($row = oci_fetch_assoc($stid)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['DATETIME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DESCRIPTION']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DURATION']) . "</td>";
        echo "<td>" . htmlspecialchars($row['TYPENAME']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

	?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mentor suggestion</title>
</head>
<body>


        <h3>Provide suggestion!  </h3>
    <form action="mentor_suggestion.php" method="POST">
        Suggestion: <input type="text" name="suggestion">
        <input type="submit" value="Submit suggestion">
    </form>

    <?php


    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('suggestion', $_POST)) {
        $suggestion = $_POST['suggestion']; 
        if (!sanitizeUserInputCheck($suggestion)){
            $sql = "
            INSERT INTO suggestion (from_mentorID, for_userID, datetime, description)
            VALUES (:mentor_id, :user_id, TIMESTAMP '" . date("Y-m-d H:i:s") . "', :suggestion)";

            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":mentor_id", $_SESSION['mentorID']);
            oci_bind_by_name($stid, ":user_id", $_SESSION['user_id']);
            oci_bind_by_name($stid, ":suggestion", $suggestion);

            oci_execute_check($stid);
            oci_free_statement($stid);
            
            echo "Suggestion has already been sent to user! ";
        }


    }
    ?>

    <ul>
            <li><a href="mentor.php">Log out</a></li>
            <li><a href="mentor_admin.php">admin page</a></li>
            <li><a href="mentor_page.php">home page</a></li>
            <li><a href="../login.php">User page</a></li>
            
    </ul>

</body>
</html>