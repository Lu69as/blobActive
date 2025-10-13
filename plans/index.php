<?php
    require_once "../queries/functions.php";
    $connBlobActive = getDBConnection("blob_active");
    $queries = array();
    parse_str($_SERVER['QUERY_STRING'], $queries);

    if (!isset($queries["p"])) header("Location: ../");
    $pagePlan = $queries["p"];

    $exercisesPlanQuery = "SELECT p.groupId, p.planName, p.userId, e.exerciseId, e.exerciseName, pe.plan_exerciseId, count(ps.plan_setId) as setsNum,
        group_concat(concat(ps.plan_setId, '||', ps.plan_setNote, '||', ps.plan_setLength , '||', ps.plan_weight) ORDER BY ps.plan_setId SEPARATOR '§§') as setsCol
        FROM plans p LEFT JOIN plan_exercise pe ON pe.planId = p.planId LEFT JOIN exercises e ON pe.exerciseId = e.exerciseId 
        LEFT JOIN plan_sets ps ON ps.plan_exerciseId = pe.plan_exerciseId WHERE p.planId = $pagePlan GROUP BY pe.plan_exerciseId";
    $exercisesPlanResult = $connBlobActive->query($exercisesPlanQuery);

    $firstRow = $exercisesPlanResult->fetch_assoc();
    if ($firstRow === null || !isset($_COOKIE["blob_user"])) { header("Location: ../"); }
    
    $isAdmin = $firstRow["userId"] == $_COOKIE["blob_user"];
    $planName = $firstRow["planName"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?php echo $baseUrl; ?>/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <link rel="stylesheet" href="../style.css">
    <title><?php echo $planName ?> - BlobActive</title>
</head>
<body>
    <section class="mainWidget">
        <div class="editBtns">
            <a class="backBtn btn1" href="../groups/?g=<?php echo $firstRow["groupId"] ?>">Back</a>
            <?php if ($isAdmin) { ?>
                <a class="edit btn1" onclick="document.querySelector('.editForm').classList.toggle('invisible');
                    document.querySelectorAll('.plan').forEach((e) => e.classList.toggle('editingMode')); toggleEditHistory();">Edit Plan</a>

                <a title="Save as today's workout" class="workout btn1">Save Workout</a>
            <?php }; ?>
        </div>
        <div class="plan"><nav><?php
            echo "<h1>Blob<span>Active</span></h1><h3>Logged in as <span>{$_COOKIE['blob_user']}</span>
                 - Viewing exercise plan: <span>$planName</span></h3>";
        ?></nav>
        <?php if ($isAdmin) { ?>
            <div class="editForm <?php if ($groupName != "New group") echo "invisible" ?>">
                <form class="editForm_form <?php if ($planName != "New plan") echo "invisible" ?>" action='../queries/exercise-update.php' method='post'><div>
                    <input type="hidden" name="page_plan" value="<?php echo $pagePlan; ?>"/>
                    <input type="text" class="rename_plan_txt btn1" name="rename_plan_txt" placeholder="New plan name" onkeydown="return event.key != 'Enter'">
                    <button class="rename_plan btn1" type="submit" name="rename_plan"><span>Rename</span></button>
                    <button class="delete_plan btn1" type="submit" name="delete_plan"><span>Delete plan</span></button>
                </div></form>
            </div>
        <?php }; ?>
        <table class="planTable">
            <?php
                $exercisesPlanResult->data_seek(0);
                if (!empty($firstRow["plan_exerciseId"])) {
                    while ($row = $exercisesPlanResult->fetch_assoc()) {
                        $currentExercise = $row["plan_exerciseId"];
                        echo "<tr id='exercise_$currentExercise'>
                            <th class='exer_name'>{$row['exerciseName']} - Notes</th>
                            <th>Reps / Time</th><th>Weight</th>";

                        if (!$isAdmin) echo "<script>document.querySelectorAll('.planTable input, .planTable button').forEach((e) => {
                            let clearEvents = e.cloneNode(true); clearEvents.disabled = true; e.parentElement.replaceChild(clearEvents, e);})</script></tr>";


                        $sets = explode("§§", $row["setsCol"]);
                        for ($i = 0; $i < count($sets); $i++) {
                            $setInfo = explode("||", $sets[$i]);

                            echo "<tr class='set_row' id='set_$setInfo[0]' data-label='Set ".($i + 1).":'><td class='set_row_plan_setNote'><input type='text' 
                                placeholder='Notes for Set' value='$setInfo[1]'>".($i == 0 ? "<button onClick='toggleSetsView(this.parentElement.parentElement)'>></button>
                                </td>" : "</td>")."<td class='set_row_plan_setLength'><input type='text' placeholder='Reps / time' inputmode='numeric' value='$setInfo[2]'></td>
                                <td class='set_row_plan_weight'><input type='text' placeholder='Weight' value='$setInfo[3]'></td>";
                            
                            if ($isAdmin && $i == 0) echo "<td class='remove'><form action='../queries/exercise-update.php' method='post'>
                                <button type='submit' title='Remopve exercise from plan'>".file_get_contents('../img/icons/trash.svg')."</button>
                                <input name='remove_exercise' type='hidden' value='$currentExercise'/></form></td></tr>";

                            else if ($isAdmin) echo "<td class='remove'><form action='../queries/exercise-update.php' method='post'><button type='submit'
                                title='Remove set from exercise'><span>_</span></button><input name='remove_set' type='hidden' value='$setInfo[0]'/></form></td></tr>";
                            else echo "<td class='remove'></td></tr>";
                        }
                        if ($isAdmin) echo "<tr class='set_row add_set'><td colspan='4'><form action='../queries/exercise-update.php' method='post'>
                            <button type='submit' title='Add set to exercise'><span>+</span> Add new set to exercise</button>
                            <input name='add_set' type='hidden' value='$currentExercise'/></form></td></tr>";
                    }
                } else echo "<h4 style='padding-inline:15px;opacity:.8'>Not a lot going on here...</h4>";
            ?>
        </table>
        <?php if ($isAdmin) { ?>
            <form class="underTableBtns" action='../queries/exercise-update.php' method="post">
                <input type="hidden" name="page_plan" value="<?php echo $pagePlan; ?>"/>
                <div class="add_exercise_id">
                    <input type="hidden" name="add_exercise_id" value="addNew"/>
                    <input type="text" class="add_exercise_search" name="add_exercise_search" placeholder="Write your exercise here"/>
                    <ul><li id="exercise_addNew">Create new exercise</li><?php
                        $exerciseListQuery = "SELECT exerciseId, exerciseName FROM exercises";
                        $exerciseListResult = $connBlobActive->query($exerciseListQuery);
                        if ($exerciseListResult->num_rows > 0) while ($row = $exerciseListResult->fetch_assoc()) 
                            echo "<li id='exercise_{$row['exerciseId']}'>{$row['exerciseName']}</li>";
                    ?></ul>
                </div>
                <input class="add_exercise" type="submit" name="add_exercise" value="+ Add exercise"/>
            </form>
        <?php }; ?>
    </div>
    <div class="plan workoutHistory">
        <nav>
            <h2>Plan History</h2>
            <div class="sortBtns">
                <button onclick="getPlanHistory(<?php echo $pagePlan; ?>, 'date')" class="btn1">Sort by date</button>
                <button onclick="getPlanHistory(<?php echo $pagePlan; ?>, 'exercises')" class="btn1">Sort by Exercises</button>
            </div>
        </nav><div></div></div>
    </section>
    <script src="../script.js"></script>
    <script>getPlanHistory(<?php echo $pagePlan; ?>, 'date')</script>
</body>
</html>