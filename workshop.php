
<?php
     require 'db_connect.php'; 
     require 'security.php';
 
     redirectIfNotLoggedIn();
    
 
    global $userInfo;
    $user_id = $_SESSION['userid']; // depends on session; fixed for now // depends on session; fixed for now



    // Handle cancel registration request
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('cancelRegistration', $_POST)) {
        $selectedItems = $_POST['cancelRegistration'];

        $cost = 0;

        foreach ($selectedItems as $itemId) {
            $sql = "SELECT c.COST, b.NAME
            FROM WORKSHOP b, COURSETYPE_COST c
            WHERE c.COURSETYPE = b.COURSETYPE AND b.WORKSHOPID = :workshopID";
            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":workshopID", $itemId);
            oci_execute_check($stid);

            while ($row = oci_fetch_assoc($stid)) {
                $cost = $cost + $row['COST'];
            }
        }

        $sql = 'SELECT totalRewardPoints FROM users WHERE userID = :user_ID';
        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_define_by_name($stid, 'TOTALREWARDPOINTS', $remaining);
        oci_execute_check($stid);
        oci_fetch($stid);
        oci_free_statement($stid);

        $updated_points = $cost + $remaining;

        $update_sql = "
        UPDATE users SET TOTALREWARDPOINTS = ". $updated_points . "WHERE USERID = :user_id
        ";
        $stid = oci_parse($db_conn, $update_sql);
        oci_bind_by_name($stid, ":user_id", $user_id);
        oci_execute_check($stid);


        foreach ($selectedItems as $itemId) {
  
            $cancel_sql = "
            DELETE FROM ATTENDING_WORKSHOP WHERE WORKSHOPID = :workshopID and FROM_USERID = :user_id
            ";
                $stid = oci_parse($db_conn, $cancel_sql);
                oci_bind_by_name($stid, ":user_id", $user_id);
                oci_bind_by_name($stid, ":workshopID", $itemId);
                oci_execute($stid);

        }
        oci_free_statement($stid);
        

        echo "<p>Cancel registration success.</p>";
    
    }




    //Handle registration request
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('registration', $_POST)) {
        $selectedItems = $_POST['registration'];
    
        echo "<h4>Registration status:</h4>";
        $cost = 0;

        foreach ($selectedItems as $itemId) {
            $sql = "
            SELECT c.COST, b.NAME
            FROM WORKSHOP b, COURSETYPE_COST c
            WHERE c.COURSETYPE = b.COURSETYPE AND b.WORKSHOPID = :workshopID";
            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":workshopID", $itemId);
            oci_execute($stid);

            while ($row = oci_fetch_assoc($stid)) {
                $cost = $cost + $row['COST'];
            }
        }

        echo "Total cost is ". $cost . " reward points.\n";
        
        $sql = 'SELECT totalRewardPoints FROM users WHERE userID = :user_ID';
        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_define_by_name($stid, 'TOTALREWARDPOINTS', $remaining);
        oci_execute_check($stid);
        oci_fetch($stid);
        oci_free_statement($stid);

        $rest = $remaining - $cost;

        if ($rest < 0) {
            echo "<p>Failure: insufficient reward points. You need more activities...:(</p>";
        } else {
            foreach ($selectedItems as $itemId) {
                $update_sql = "
                UPDATE users SET TOTALREWARDPOINTS = ". $rest . "WHERE USERID = :user_id
                ";
                $stid = oci_parse($db_conn, $update_sql);
                oci_bind_by_name($stid, ":user_id", $user_id);
                oci_execute_check($stid);

                oci_free_statement($stid);

                $insert_sql = "
                INSERT INTO attending_workshop(workshopID, from_userID)
                VALUES (:workshopID, :user_id)";
                $stid = oci_parse($db_conn, $insert_sql);
                oci_bind_by_name($stid, ":workshopID", $itemId);
                oci_bind_by_name($stid, ":user_id", $user_id);
                oci_execute_check($stid);

                oci_free_statement($stid);
            }

            
            echo "<p>Registration success.</p>";

        }
    }




	?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshop</title>
</head>
<body>

        <h3>Registered workshops: </h3>
        <form action="workshop.php" method="POST" onsubmit="return confirm('Are you sure you want to delete?');">
        <table border='1'>
            <tr>
                <th>Select</th>
                <th>workshop name</th>
                <th>courseType</th>
                <th>startTime</th>
                <th>EndTime Name</th>
                <th>MeetingLink</th>
            </tr>
     
        <?php
        $sql = "
        SELECT b.WORKSHOPID, b.NAME, b.COURSETYPE, b.STARTTIME, b.ENDTIME, b.MEETINGLINK
        FROM ATTENDING_WORKSHOP a, WORKSHOP b
        WHERE a.FROM_USERID = :user_ID and a.WORKSHOPID = b.WORKSHOPID";

        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_execute_check($stid);

        while ($row = oci_fetch_assoc($stid)) {
            echo "<tr>";
            echo "<td><input type='checkbox' name='cancelRegistration[]' id ='cancelRegistration' value='" . htmlspecialchars($row['WORKSHOPID']) . "'></td>";
            echo "<td>" . htmlspecialchars($row['NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['COURSETYPE']) . "</td>";
            echo "<td>" . htmlspecialchars($row['STARTTIME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ENDTIME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['MEETINGLINK']) . "</td>";
            echo "</tr>";
        }
        oci_free_statement($stid);
        
        ?>
            </table>
            <input type="submit" value="Cancel registration">
        </form>


        <h4>Current rewardPoints: 
            <?php 
                $sql = 'SELECT totalRewardPoints FROM users WHERE userID = :user_ID';
                $stid = oci_parse($db_conn, $sql);
                oci_bind_by_name($stid, ":user_ID", $user_id);
                oci_define_by_name($stid, 'TOTALREWARDPOINTS', $remaining);
                oci_execute_check($stid);
                oci_fetch($stid);
                oci_free_statement($stid);
                echo $remaining; ?></h4>



<h3>Available upcoming workshops:</h3>

<form action="workshop.php" method="POST" onsubmit="return confirm('Are you sure you want to register?');">
    <table border="1">
    <tr>
        <th>Select</th>
        <th>Name</th>
        <th>Mentor Name</th>
        <th>CourseType</th>
        <th>Start time</th>
        <th>End time</th>
        <th>Description</th>
        <th>Cost</th>
    </tr>
        <?php
        $sql = "
        SELECT b.WORKSHOPID, a.NAME AS mentorname, b.NAME as workshopName, b.HOST, b.COURSETYPE, c.COST, b.STARTTIME, b.ENDTIME, b.DESCRIPTION
        FROM MENTOR a, WORKSHOP b, COURSETYPE_COST c
        WHERE a.MENTORID = b.HOST AND
              c.COURSETYPE = b.COURSETYPE AND
              b.WORKSHOPID NOT IN (SELECT b.WORKSHOPID
                FROM ATTENDING_WORKSHOP a, WORKSHOP b
                WHERE a.FROM_USERID = :user_ID and a.WORKSHOPID = b.WORKSHOPID)";
        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_execute_check($stid);


        while ($row = oci_fetch_assoc($stid)) {
            echo "<tr>";
            echo "<td><input type='checkbox' name='registration[]' id='registration' value='" . htmlspecialchars($row['WORKSHOPID']) . "'></td>";
            echo "<td>" . htmlspecialchars($row['WORKSHOPNAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['MENTORNAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['COURSETYPE']) . "</td>";
            echo "<td>" . htmlspecialchars($row['STARTTIME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ENDTIME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['DESCRIPTION']) . "</td>";
            echo "<td>" . htmlspecialchars($row['COST']) . "</td>";
            echo "</tr>";
        }
        oci_free_statement($stid);
        

        ?>
    </table>
    <br>
    <input type="submit" value="Submit Selection">
</form>




        

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


