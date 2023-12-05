<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Page</title>
</head>
<body>

<header>
    <h1>Community Information</h1>
</header>

<?php
require 'db_connect.php';
require 'security.php';
 
redirectIfNotLoggedIn();
 


function getUserName($db_conn, $userID) {
    $sql = "SELECT userName FROM users
        WHERE userID= :userID";
        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ":userID", $userID);
        oci_execute($statement);
        $row = oci_fetch_assoc($statement);
        oci_free_statement($statement);


    return $row['USERNAME']; 
}

    
function displayCommunityInfo($db_conn, $communityID) {
        $sql = "SELECT * FROM community c, memberCount_level m
        WHERE c.memberCount = m.memberCount AND communityID = :communityID";
        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ":communityID", $communityID);
        oci_execute($statement);
        $row = oci_fetch_assoc($statement);
        oci_free_statement($statement);
        if($row){
            ?>
       <div>
       <form method="post" action="" onsubmit="return confirm('Are you sure you want to leave this community?');"> 
        
            <p><strong>Community Name:</strong> <?php echo $row['NAME']; ?></p>
            <p><strong>Community ID:</strong> <?php echo $row['COMMUNITYID']; ?></p>
            <p><strong>Leader User ID:</strong> <?php echo $row['LEADER']; ?></p>
            <p><strong>Member Count:</strong> <?php echo $row['MEMBERCOUNT']; ?></p>
            <p><strong>Community Level: </strong><?php echo $row['COMMUNITYLEVEL']; ?></p>
            <button type = "submit" name = "submit">Leave Current Community</button>
        </form> 
        </div>
        <?php
        }   
    }

    //bug refresh after join doesn't show community info
    function joinCommunity($db_conn,$userID){
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registration'])) {
           
            $itemId = $_POST['registration'][0];
        
                $update_user_sql = "UPDATE users SET partof_community = :communityID WHERE userID = :userID";
                $user_stid = oci_parse($db_conn, $update_user_sql);
                oci_bind_by_name($user_stid, ":communityID", $itemId);
                oci_bind_by_name($user_stid, ":userID", $userID);
                oci_execute($user_stid);
                oci_free_statement($user_stid);
    
                $update_community_sql = "UPDATE community SET memberCount = membercount +1 WHERE communityID = :communityID ";
                $community_stid = oci_parse($db_conn, $update_community_sql);
                oci_bind_by_name($community_stid, ":communityID", $itemId);
                oci_execute($community_stid);
                oci_free_statement($community_stid);
                
                echo'<script> alert("You successfully joined the community!"); window.location.href = "community.php"; </script>';
                exit();
                
            }
        }

       // bug need to refresh twice/click on leave twice
       function leaveCommunity($db_conn,$userID,$userName, $communityID){
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {

$check_sql = "SELECT communityID FROM community WHERE leader = :userName ";
                $check_stid = oci_parse($db_conn, $check_sql);
                oci_bind_by_name($check_stid, ":userName", $userName);
                oci_execute($check_stid);
                $row=oci_fetch_assoc($check_stid);
            

                oci_free_statement($check_stid);

        if($row != false){

            echo "<p><strong> ERROR[Sorry...You cannot leave your own community if you are a leader].</p>";
            
        }else{

            $community_sql = "UPDATE community SET memberCount = memberCount - 1 WHERE communityID = :communityID ";
                $community_stid = oci_parse($db_conn, $community_sql);
                oci_bind_by_name($community_stid, ":communityID", $communityID);
                oci_execute($community_stid);
                oci_free_statement($community_stid);

                $leave_sql = "UPDATE users SET partof_community = null WHERE userID = :userID ";
                $leave_stid = oci_parse($db_conn, $leave_sql);
                oci_bind_by_name($leave_stid, ":userID", $userID);
                oci_execute($leave_stid);
                oci_free_statement($leave_stid); 
                
                echo'<script> alert("You successfully left the community!"); window.location.href = "community.php"; </script>';
                exit();
        }
    }
}
           


$userID = $_SESSION['userid'];;
$userName = getUserName($db_conn, $userID);


$sql = "SELECT partof_community FROM users WHERE userID = :userID";
$result = oci_parse($db_conn, $sql);
oci_bind_by_name($result, ':userID', $userID);
oci_execute($result);

$communityID = oci_fetch_assoc($result)['PARTOF_COMMUNITY'];

if ($communityID) {
    leaveCommunity($db_conn,$userID,$userName, $communityID);
    displayCommunityInfo($db_conn, $communityID);
} else {
    echo "<p><strong> Sorry...You currently are not a part of any community, and here are your options:</p>";
   ?>
     <div> <div><h2>- Create a new Community</h2><a href="create_community.php">[click here to Create]</a>
</div>
</div>

<div>
<h2>- Filtering Existing Communities</h2>
<form method="POST" action=""> 

<label for="filterCount">Member Count: </label>
<input type ="text" id ="filterCount" name="filterCount">

<label for="filterOperator1">AND/OR: </label>
<select name="filterOperator1" >
<option value="AND" selected>AND</option>
<option value="OR">OR</option>
</select>

<label for="filterLevel">Community Level: </label>
<input type ="text" id ="filterLevel" name="filterLevel">

<input type ="submit" name ="apply" value="apply filters">

<?php
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['apply'])) {
$memberCount=!empty($_POST['filterCount'])? $_POST['filterCount']: null;
if ($_POST['filterLevel'] == '0'){
    $communityLevel = $_POST['filterLevel'];
}else{
    $communityLevel=!empty($_POST['filterLevel'])? $_POST['filterLevel']: null;
}
if ($memberCount == null && $communityLevel == null){
    echo"<p>Please enter values for filtering.</p>";
}

$operator1=isset($_POST['filterOperator1'])? $_POST['filterOperator1']: 'AND';

if ($memberCount != null || $communityLevel != null){

    if ($memberCount != null && $communityLevel != null && $operator1=='AND'){
        if (!sanitizeUserInputCheck($memberCount) && !sanitizeUserInputCheck($communityLevel)) {
            $sql = "SELECT c.communityID,c.name,c.leader,c.memberCount, m.communityLevel 
            FROM community c, memberCount_level m 
            WHERE c.memberCount = m.memberCount 
            AND c.memberCount = :memberCount
            AND m.communityLevel = :communityLevel";
            
            $statement = oci_parse($db_conn, $sql);
            oci_bind_by_name($statement, ":memberCount", $memberCount);
            oci_bind_by_name($statement, ":communityLevel", $communityLevel);
        }else{
            echo "invalid input for security purpose!! ";
        }
      
    }

    if ($memberCount != null && $communityLevel != null && $operator1=='OR'){
        if (!sanitizeUserInputCheck($memberCount) && !sanitizeUserInputCheck($communityLevel)) {
        $sql = "SELECT c.communityID,c.name,c.leader,c.memberCount, m.communityLevel 
        FROM community c, memberCount_level m 
        WHERE c.memberCount = m.memberCount 
        AND (c.memberCount = :memberCount OR m.communityLevel = :communityLevel)";
        
        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ":memberCount", $memberCount);
        oci_bind_by_name($statement, ":communityLevel", $communityLevel);
    }else{
        echo "invalid input for security purpose!! ";
    }
    }


    if ($memberCount != null && $communityLevel == null){
        if (!sanitizeUserInputCheck($memberCount)) {
        $sql = "SELECT c.communityID,c.name,c.leader,c.memberCount, m.communityLevel 
        FROM community c, memberCount_level m 
        WHERE c.memberCount = m.memberCount 
        AND c.memberCount = :memberCount";
        
        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ":memberCount", $memberCount);
    }else{
        echo "invalid input for security purpose!! ";
    }
    }

    if ($memberCount == null && $communityLevel != null){
        if (!sanitizeUserInputCheck($communityLevel)) {
        $sql = "SELECT c.communityID,c.name,c.leader,c.memberCount, m.communityLevel 
        FROM community c, memberCount_level m 
        WHERE c.memberCount = m.memberCount 
        AND m.communityLevel = :communityLevel";
        
        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ":communityLevel", $communityLevel);
    }else{
        echo "invalid input for security purpose!! ";
    }
    }

    oci_execute($statement);
    $row = oci_fetch_assoc($statement);

    if ($row == null){
        echo"<p>no community found with the given criteria.</p>";
    }else{
    echo"<h3>Filtered Communities</h3>";
    echo"<table border='1'>";
    echo"<tr>
    <th>Community ID</th>
    <th>Community Name</th>
    <th>Leader Name</th>
    <th>Member count</th>
    <th>Community Level</th>
    </tr>";
    
    while ($row) {
        echo "<tr>";
        echo "<td>" .htmlspecialchars($row['COMMUNITYID']). "</td>";
        echo "<td>" .htmlspecialchars($row['NAME']). "</td>";
        echo "<td>" .htmlspecialchars($row['LEADER']). "</td>";
        echo "<td>" .htmlspecialchars($row['MEMBERCOUNT']). "</td>";
        echo "<td>" .htmlspecialchars($row['COMMUNITYLEVEL']). "</td>";
        echo "</tr>";
        $row = oci_fetch_assoc($statement);
    }

    echo"</table>";
    oci_free_statement($statement);
}


   }
}

?>
   
  
</form>
</div>

   <div>
       <h2>- Join an Existing Community</h2>


       <form action="" method="POST" onsubmit="return confirm('Are you sure you want to join this community?');">
    <table border="1">
    <tr>
        <th>Select</th>
        <th>Community ID</th>
        <th>Community Name</th>
        <th>Leader Name</th>
        <th>Member count</th>
        <th>Community Level</th>
    </tr>
        <?php
        $sql = "
        SELECT c.communityID,c.name,c.leader,c.memberCount, m.communityLevel FROM community c, memberCount_level m WHERE c.memberCount = m.memberCount 
        AND NOT EXISTS (SELECT * FROM community x WHERE x.communityID = c.communityID AND x.leader = :userName)"; 

        $stid = oci_parse($db_conn, $sql);
        oci_bind_by_name($stid, ":userName", $userName);
        oci_execute($stid);


        while ($row = oci_fetch_assoc($stid)) {
            echo "<tr>";
            echo "<td><input type='radio' name='registration[]' id='registration' value='" . htmlspecialchars($row['COMMUNITYID']) . "'></td>";
            echo "<td>" .htmlspecialchars($row['COMMUNITYID']). "</td>";
            echo "<td>" .htmlspecialchars($row['NAME']). "</td>";
            echo "<td>" .htmlspecialchars($row['LEADER']). "</td>";
            echo "<td>" .htmlspecialchars($row['MEMBERCOUNT']). "</td>";
            echo "<td>" .htmlspecialchars($row['COMMUNITYLEVEL']). "</td>";
            echo "</tr>";
        }

        ?>
    </table>
    <input type="submit" name="submit" value="Join Community">
</form>
    </div>
   <?php
   joinCommunity($db_conn,$userID);
   
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
