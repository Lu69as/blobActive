<?php
    require_once "../queries/functions.php";
    $connBlobActive = getDBConnection("blob_active");

    if (isset($_POST) && !empty($_POST)) {
        $query = "";
        switch (true) {
            case isset($_POST['add_exercise']):
                $query = "INSERT INTO exercises (planId, name, sets, reps) VALUES (".$_POST['page_plan'].", 'New exercise', 1, 1)";
                break;

            case isset($_POST['remove_exercise']):
                $query = "DELETE FROM exercises WHERE exerciseId = ".$_POST['remove_exercise'];
                break;

            case isset($_POST['finish_exercise']):
                $query = "UPDATE plans SET planHistory = '".$_POST['newPlanHistory']."' WHERE planId = ".$_POST['page_plan'];
                break;

            case isset($_POST['addPlanBtn']):
                $ps = explode("§", $_POST['additional']);
                $query = "INSERT INTO plans (groupId, userId, name, weekday) VALUES (".$ps[0].",'".$ps[1]."','New plan',".$ps[2].")";
                break;

            case isset($_POST['rename_plan']):
                $query = "UPDATE plans SET name = '".$_POST['rename_plan_txt']."' WHERE planId = ".$_POST['page_plan'];
                break;

            case isset($_POST['delete_plan']):
                $query = "DELETE FROM plans WHERE planId = ".$_POST['page_plan'];
                break;

            case isset($_POST['rename_group']):
                $query = "UPDATE groups SET name = '".$_POST['rename_group_txt']."' WHERE groupId = ".$_POST['page_group'];
                break;

            case isset($_POST['delete_group']):
                $query = "DELETE FROM groups WHERE groupId = ".$_POST['page_group'];
                break;

            case isset($_POST['add_user_group']):
                $query = "INSERT INTO groups_users (groupId, userId) VALUES";
                $users = explode(",", $_POST['add_user_group_txt']);
                foreach ($users as $usr) $query .= ($usr == $users[0] ? "" : ",")." (".$_POST['page_group'].", '$usr')";
                break;

            case isset($_POST['remove_user_group']):
                $query = "DELETE FROM groups_users WHERE groupUserId = ".$_POST['page_group_user'];
                break;

            case isset($_POST['add_group']):
                $connBlobActive->query("INSERT INTO groups (name) VALUES ('New group')");
                $query = "INSERT INTO groups_users (groupId, userId) VALUES (".$connBlobActive->insert_id.", '".$_POST['add_group_userId']."')";
                break;
        }
        $connBlobActive->query($query);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    // if(intval($_GET['id'])) 
    //     $connBlobActive->query("UPDATE exercises set ".$_GET['col']." = '".$_GET['val']."' where exerciseId = ".$_GET['id']);
    exit;
?>