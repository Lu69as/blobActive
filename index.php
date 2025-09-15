<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="./img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./img/favicon/favicon-16x16.png">
    <link rel="manifest" href="./img/favicon/site.webmanifest">

    <link rel="stylesheet" href="./style.css">
    <title>BlobActive</title>
</head>
<?php
    require_once "./queries/functions.php";
    $queries = array();
    parse_str($_SERVER['QUERY_STRING'], $queries);
?>
<body>
    <section class="mainWidget"><?php
        if(!isset($_COOKIE["blob_user"])) {
            $connBlob = getDBConnection("blob");
            $userIdListQuery = "SELECT userId FROM users";
            $userIdListResult = $connBlob->query($userIdListQuery);

            ?><div class="login_select"><p class="userId_list"><?php
                if ($userIdListResult->num_rows > 0)
                    while($row = $userIdListResult->fetch_assoc()) { echo $row["userId"] . '|'; };
            ?>
            </p><div class="login_tabs">
                    <div class="sign_up">Sign up</div>
                    <div class="log_in" style="opacity:.7">Log in</div>
                </div>
                <form class="sign_up" action="./queries/log-in.php" method="post" ><div>
                    <div><label for="userId">User ID*</label>
                        <input required="yes" maxlength="30" type="text" name="userId" class="userId" placeholder="exampler123"></div>
                    <div><label for="password">Password*</label>
                        <input required="yes" maxlength="40" type="password" name="password" placeholder="Pass123"></div></div><div>
                    <div><label for="userName">Username*</label>
                        <input required="yes" maxlength="30" type="text" name="userName" placeholder="The Great Exampler"></div>
                    <div><label for="profilePic">Profile picture</label>
                        <input maxlength="255" type="text" name="profilePic" placeholder="https://example.com/profile.jpg"></div>
                    </div><label for="desc">Description</label><textarea name="desc" id="desc" 
                        maxlength="255" placeholder="I am a great example."></textarea>
                    <button class="invalid btn1" type="submit">Sign up!</button>
                </form>
                <form class="log_in" style="display:none" action="./queries/log-in.php" method="post"><div>
                    <div><label for="userId">User ID*</label>
                        <input required="yes" maxlength="30" type="text" name="userId" class="userId" placeholder="exampler123"></div>
                    <div><label for="password">Password*</label>
                        <input required="yes" maxlength="40" type="password" name="password" placeholder="Pass123"></div></div>
                    <button class="invalid btn1" type="submit">Sign up!</button><input type="hidden" name="log_in_formType" value="true">
                </form>
            </div><?php
        } else {
            $connBlobActive = getDBConnection("blob_active");
            $groupsListQuery = "SELECT g.groupId, g.name, GROUP_CONCAT(gu.userId SEPARATOR ', ') AS users
                FROM groups g JOIN groups_users gu ON g.groupId = gu.groupId GROUP BY g.groupId, g.name
                HAVING users LIKE '%".$_COOKIE["blob_user"]."%'";
            $groupsListResult = $connBlobActive->query($groupsListQuery);

            ?><div class="groups">
                <nav>
                    <h1>Blob<span>Active</span></h1>
                    <h3>Logged in as <span><?php echo $_COOKIE["blob_user"] ?></span></h3>
                </nav><div class="groupsList"><?php
                    if ($groupsListResult->num_rows > 0) {
                        while($row = $groupsListResult->fetch_assoc()) {
                            echo '<a href="./groups/?g='.$row["groupId"].'">'.file_get_contents("./img/icons/lockers.svg").'<p class="g_name">'.
                                $row["name"].'</p><p class="g_users">'.$row["users"].'</p></a>';
                        };
                    } else echo "<a>No groups with your user was found. Create a new group here!</a>"
                ?></div>
                <form class="underTableBtns" action='./queries/exercise-update.php' method="post">
                    <input type="hidden" name="add_group_userId" value="<?php echo $_COOKIE["blob_user"]; ?>"/>
                    <input class="add_group" type="submit" name="add_group" value="+ Add group"/>
                </form>
            </div><?php
        }?>
    </section>
    <script src="./script.js"></script>
</body>
<?php if (isset($connBlob)) $connBlob->close(); ?>
</html>