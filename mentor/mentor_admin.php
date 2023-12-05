<?php
    session_start();


    // if (!isset($_SESSION['mentorName'])) {
    //     header("Location: mentor.php");
    //     exit();
    // }
    require '../db_connect.php'; 
    require '../security.php'; 
?>
    
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>admin</title>
</head>
<body>

<h3>Select table to view: </h3>  




<form action="mentor_admin.php" method="POST">
        <?php
        $sql = "
        SELECT table_name
        FROM user_tables";

        $stid = oci_parse($db_conn, $sql);
        oci_execute_check($stid);


        echo "<select name='selected_table' id='selected_table'>";
        while ($row = oci_fetch_assoc($stid)) {
            echo "<option value='" . htmlspecialchars($row['TABLE_NAME']) . "'>" . htmlspecialchars($row['TABLE_NAME']). "</option>";
        }
        oci_free_statement($stid);
        

        ?>
        </select>

       <input type="submit" value="Select">
      
</form>


        <?php
        echo "<h3>Select multiple attributes to view: </h3> ";
        if (array_key_exists('selected_table', $_POST)) {
        echo $table;
        $table = $_POST['selected_table'];
        echo "<form action='mentor_admin.php' method='POST'>";
        $sql = "
        SELECT column_name
        FROM user_tab_columns
        WHERE table_name = '". $table ."'
        ";

        $stid = oci_parse($db_conn, $sql);
        oci_execute_check($stid);

        while ($row = oci_fetch_assoc($stid)) {            
            echo "<input type='checkbox' name='selectedAttributes[]' id='selectedAttributes' value='" . htmlspecialchars($row['COLUMN_NAME']) . "'>".$row['COLUMN_NAME']."</td>\n";
        }
        echo "<input type='hidden' name='selected_table' value='" . htmlspecialchars($table) . "'>";
        echo "\n<input type='submit' value='SelectAttributes'>";
        echo "</form>";
        oci_free_statement($stid);
        
    } else {
        echo "Please select table for viewing attributes. \n";
    }
    
        ?>


    <?php
        
    if (array_key_exists('selectedAttributes', $_POST)) {
        
        $selectedItems = $_POST['selectedAttributes'];
        $table = $_POST['selected_table'];
        echo "<h3>Selected attributes in ".$table." </h3> ";

        $count = 0;
        $length = sizeof($selectedItems);
        $statement = ' ';

        echo "<table border='1'>";
        echo "<tr>";

        foreach ($selectedItems as $item) {
            echo "<th>".$item."</th>";
            if ($count == $length-1) {
                $statement = $statement . $item;
            } else {
                $statement = $statement . $item . ", ";
            }
            $count = $count + 1;
        }
        echo "</tr>";

        $sql = "SELECT".$statement." FROM ".$table;


        $stid = oci_parse($db_conn, $sql);
        oci_execute_check($stid);

        while ($row = oci_fetch_assoc($stid)) {
            echo "<tr>\n";
            foreach ($selectedItems as $item) {
                echo "    <td>".(htmlentities($row[$item]))."</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>\n";
        oci_free_statement($stid);
        
        }

    ?>

<h3>Mentor management: </h3>

<form action="mentor_admin.php" method="POST">
        <?php
        $sql = "
        SELECT NAME, MENTORID
        FROM MENTOR";

        $stid = oci_parse($db_conn, $sql);
        oci_execute_check($stid);

        echo "<select name='selected_mentor' id='selected_mentor'>";
        while ($row = oci_fetch_assoc($stid)) {
            echo "<option value='" . htmlspecialchars($row['MENTORID']) . "'>" . htmlspecialchars($row['NAME']). "</option>\n";
        }
        oci_free_statement($stid);

        ?>
        </select>
        <br>
        <br>
        <label for="name">Change Name:</label>
        <input type="text" id="mentorName" name="mentorName"><br><br>
        <label for="description">Change description:</label>
        <input type="ttextime" id="description" name="description">
       <input type="submit" value="Submit change">
</form>


        <?php
        if (array_key_exists('mentorName', $_POST)) {
            $mentorID = $_POST['selected_mentor'];
            $changedName = $_POST['mentorName'];
            $changedDescription = $_POST['description'];

            if ($mentorID && ($changedName or $changedDescription)) {
                if (!sanitizeUserInputCheck($changedName) && !sanitizeUserInputCheck($changedDescription)){
                    $sql = "update MENTOR
                    set NAME = :mentorName, DESCRIPTION = :changedDescription
                    WHERE MENTORID = :mentorID";

                    $stid = oci_parse($db_conn, $sql);
                    oci_bind_by_name($stid, ':mentorName', $changedName);
                    oci_bind_by_name($stid, ':changedDescription', $changedDescription);
                    oci_bind_by_name($stid, ':mentorID', $mentorID);

                    if (oci_execute($stid)) {
                        echo "Profile updated successfully!";
                    } else {
                        $error = oci_error($stid);
                        echo "Error updating profile: Seems like there already a mentor with the same name!";
                    }
                    oci_free_statement($stid);
                } else {
                    echo "Invalid input for security purpose...::Try again! ";
                }
            } else {
                echo "Sorry, Submission failed. Try again! ";
            }
        }
        ?>


<h3>Trend analysis:</h3>
<form method = 'POST' action = ''>





<p>These are workshops that are taken by all users! </p>
<input type ='submit' name ='calculate' value='Show'>

        <?php
        if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['calculate'])){
            echo "<table border='1'>
            <tr>
                <th>Workshop Name</th>
                <th>Mentor Name</th>
            </tr>";
            $div_sql = "SELECT a.NAME as WORKSHOPNAME, c.NAME as MENTORNAME
            FROM WORKSHOP a, MENTOR c
            WHERE a.HOST = c.MENTORID and
                NOT EXISTS (SELECT USERID
                        FROM USERS
                        MINUS
                        SELECT b.FROM_USERID
                        FROM ATTENDING_WORKSHOP b
                            WHERE a.WORKSHOPID = b.WORKSHOPID)";
            $stid = oci_parse($db_conn, $div_sql);
            
            if (oci_execute($stid)) {
            while ($row = oci_fetch_assoc($stid)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['WORKSHOPNAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['MENTORNAME']) . "</td>";
                echo "</tr>";
            }
            }else {
                $error = oci_error($stid);
                echo "Error when generating table: " . $error['message'];
            }
            oci_free_statement($stid);
    }


        
        ?>

    </table>

    <?php echo "<h3>Finding new user:</h3>"; ?>

        <form method="POST" action="mentor-admin.php">
            <input type="hidden" id="seeCount" name="seeCount">
            <input type="submit" value="Show">
        </form>

<?php
    findNumNewerUsers();
    function findNumNewerUsers() {
        if (isset($_POST['seeCount'])) {
            global $db_conn;
            $num_sql = "SELECT COUNT(*) FROM users u1 WHERE u1.userID > ALL (SELECT AVG(u2.userID)/1.2 FROM users u2 GROUP BY u2.partof_community)";
            $num_stid = oci_parse($db_conn, $num_sql);
            oci_define_by_name($num_stid, 'COUNT(*)', $count);
            oci_execute($num_stid);
            oci_fetch($num_stid);
            echo "The number of newer users in the system are: $count<br>"; 
            unset($_POST['seeCount']);
        }
    }

?>


<nav>
        <ul>
            <li><a href="mentor.php">Log out</a></li>
            <li><a href="mentor_page.php">home page</a></li>
            <li><a href="../login.php">User page</a></li>
            
        </ul>
</nav>

</body>
</html>
