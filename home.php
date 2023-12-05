<?php require 'db_connect.php' ?>
<?php require 'security.php' ?>
<?php
    redirectIfNotLoggedIn(); // Continue the session
    
    $user_id = $_SESSION['userid']; 

    #basic information
    $sql = "SELECT a.userName, a.totalRewardPoints FROM users a WHERE a.userID = :user_ID";
    $stid = oci_parse($db_conn, $sql);
    oci_bind_by_name($stid, ":user_ID", $user_id);
    oci_define_by_name($stid, 'USERNAME', $username);
    oci_define_by_name($stid, 'TOTALREWARDPOINTS', $rewardPoints);


    oci_execute_check($stid);
    oci_fetch($stid);

    oci_free_statement($stid);

    echo " <h1>Welcome, ".$username."</h1>";
    echo " <h4>Current rewardPoints: ".$rewardPoints."</h4>";



	?>
	

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>
<body>

        <?php     
        $sql = "
        select DATETIME, DESCRIPTION, DURATION, b.TYPENAME
        from ACTIVITYEVENT a, ACTIVITYTYPE b
        WHERE a.USERID = :user_ID and a.ACTIVITYID = b.ACTIVITYID";

        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        
        oci_execute($stid);


        echo "<h4>Your activity Events: </h4>

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
        oci_free_statement($stid);
        
        echo "<form method = 'POST' action = ''>";

        echo "<h4>Your activity summary: </h4>";
        echo "<input type ='submit' name ='calculate' value='Show'>";

        if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['calculate'])){
            $group_sql = "
            SELECT COUNT(*), b.TYPENAME
            FROM ACTIVITYEVENT a, ACTIVITYTYPE b
            WHERE a.USERID = :user_ID and a.ACTIVITYID = b.ACTIVITYID
            GROUP BY b.TYPENAME
            ";

            $stid = oci_parse($db_conn, $group_sql);
            oci_bind_by_name($stid, ":user_ID", $user_id);
            
            oci_execute_check($stid);

            while ($row = oci_fetch_assoc($stid)) {
                    echo "Your have performed ". htmlspecialchars($row['TYPENAME']). " ". htmlspecialchars($row['COUNT(*)'])." times!";
            }
        oci_free_statement($stid);
        }


        
        ?>

    


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


