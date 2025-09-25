<?php
    require_once "../queries/functions.php";
    $connBlobActive = getDBConnection("blob_active");

    if (isset($_POST) && !empty($_POST)) {
        $query = "";
        switch (true) {
            case isset($_POST['add_exercise_id']):
                $exerciseId = $_POST['add_exercise_id'];
                if ($exerciseId == "addNew") {
                    $connBlobActive->query("INSERT into exercises (exerciseName) values ('".$_POST['add_exercise_search']."')");
                    $exerciseId = $connBlobActive->insert_id;
                }
                $connBlobActive->query("INSERT into plan_exercise (planId, exerciseId) values (".$_POST['page_plan'].", $exerciseId)");
                $exerciseId = $connBlobActive->insert_id;
                $query = "INSERT into sets (plan_exerciseId) values ($exerciseId)";
                break;

            case isset($_POST['remove_exercise']): $query = "DELETE FROM plan_exercise WHERE plan_exerciseId = ".$_POST['remove_exercise']; break;
            case isset($_POST['remove_set']): $query = "DELETE FROM sets WHERE setId = ".$_POST['remove_set']; break;
            case isset($_POST['add_set']): $query = "INSERT into sets (plan_exerciseId) values (".$_POST['add_set'].")"; break;

            case isset($_POST['addPlanBtn']):
                $ps = explode("§", $_POST['additional']);
                $query = "INSERT INTO plans (groupId, userId, planName, weekday) VALUES ($ps[0],'".$ps[1]."','New plan',".$ps[2].")";
                break;

            case isset($_POST['rename_plan']): $query = "UPDATE plans SET planName = '".$_POST['rename_plan_txt']."' WHERE planId = ".$_POST['page_plan']; break;
            case isset($_POST['delete_plan']): $query = "DELETE FROM plans WHERE planId = ".$_POST['page_plan']; break;
            case isset($_POST['rename_group']): $query = "UPDATE groups SET groupName = '".$_POST['rename_group_txt']."' WHERE groupId = ".$_POST['page_group']; break;
            case isset($_POST['delete_group']): $query = "DELETE FROM groups WHERE groupId = ".$_POST['page_group']; break;

            case isset($_POST['add_user_group']):
                $query = "INSERT INTO group_users (groupId, userId) VALUES";
                $users = explode(",", $_POST['add_user_group_txt']);
                foreach ($users as $usr) $query .= ($usr == $users[0] ? "" : ",")." (".$_POST['page_group'].", '$usr')";
                break;

            case isset($_POST['remove_user_group']): $query = "DELETE FROM group_users WHERE groupUserId = ".$_POST['page_group_user']; break;

            case isset($_POST['add_group']):
                $connBlobActive->query("INSERT INTO groups (groupName) VALUES ('New group')");
                $query = "INSERT INTO group_users (groupId, userId) VALUES (".$connBlobActive->insert_id.", '".$_POST['add_group_userId']."')";
                break;
        }
        $connBlobActive->query($query);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    if(intval($_GET['id'])) $connBlobActive->query("UPDATE sets SET ".$_GET['col']." = '".$_GET['val']."' where setId = ".$_GET['id']);
    exit;
?>