<?php require 'db_connect.php' ?>
<?php require 'security.php' ?>


<html>
    <head>
        <title>Add User Activity and Calculate Points</title>
    </head>

    <body>
        <h2>Add User Activity and Calculate Points</h2>
        <?php
            redirectIfNotLoggedIn();
        ?>
        <form method="POST" action="activity-type.php">
            <br>

            <label for="activityType">Choose an activity type: *</label><br>

            <select name="activityType" id="activityType">
            <?php
                $workshop_sql = "
                SELECT activityID, cardioType, trainingType
                FROM activityType";
                $stid = oci_parse($db_conn, $workshop_sql);
                oci_execute($stid);

                while ($row = oci_fetch_assoc($stid)) {
                    if ($row['CARDIOTYPE'] != NULL) {
                        echo "<option value='" . htmlspecialchars($row['ACTIVITYID']) . "'>" . htmlspecialchars($row['CARDIOTYPE']) . "</td>";
                    } else {
                        echo "<option value='" . htmlspecialchars($row['ACTIVITYID']) . "'>" . htmlspecialchars($row['TRAININGTYPE']) . "</td>";
                    }       
                }

                oci_free_statement($stid);
             ?>
            </select><br><br>

            <label for="datetime">Date and Time Activity Started: *</label><br>
            <input type="datetime-local" name="datetime"> <br /><br />
            
            <label for="duration">Duration (in minutes): *</label><br>
            <input type="number" name="duration" min="0" max="1440"> <br /><br />


            <label for="description">Description:</label><br>
            <textarea id="description" name="description" rows="4" cols="50">This is the description for my activity.</textarea><br /><br />

            <input type="hidden" id="attemptChange" name="attemptChange">
            <input type="submit" value="Add Activity">

            <br><br>
            

        </form>
	    
	<button onclick="window.location.href='home.php'">Return to Home</button> <br>
        <h3> </h3>
        
	</body>

    <?php
    session_start();
    $activityType = $_POST['activityType'];
    $datetime = $_POST['datetime'];
    $datetime = str_replace("T"," ", $datetime);
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    $rewardID = 0;

    if (isset($_POST['attemptChange'])) {
        if (($_POST['activityType'] == NULL) || ($_POST['datetime'] == NULL) || ($_POST['duration'] == NULL)) {
            echo "<br>Please fill out all required fields.";
        } else if (is_int($duration)) {
            echo "<br>The duration must be a number.";
        } else if (sanitizeUserInputCheck($duration) || sanitizeUserInputCheck($description)) {
            echo "Code injection attempt detected. Please try again.";
        } else {
            session_start();
            //$id = 1;
            $id = $_SESSION['userid'];
    
            addActivity();
        }
    }


    unset($_POST['activityType']);
    unset($_POST['datetime']);
    unset($_POST['duration']);
    unset($_POST['description']);


    function calculatePoints() {

        global $db_conn;
        global $activityType;
        global $duration;


        $sql = "SELECT rewardRate FROM activityType WHERE activityID = {$activityType}";

        $stid = oci_parse($db_conn, $sql);

        if (oci_execute($stid)) {
            oci_fetch($stid);
            $rewardRate = oci_result($stid, 'REWARDRATE');
        }
        oci_free_statement($stid);

        return $duration * $rewardRate;

        
    }

    function addActivity() {

        global $db_conn;
        global $datetime;
        global $activityType;
        global $duration;
        global $description;
        global $rewardID;
        global $id;

        $points = calculatePoints();

        //----------------------------------------------------------------------------------------------//
        $sql = "INSERT INTO reward (rewardPoint, for_userID) VALUES ('{$points}', '{$id}')";
        $stid = oci_parse($db_conn, $sql);
        
        if (oci_execute($stid) == FALSE) {
            echo "ERROR!!!!!!";
            return;
        }      
        oci_free_statement($stid);

        //----------------------------------------------------------------------------------------------//
        $sql = "SELECT MAX(rewardID) FROM reward WHERE for_userID = {$id}";

        $stid = oci_parse($db_conn, $sql);

        oci_define_by_name($stid, 'MAX(REWARDID)', $max);

        oci_execute($stid);
        
        while (oci_fetch($stid)) {
            $rewardID = $max;
        }

        oci_free_statement($stid);
        //----------------------------------------------------------------------------------------------//
        $sql = "INSERT INTO activityEvent (datetime, description, duration, userID, activityID, get_rewardID)
         VALUES ((SELECT TO_TIMESTAMP ('{$datetime}', 'YYYY-MM-DD HH24:MI') FROM DUAL),'{$description}', NUMTODSINTERVAL({$duration}, 'MINUTE'),
          {$id},  {$activityType}, {$rewardID})";

        //echo "<br>$sql";  

        $stid = oci_parse($db_conn, $sql);
        
        if (oci_execute($stid) == FALSE) {
            echo "Activity Addition Failed: Two activities cannot have the same datetime.";
            oci_free_statement($stid);
            $sql = "DELETE FROM reward WHERE rewardID = $rewardID";
            $stid = oci_parse($db_conn, $sql);
            oci_execute($stid);
            return;
        }

        oci_free_statement($stid);
        
        
        echo "<br>Addition Successful!";

    }

    ?>
</html>