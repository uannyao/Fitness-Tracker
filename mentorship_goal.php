<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Goal & MentorSuggestion</title>
</head>
<body>

<?php
    require 'db_connect.php'; 
    require 'security.php';

    redirectIfNotLoggedIn();
   

    global $userInfo;
    $user_id = $_SESSION['userid']; // depends on session; fixed for now
    $userInfo = array("user_ID" => $user_id);
    $userInfo = getUserinformation($userInfo, $user_id, $db_conn);
    
    
    function getUserinformation($userInfo, $user_id, $db_conn) {
        #basic information
        $sql = 'SELECT userName, totalRewardPoints, partof_community FROM users WHERE userID = :user_ID';
        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        
        
        oci_define_by_name($stid, 'USERNAME', $userInfo['userName']);
        oci_define_by_name($stid, 'TOTALREWARDPOINTS', $userInfo['rewardPoints']);
        oci_define_by_name($stid, 'PARTOF_COMMUNITY', $userInfo['$community']);

        oci_execute($stid);
        oci_fetch($stid);
        oci_free_statement($stid);
        oci_close($db_conn);
        

        return $userInfo;
    }

    //Delete goal list
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('deleteGoal', $_POST)) {
        $selectedItems = $_POST['deleteGoal'];

        foreach ($selectedItems as $itemId) {
            $sql = "DELETE FROM GOAL WHERE GOALID = :goal_id";
            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":goal_id", $itemId);
            oci_execute_check($stid);
        }
        echo "<p>Deletion success.</p>";
        oci_free_statement($stid);

    }

    //Insert new goal
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('goal_activityType', $_POST)) {
        $activityID = $_POST['goal_activityType'];
        $description = $_POST['goal_description'];
        if (!sanitizeUserInputCheck($activityID) && !sanitizeUserInputCheck($description)) {
            $sql = "
            INSERT INTO goal (description, from_userID, activityID) VALUES (:description, :user_id, :activity_id)
            ";
            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":description", $description);
            oci_bind_by_name($stid, ":user_id", $user_id);
            oci_bind_by_name($stid, ":activity_id", $activityID);
            if (oci_execute_check($stid)) {
                echo 'Adding new goal successfully. Fighting! YOU CAN DO IT!!!';
            }
            oci_free_statement($stid);
        } else {
            echo "invalid input for security purpose!! ";
        }
    }



    ?>

    <h3>Goal list: </h3>
    <form action="mentorship_goal.php" method="POST" onsubmit="return confirm('Are you sure you want to delete?');">
        <table border='1'>
            <tr>
                <th>Select</th>
                <th>Activity Type Name</th>
                <th>Description</th>

            </tr>
     
        <?php
        $sql = "
        SELECT a.GOALID, a.DESCRIPTION, b.TYPENAME, b.CARDIOTYPE, b.TRAININGTYPE
        FROM GOAL a, ACTIVITYTYPE b
        WHERE a.FROM_USERID = :user_ID and a.ACTIVITYID = b.ACTIVITYID";

        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_execute($stid);

        while ($row = oci_fetch_assoc($stid)) {
            $activityType = '';
            if ($row['CARDIOTYPE']) {
                $activityType = "Cardio - " .htmlspecialchars($row['CARDIOTYPE']);
            } else {
                $activityType = "WeightTraining - " .htmlspecialchars($row['TRAININGTYPE']);
            };

            echo "<tr>";
            echo "<td><input type='checkbox' name='deleteGoal[]' id ='deleteGoal' value='" . htmlspecialchars($row['GOALID']) . "'></td>";
            echo "<td>" . $activityType . "</td>";
            echo "<td>" . htmlspecialchars($row['DESCRIPTION']) . "</td>";
            echo "</tr>";
        }
        ?>
            </table>
            <input type="submit" value="Delete Goal">
        </form>


        <h3>Add new goal: </h3>
        <p>choose relavent activity types(if any): </p>
        <form action="mentorship_goal.php" method="POST">
        <?php
        $sql = "
        SELECT ACTIVITYID, TYPENAME, CARDIOTYPE, TRAININGTYPE
        FROM ACTIVITYTYPE";

        $stid = oci_parse($db_conn, $sql);
        oci_execute_check($stid);

        echo "<select name='goal_activityType' id='goal_activityType'>";
        while ($row = oci_fetch_assoc($stid)) {
            $activityType = '';
            if ($row['CARDIOTYPE']) {
                $activityType = "Cardio - " .htmlspecialchars($row['CARDIOTYPE']);
            } else {
                $activityType = "WeightTraining - " .htmlspecialchars($row['TRAININGTYPE']);
            };

            echo "<option value='" . htmlspecialchars($row['ACTIVITYID']) . "'>" . $activityType. "</option>";
        }
        ?>
         </select>

         <br /> <br />Description: <input type="text" name="goal_description"> <br /><br />

        <input type="submit" value="Submit goal">
        </form>

        <h3>Mentor's suggestion: </h3>
        <table border='1'>
            <tr>
                <th>Mentor Name</th>
                <th>Suggestion</th>
                <th>Datetime</th>
            </tr>
     
        <?php
        $sql = "
        SELECT a.name, b.description, b.datetime
        FROM MENTOR a, suggestion b
        WHERE b.for_userID = :user_ID and b.from_mentorID = a.mentorID";

        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":user_ID", $user_id);
        oci_execute_check($stid);

        while ($row = oci_fetch_assoc($stid)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['DESCRIPTION']) . "</td>";
            echo "<td>" . htmlspecialchars($row['DATETIME']) . "</td>";
            echo "</tr>";
        }
        oci_free_statement($stid);
        ?>
            </table>

    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="personalProfile.php">Profile</a></li>
            <li><a href="activity-type.php">Enter your activity event! </a></li>
	        <li><a href="redeem-rewards.php">Redeem rewards!</a></li>
            <li><a href="workshop.php">Workshop</a></li>
            <li><a href="mentorship_goal.php">Goal setting & mentor suggestion</a></li>
            <li><a href="community.php">Community management</a></li>
            <li><a href="mentor/mentor.php">Log in as mentor</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>


</body>
</html>
