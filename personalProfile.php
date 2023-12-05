<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
</head>
<body>

<?php
require 'db_connect.php';
require 'security.php';
 
redirectIfNotLoggedIn();
 

$userID = $_SESSION['userid'];

$sql = "SELECT userName, phoneNumber, address, postalCode, totalRewardPoints, partof_community FROM users WHERE userID = :userID";
$result = oci_parse($db_conn, $sql);
oci_bind_by_name($result, ':userID', $userID);
oci_execute($result);

if (($row = oci_fetch_assoc($result)) != false) {
    ?>
    <h1>Personal Profile for:  <?php echo $row["USERNAME"]; ?></h1>
    <p><strong>User ID:</strong> <?php echo $userID; ?></p>

    <p><strong>Phone Number:</strong><?php if ($row["PHONENUMBER"]==null){echo " null";}else{echo " "; echo $row["PHONENUMBER"];} ?></p>
    
    <p><strong>Address:</strong><span><?php if ($row["ADDRESS"]==null){echo " null";}else{echo " "; echo $row["ADDRESS"];} ?></span> </p>
       
    <p><strong>Postal Code:</strong><span><?php if ($row["POSTALCODE"]==null){echo " null";}else{echo " "; echo $row["POSTALCODE"];} ?></span> </p>
       
    <p><strong>Total Reward Points:</strong> <?php echo $row["TOTALREWARDPOINTS"]; ?></p>
    
 <form method = "POST" action = "">
       <h4>Show Your Rank of Total Reward Points:<input type ="submit" name ="calculate" value="Show"></h4>
       <?php

       $totalRewardPoint = $row["TOTALREWARDPOINTS"];
       if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['calculate'])){
        $sql="SELECT COUNT(*) as result
        FROM users
        GROUP BY totalRewardPoints
        HAVING totalRewardPoints < :totalRewardPoint";
        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ':totalRewardPoint', $totalRewardPoint);
        oci_execute($statement);
        $resultRow = oci_fetch_assoc($statement);

        $total_user_sql = "SELECT COUNT(*) FROM users";
        $total_user_statement=oci_parse($db_conn, $total_user_sql);
        oci_execute($total_user_statement);
        $totalUser = oci_fetch_assoc($total_user_statement);
       

        if($resultRow['RESULT'] == 0){
            echo"<p>Unfortunately, there are no user below you with {$totalUser['COUNT(*)']} users in total, but never give up!</p>";
        }

        if($resultRow != null){
    
            echo"<p>Congratulation! You are above {$resultRow['RESULT']} users with {$totalUser['COUNT(*)']} users in total!</p>";
        }

        oci_free_statement($statement);

       }
       ?>

</form>

    <?php
    if ($row["PARTOF_COMMUNITY"] !== null) {
        echo "<p><strong>Community ID:</strong> " . $row["PARTOF_COMMUNITY"] . "</p>";
    } else {
        echo "<p><strong>Community ID:</strong> You currently are not a part of any community.</p>";
    } 
   

} 
oci_commit($db_conn);
oci_free_statement($result);
oci_close($db_conn);
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
