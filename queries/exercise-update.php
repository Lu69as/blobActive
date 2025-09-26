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
                $query = "INSERT into plan_sets (plan_exerciseId) values ($exerciseId)";
                break;

            case isset($_POST['remove_exercise']): $query = "DELETE FROM plan_exercise WHERE plan_exerciseId = ".$_POST['remove_exercise']; break;
            case isset($_POST['remove_set']): $query = "DELETE FROM plan_sets WHERE plan_setId = ".$_POST['remove_set']; break;
            case isset($_POST['add_set']): $query = "INSERT into plan_sets (plan_exerciseId) values (".$_POST['add_set'].")"; break;

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
    
    if(intval($_GET['update_id']))
        $connBlobActive->query("UPDATE plan_sets SET ".$_GET['col']." = '".$_GET['val']."' where plan_setId = ".$_GET['update_id']);

    else if(intval($_GET['save_id'])) {
        $plan_id = $_GET['save_id'];
        $connBlobActive->query("INSERT into workouts (planId) values ($plan_id);"); 
        $workoutId = $connBlobActive->insert_id;

        $connBlobActive->query("INSERT into workout_exercise (workoutId, exerciseId) select $workoutId, e.exerciseId from plan_exercise pe 
	        join exercises e on e.exerciseId = pe.exerciseId where pe.planId = $plan_id;"); 
        $workout_exerciseId = $connBlobActive->insert_id;
        
        $connBlobActive->query("INSERT into workout_sets (workout_exerciseId, workout_setNote, workout_setLength, workout_weight) 
            select we.workout_exerciseId, ps.plan_setNote, ps.plan_setLength, ps.plan_weight from plan_sets ps
            join plan_exercise pe on pe.plan_exerciseId = ps.plan_exerciseId JOIN workout_exercise we ON we.exerciseId = pe.exerciseId 
            AND we.workoutId = $workoutId where pe.planId = $plan_id;");
    }
    exit;
?>