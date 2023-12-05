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

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('selected_user', $_POST)) {
        $_SESSION['user_id'] = $_POST['selected_user'];
        header("Location: mentor_suggestion.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('createWorkshop', $_POST)) {
        $name = $_POST['workshopName'];
        $courseType = $_POST['courseType'];
        $date = $_POST['date'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        $description = $_POST['description'];
        $meetingLink = "https://meetinglink.com/".str_replace(" ", "", $name);

        if (!sanitizeUserInputCheck($name) && !sanitizeUserInputCheck($description)){
            $startTimestamp = date('Y-m-d H:i:s', strtotime("$date $startTime"));
            $endTimestamp = date('Y-m-d H:i:s', strtotime("$date $endTime"));

            $sql = "
            INSERT INTO workshop (host, description, meetingLink, courseType, name, datetime, startTime, endTime) 
            VALUES (:mentor_id, :description, :meetingLink, :courseType, :name, DATE '". $date."', TIMESTAMP '".$startTimestamp."', TIMESTAMP '".$endTimestamp."')
            ";

            $stid = oci_parse($db_conn, $sql);
            oci_bind_by_name($stid, ":mentor_id", $_SESSION['mentorID']);
            oci_bind_by_name($stid, ":courseType", $courseType);
            oci_bind_by_name($stid, ":name", $name);
            oci_bind_by_name($stid, ":description", $description);
            oci_bind_by_name($stid, ":meetingLink", $meetingLink);
            oci_bind_by_name($stid, ":datetime", $date);
            oci_bind_by_name($stid, ":startTime", $startTimestamp);
            oci_bind_by_name($stid, ":endTime", $endTimestamp);
       
            if (oci_execute_check($stid)) {
                echo "workshop has been successfully created. Here is the meeting link: ". $meetingLink;
            }
            oci_free_statement($stid);
        }
        else {
            echo "Sorry, invalid input for security purpose!";
        }  
    }   

	?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mentor</title>
</head>
<body>    

<h1>Welcome, <?php echo htmlspecialchars($mentorName); ?></h1>

<h3>Select a user to make suggestion: </h3>

<form action="mentor_page.php" method="POST">
        <?php
        $sql = "
        SELECT USERID, USERNAME
        FROM USERS";

        $stid = oci_parse($db_conn, $sql);
        oci_execute_check($stid);

        echo "<select name='selected_user' id='selected_user'>";
        while ($row = oci_fetch_assoc($stid)) {
            echo "<option value='" . htmlspecialchars($row['USERID']) . "'>" . htmlspecialchars($row['USERNAME']). "</option>";
        }
        oci_free_statement($stid);
        ?>
         </select>

        <input type="submit" value="Start Suggestion">
        </form>


<h3>Create new workshop: </h3>
<form method="POST" action="mentor_page.php"> 
            <input type="hidden" id="createWorkshop" name="createWorkshop">
            workshopName: <input type="text" name="workshopName"> <br /><br />

            courseType: 
            <?php
            $sql = "
            SELECT courseType
            FROM courseType_cost";

            $stid = oci_parse($db_conn, $sql);
            oci_execute_check($stid);

            echo "<select name='courseType' id='courseType'>";
            while ($row = oci_fetch_assoc($stid)) {
                echo "<option value='" . htmlspecialchars($row['COURSETYPE']) . "'>" . htmlspecialchars($row['COURSETYPE']). "</option>";
            }
            oci_free_statement($stid);
        ?>
            </select><br><br>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required><br><br>
            <label for="startTime">Start Time:</label>
            <input type="time" id="startTime" name="startTime" required>
            <label for="endTime">End Time:</label>
            <input type="time" id="endTime" name="endTime" required><br><br>
            description: <input type="text" name="description"> <br /><br />
            <input type="submit" value="Create" name="Create"></p>
        </form>






        <nav>
        <ul>
            <li><a href="mentor.php">Log out</a></li>
            <li><a href="mentor_admin.php">admin page</a></li>
            <li><a href="../login.php">User page</a></li>
        </ul>
</nav>

    </body>
</html>