<?php
    require_once "../queries/functions.php";
    $connBlobActive = getDBConnection("blob_active");
    $output = false;

    if (isset($_POST) && !empty($_POST)) {
        $query = "";
        switch (true) {
            case isset($_POST['add_exercise_id']):
                $exerciseId = $_POST['add_exercise_id'];
                if ($exerciseId == "addNew") {
                    $connBlobActive->query("INSERT into exercises (exerciseName) values ('{$_POST['add_exercise_search']}')");
                    $exerciseId = $connBlobActive->insert_id;
                }
                $connBlobActive->query("INSERT into plan_exercise (planId, exerciseId) values ({$_POST['page_plan']}, $exerciseId)");
                $exerciseId = $connBlobActive->insert_id;
                $query = "INSERT into plan_sets (plan_exerciseId) values ($exerciseId)";
            break;

            case isset($_POST['remove_exercise']): $query = "DELETE FROM plan_exercise WHERE plan_exerciseId = {$_POST['remove_exercise']}"; break;
            case isset($_POST['remove_set']): $query = "DELETE FROM plan_sets WHERE plan_setId = {$_POST['remove_set']}"; break;
            case isset($_POST['add_set']): $query = "INSERT into plan_sets (plan_exerciseId) values ({$_POST['add_set']})"; break;

            case isset($_POST['addPlanBtn']):
                $ps = explode("§", $_POST['additional']);
                $query = "INSERT INTO plans (groupId, userId, planName, weekday) VALUES ($ps[0], '$ps[1]', 'New plan', $ps[2])";
            break;

            case isset($_POST['rename_plan']): $query = "UPDATE plans SET planName = '{$_POST['rename_plan_txt']}' WHERE planId = {$_POST['page_plan']}"; break;
            case isset($_POST['delete_plan']): $query = "DELETE FROM plans WHERE planId = {$_POST['page_plan']}"; break;
            case isset($_POST['rename_group']): $query = "UPDATE groups SET groupName = '{$_POST['rename_group_txt']}' WHERE groupId = {$_POST['page_group']}"; break;
            case isset($_POST['delete_group']): $query = "DELETE FROM groups WHERE groupId = {$_POST['page_group']}"; break;

            case isset($_POST['add_user_group']):
                $query = "INSERT INTO group_users (groupId, userId) VALUES";
                $users = explode(",", $_POST['add_user_group_txt']);
                foreach ($users as $usr) $query .= ($usr == $users[0] ? "" : ",")." ({$_POST['page_group']}, '$usr')";
            break;

            case isset($_POST['remove_user_group']): $query = "DELETE FROM group_users WHERE groupUserId = {$_POST['page_group_user']}"; break;

            case isset($_POST['add_group']):
                $connBlobActive->query("INSERT INTO groups (groupName) VALUES ('New group')");
                $query = "INSERT INTO group_users (groupId, userId) VALUES (".$connBlobActive->insert_id.", '{$_POST['add_group_userId']}')";
            break;
        }
        $connBlobActive->query($query);
    }
    else {
        switch (true) {
            case isset($_GET['update_id']):
                $connBlobActive->query("UPDATE {$_GET['table']} SET {$_GET['col']} = '{$_GET['val']}' where {$_GET['tableId']} = {$_GET['update_id']}");
            break;

            case isset($_GET['history_id']):
                $sort = $_GET['sort'] == "exercises"; $output = "";
                $workoutsPlanQuery = "SELECT w.workoutId, w.workout_date, ROW_NUMBER() OVER (PARTITION BY ws.workout_exerciseId ORDER BY ws.workout_setId) AS setNum,
                    ws.workout_setId, e.exerciseId, e.exerciseName, ws.workout_setLength, ws.workout_weight, ws.workout_setNote from workout_sets ws
                    join workout_exercise we on we.workout_exerciseId = ws.workout_exerciseId join exercises e on e.exerciseId = we.exerciseId
                    join workouts w on w.workoutId = we.workoutId where w.planId = ".$_GET['history_id'].($sort ? " ORDER BY exerciseId, workout_date, workoutId, setNum" : "");
                $workoutsPlanResult = $connBlobActive->query($workoutsPlanQuery);
                
                if ($workoutsPlanResult->num_rows > 0) {
                    $currentRow = 0; $currentSet = 999;
                    while ($row = $workoutsPlanResult->fetch_assoc()) {
                        $date = str_replace("-", "/", $row["workout_date"]);
                        if ($row["setNum"] < $currentSet && $sort) {
                            if ($currentRow != 0) $output .= "</div>";
                            if ($currentRow != $row["exerciseName"]) $output .= "</div>";
                            if ($currentRow != $row["exerciseName"]) $output .= "<div class='historyParent'><h2>{$row['exerciseName']}:</h2>";

                            $output .= "<div id='workout_{$row['workoutId']}' class='exercise'><h3><span>{$row['workoutId']}.</span> $date:</h3>
                                <p>Set {$row['setNum']}: <b class='historyEditField'
                                    data-label='table=workout_sets&tableId=workout_setId&update_id={$row['workout_setId']}&col=workout_setLength'>{$row['workout_setLength']}
                                </b> - <b class='historyEditField'
                                    data-label='table=workout_sets&tableId=workout_setId&update_id={$row['workout_setId']}&col=workout_weight'>{$row['workout_weight']}</b></p>";
                            $currentRow = $row["exerciseName"];
                        }
                        else if ($row["setNum"] < $currentSet) {
                            if ($currentRow != 0) $output .= "</div>";
                            if ($currentRow != $row["workoutId"]) $output .= "</div>";
                            if ($currentRow != $row["workoutId"]) $output .= "<div class='historyParent'><h2><span>{$row['workoutId']}.</span> 
                                <b class='historyEditField' data-label='table=workouts&tableId=workoutId&update_id={$row['workoutId']}&col=workout_date'>$date</b>:</h2>";

                            $output .= "<div class='exercise'><h3>{$row['exerciseName']}:</h3>
                                <p>Set {$row['setNum']}: <b class='historyEditField'
                                    data-label='table=workout_sets&tableId=workout_setId&update_id={$row['workout_setId']}&col=workout_setLength'>{$row['workout_setLength']}
                                </b> - <b class='historyEditField'
                                    data-label='table=workout_sets&tableId=workout_setId&update_id={$row['workout_setId']}&col=workout_weight'>{$row['workout_weight']}</b></p>";
                            $currentRow = $row["workoutId"];
                        }
                        else $output .= "<p>Set {$row['setNum']}: <b class='historyEditField'
                            data-label='table=workout_sets&tableId=workout_setId&update_id={$row['workout_setId']}&col=workout_setLength'>{$row['workout_setLength']}
                            </b> - <b class='historyEditField'
                            data-label='table=workout_sets&tableId=workout_setId&update_id={$row['workout_setId']}&col=workout_weight'>{$row['workout_weight']}</b></p>";
                        $currentSet = $row["setNum"];
                    }
                } else $output = "<h4>This plan has no history. Yet...</h4>";
            break;

            case isset($_GET['save_id']):
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
            break;
        }
    }

    if ($output) echo $output;
    else header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
?>