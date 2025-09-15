<?php
    require_once "../queries/functions.php";
    $connBlobActive = getDBConnection("blob_active");
    $queries = array();
    parse_str($_SERVER['QUERY_STRING'], $queries);

    if (!isset($queries["p"])) header("Location: ../");
    $pagePlan = $queries["p"];

    $exercisesPlanQuery = "SELECT p.name AS planName, p.userId, p.planHistory, e.exerciseId, e.name, e.sets, e.reps, e.weight 
        FROM plans p LEFT JOIN exercises e ON e.planId = p.planId WHERE p.planId = ".$pagePlan;
    $exercisesPlanResult = $connBlobActive->query($exercisesPlanQuery);

    $firstRow = $exercisesPlanResult->fetch_assoc();
    if ($firstRow === null) { header("Location: ../"); }
    $planHistory = json_decode($firstRow["planHistory"], true);
    $isAdmin = $firstRow["userId"] == $_COOKIE["blob_user"];
    $planName = $firstRow["planName"];
    $exercisesPlanResult->data_seek(0);
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
    <title><?php echo $planName ?> - BlobActive</title>
</head>
<body>
    <section class="mainWidget">
        <div class="editBtns">
            <a class="backBtn btn1" href="../">Back</a>
            <?php if ($isAdmin) { ?>
                <a class="edit btn1" onclick="document.querySelector('.editForm').classList.toggle('invisible')">Edit Group</a>
            <?php }; ?>
        </div>
        <div class="plan"><nav><?php
            echo '<h1>Blob<span>Active</span></h1><h3>Logged in as <span>'.$_COOKIE["blob_user"].'</span>
                 - Viewing exercise plan: <span>'.$planName.'</span></h3>';
        ?></nav>
        <?php if ($isAdmin) { ?><div class="editForm <?php if ($groupName != "New group") echo "invisible" ?>">
            <form class="editForm_form <?php if ($planName != "New plan") echo "invisible" ?>" action='../queries/exercise-update.php' method='post'><div>
                <input type="hidden" name="page_plan" value="<?php echo $pagePlan; ?>"/>
                <input type="text" class="rename_plan_txt btn1" name="rename_plan_txt" placeholder="New plan name" onkeydown="return event.key != 'Enter'">
                <button class="rename_plan btn1" type="submit" name="rename_plan"><span>Rename</span></button>
                <button class="delete_plan btn1" type="submit" name="delete_plan"><span>Delete plan</span></button>
            </div></form>
        </div><?php }; ?>
        <table class="planTable">
            <tr>
                <th class="userColumn">Name</th>
                <th>Sets</th>
                <th>Reps / Time</th>
                <th>Weight</th>
            </tr>
            <?php
                if (!empty($firstRow["exerciseId"])) {
                    while ($row = $exercisesPlanResult->fetch_assoc()) {
                        echo "<tr id='exercise_".$row["exerciseId"]."'>
                            <td class='exer_name'><input type='text' value='".$row["name"]."'></td>
                            <td class='exer_sets'><input type='number' value='".$row["sets"]."'></td>
                            <td class='exer_reps'><input type='number' value='".$row["reps"]."'></td>
                            <td class='exer_weight'><input type='text' value='".$row["weight"]."'></td>";
                        if (!$isAdmin) {
                            echo "<script>document.querySelectorAll('.planTable input, button').forEach((e) => {
                                let clearEvents = e.cloneNode(true); clearEvents.disabled = true;
                                e.parentElement.replaceChild(clearEvents, e);})</script></tr>";
                        }
                        else echo "<td><form action='../queries/exercise-update.php' method='post'><button type='submit'>".file_get_contents('../img/icons/trash.svg')."</button>
                            <input name='remove_exercise' type='hidden' value='".$row["exerciseId"]."'/></form></td></tr>";
                    }
                }
            ?>
        </table>
        <?php if ($isAdmin) { ?>
            <form class="underTableBtns" action='../queries/exercise-update.php' method="post">
                <input type="hidden" name="page_plan" value="<?php echo $pagePlan; ?>"/>
                <input type="hidden" name="currentPlanHistory" value='<?php echo json_encode($planHistory); ?>'/>
                <input type="hidden" name="newPlanHistory"/>
                <input class="add_exercise" type="submit" name="add_exercise" value="+ Add exercise"/>
                <input class="finish_exercise" type="submit" name="finish_exercise" value="✓ Add to history"/>
            </form>
        <?php }; ?>
        <div class="plan_history">
            <a onclick="this.parentElement.querySelector('p').classList.toggle('visible');">Show plan history</a>
            <!-- <a onclick="this.nextElementSibling.classList.toggle('visible');">Update plan history</a> -->
            <p><?php
            if ($planHistory != null) {
                foreach ($planHistory as $dt) {
                    echo "<b>".$dt["date"].":</b><br>";
                    foreach ($dt["exercises"] as $ex) echo $ex."<br>";
                    echo "<br>";
                }
            } else echo "History of this plan is empty";
            ?></p>
        </div></div>
    </section>
    <script src="../script.js"></script>
</body>
</html>