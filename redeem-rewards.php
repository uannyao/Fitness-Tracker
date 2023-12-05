<?php
     require 'db_connect.php'; 
     require 'security.php';


    // session_start();
    // if (!isset($_SESSION['userid'])){
    //     header('Location: login.php');
    // }

    redirectIfNotLoggedIn();
    //redirectIfNotLoggedIn();
    
 
    global $userInfo;
    $user_id = $_SESSION['userid']; // depends on session; fixed for now // depends on session; fixed for now
    //$user_id = 1;



    // Handle cancel registration request
    //echo "REACHED!!!!!";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('reward', $_POST)) {
        //echo "REACHED!!!!!";
        $selectedItems = $_POST['reward'];

        $gainedPoints = 0;

        foreach ($selectedItems as $itemId) {
            $sql = "
            SELECT rewardPoint
            FROM reward r
            WHERE r.rewardID = {$itemId}";
            //echo "<br>$sql";
            $stid = oci_parse($db_conn, $sql);
            //oci_bind_by_name($stid, ":rewardID", $itemId);
            oci_execute($stid);

            while ($row = oci_fetch_assoc($stid)) {
                $gainedPoints = $gainedPoints + $row['REWARDPOINT'];
            }
        }

        $sql = "SELECT totalRewardPoints FROM users WHERE userID = {$user_id}";
        $stid = oci_parse($db_conn, $sql);
        //oci_bind_by_name($stid, ":user_ID", $user_id);
        //oci_define_by_name($stid, 'TOTALREWARDPOINTS', $remaining);
        oci_execute($stid);
        oci_fetch($stid);
        $remaining = oci_result($stid, 'TOTALREWARDPOINTS');
        oci_free_statement($stid);

        $updated_points = $gainedPoints + $remaining;

        $update_sql = "
        UPDATE users SET TOTALREWARDPOINTS = {$updated_points} WHERE USERID = {$user_id}
        ";
        $stid = oci_parse($db_conn, $update_sql);
        //oci_bind_by_name($stid, ":user_id", $user_id);
        oci_execute($stid);


        foreach ($selectedItems as $itemId) {
  
            $cancel_sql = "
            DELETE FROM REWARD WHERE REWARDID = {$itemId} and FOR_USERID = {$user_id}
            ";
            //echo "<br>$cancel_sql";
                $stid = oci_parse($db_conn, $cancel_sql);
                // oci_bind_by_name($stid, ":user_id", $user_id);
                // oci_bind_by_name($stid, ":workshopID", $itemId);
                oci_execute($stid);

        }
        oci_free_statement($stid);
        

        echo "<p>Redeem rewards success!</p>";
    
    }




	?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshop</title>
</head>
<body>

        <h3>Rewards to redeem: </h3>
<form action="redeem-rewards.php" method="POST" onsubmit="return confirm('Are you sure you want to redeem these rewards?');">
    <table border="1">
    <tr>
        <th>Redeem?</th>
        <th>Reward ID</th>
        <th>Reward Points</th>
    </tr>
    <?php
        $sql = "
        SELECT rewardID, rewardPoint
        FROM reward
        WHERE for_userID = {$user_id}";
        $stid = oci_parse($db_conn, $sql);
        //oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_execute($stid);


        while ($row = oci_fetch_assoc($stid)) {
            echo "<tr>";
            echo "<td><input type='checkbox' name='reward[]' id='reward' value='" . htmlspecialchars($row['REWARDID']) . "'></td>";
            echo "<td>" . htmlspecialchars($row['REWARDID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REWARDPOINT']) . "</td>";
            echo "</tr>";
        }
        oci_free_statement($stid);
        

        ?>
    </table>
    <br>
    <input type="submit" value="Submit Selection"> 
</form>


        <h4>Current rewardPoints: 
            <?php 
                $sql = "SELECT totalRewardPoints FROM users WHERE userID = {$user_id}";
                $stid = oci_parse($db_conn, $sql);
                //oci_bind_by_name($stid, ":user_ID", $user_id);
                //oci_define_by_name($stid, 'TOTALREWARDPOINTS', $remaining);
                oci_execute($stid);
                oci_fetch($stid);
                $remaining = oci_result($stid, 'TOTALREWARDPOINTS');
                oci_free_statement($stid);
                echo $remaining; ?></h4>

        

    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="personalProfile.php">Profile</a></li>
            <li><a href="activity-type.php">Enter your activity event! </a></li>
	        <li><a href="redeem-rewards.php">Redeem rewards!</a></li>
            <li><a href="workshop.php">Workshop</a></li>
            <li><a href="mentorship_goal.php">Goal setting & mentor suggestion</a></li>
            <li><a href="community.php">Community management</a></li>
            <li><a href="account-menu-settings.php">Account Settings</a></li>
            <li><a href="mentor/mentor.php">Log in as mentor</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    
</body>
</html>

