<?php
    require_once "../queries/functions.php";
    $connBlobActive = getDBConnection("blob_active");
    $queries = array();
    parse_str($_SERVER['QUERY_STRING'], $queries);

    if (!isset($queries["g"])) header("Location: ../");
    $pageGroup = $queries["g"];
        
    $groupsUsersQuery = "SELECT g.groupId, g.name, gu.groupUserId, gu.userId, 
        (select userId from groups_users gu_inner where gu_inner.groupId = gu.groupId ORDER BY gu_inner.groupUserId limit 1) AS adminUser
        FROM groups g LEFT JOIN groups_users gu ON gu.groupId = g.groupId WHERE g.groupId = ".$pageGroup." GROUP BY gu.groupUserId";
    $groupsUsersResult = $connBlobActive->query($groupsUsersQuery);

    $firstRow = $groupsUsersResult->fetch_assoc();
    if ($firstRow === null) { header("Location: ../"); }
    $isAdmin = $firstRow["adminUser"] == $_COOKIE["blob_user"];
    $groupName = $firstRow["name"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <link rel="stylesheet" href="../style.css">
    <title><?php echo $groupName ?> - BlobActive</title>
</head>
<body>
    <section class="mainWidget">
        <div class="editBtns">
            <a class="backBtn btn1" href="../">Back</a>
            <?php if ($isAdmin) { ?>
                <a class="edit btn1" onclick="document.querySelector('.editForm').classList.toggle('invisible')">Edit Group</a>
            <?php }; ?>
        </div>
        <div class="groups"><nav><?php
            echo '<h1>Blob<span>Active</span></h1><h3>Logged in as <span>'.$_COOKIE["blob_user"].'</span>
                 - Viewing exercise group: <span>'.$groupName.'</span></h3>';
        ?></nav>
        
        <?php if ($isAdmin) { ?><div class="editForm <?php if ($groupName != "New group") echo "invisible" ?>">
            <form class="editForm_form" action='../queries/exercise-update.php' method='post'>
                <div>
                    <input type="hidden" name="page_group" value="<?php echo $pageGroup; ?>"/>
                    <input type="text" class="rename_group_txt btn1" name="rename_group_txt" placeholder="New group name" 
                        onkeydown="return event.key != 'Enter'" oninput="this.value = this.value.replace(/[^A-Za-z0-9 ]/g, '')">
                    <button class="rename_group btn1" type="submit" name="rename_group"><span>Rename</span></button>
                    <button class="delete_group btn1" type="submit" name="delete_group"><span>Delete group</span></button>
                </div>
                <div>
                    <input type="text" class="add_user_group_txt btn1" name="add_user_group_txt" placeholder="New userId to add (userId,userId)" 
                        onkeydown="return event.key != 'Enter'" oninput="this.value = this.value.replace(/[^A-Za-z0-9,]/g, '')">
                    <button class="add_user_group btn1" type="submit" name="add_user_group"><span>Add user to group</span></button>
                </div>
            </form>
            <?php if ($groupsUsersResult->num_rows > 1) {
                echo "<div class='usersRemove'>";
                while ($user = $groupsUsersResult->fetch_assoc())
                    echo "<form action='../queries/exercise-update.php' method='post'><input type='hidden' name='page_group_user' value='".$user["groupUserId"]."'/>
                        <button class='btn1' type='submit' name='remove_user_group'>".$user["userId"]."</button></form>";
                echo "</div>";
            }; ?>
        </div><?php }; ?>
        
        <table class="groupTable">
            <tr><th>User</th><?php
                $weekdayNames = [1=>"Monday", 2=>"Tuesday", 3=>"Wednesday", 4=>"Thursday", 5=>"Friday", 6=>"Saturday", 7=>"Sunday"];
                foreach ($weekdayNames as $dayNum => $dayName) echo "<th>".$dayName."</th>";
            ?></tr><?php
                $groupsUsersResult->data_seek(0);
                while ($row = $groupsUsersResult->fetch_assoc()) {
                    echo "<tr><td data-label='User:'>".$row["userId"]."</td>";
                    $plansUserQuery = "SELECT planId, name, weekday FROM plans WHERE groupId = ".$pageGroup." AND userId = '".$row["userId"]."'";
                    $plansUserResult = $connBlobActive->query($plansUserQuery);

                    $plansByDay = [];
                    if ($plansUserResult && $plansUserResult->num_rows > 0) {
                        while ($plan = $plansUserResult->fetch_assoc())
                            $plansByDay[$plan["weekday"]][] = [$plan["planId"], $plan["name"]];
                    }

                    for ($i = 1; $i <= 7; $i++) {
                        if (!empty($plansByDay[$i])) echo "<td data-label='".$weekdayNames[$i].":'>
                            <a href='../plans?p=".$plansByDay[$i][0][0]."'>".$plansByDay[$i][0][1]."</a></td>";
                        else if (isset($_COOKIE["blob_user"]) && $row["userId"] == $_COOKIE["blob_user"])
                            echo "<td data-label='".$weekdayNames[$i].":'><form class='underTableBtns' action='../queries/exercise-update.php' method='post'>
                                <input type='hidden' name='additional' value='".$pageGroup."§".$_COOKIE["blob_user"]."§".$i."§'/>
                                <input class='addPlanBtn' type='submit' name='addPlanBtn' value='+'/></form></td>";
                        else echo "<td></td>";
                    } echo "</tr>";
                }
            ?>
        </table></div>
    </section>
    <script src="../script.js"></script>
</body>
</html>