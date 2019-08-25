<!DOCTYPE html>
<?php
// Load in the database init routine and the AJAX post routine.
require 'initdb.php';
require 'post.php';
// If there's no database, we will initialize one with defaults
if (!file_exists('coldcalls.sqlite3')) {
    db_init();
    }
// If there is no $_POST, then we're loading the app.
// Get all of the students and load the user preferences.
if (!$_POST) {
    try {
        $db = new PDO("sqlite:coldcalls.sqlite3");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }	catch (Exception $e) {
        echo "Unable to connect to database.";
        echo $e->getMessage();
        exit;
    }
    $globalPrefs = $db->query("SELECT * FROM `globalPreferences`;")->fetchAll(PDO::FETCH_ASSOC);
    $lastID = $db->query("SELECT * FROM `lastID`;")->fetchAll(PDO::FETCH_ASSOC);
    $periodPrefs = $db->query("SELECT * FROM `periodPreferences`;")->fetchAll(PDO::FETCH_OBJ);
    $students =  $db->query("SELECT * FROM `STUDENTS`;")->fetchAll(PDO::FETCH_OBJ);
    //Check if the students were absent today or earlier. If earlier, reset absence.
    $timeZone = new DateTimeZone('America/Los_Angeles');
    $dt = new DateTime();
    $dt->setTimezone($timeZone);
    foreach ($students as $student) {
        if ($student->absentDate != $dt->format('Y-m-d')) {
            $student->absent = false;
            $student->absentDate = null;
            $db->prepare("UPDATE `STUDENTS` SET absent = 'false', absentDate = null where `id` = :id;")->execute(['id' => $student->id]);
        }
    }
} else  {
//    If this is an AJAX query coming in, go do that.
    handlepost();
}

// Use $_GET to specify period so we can bookmark it. If someone is trying to get a number greater than
// The number of periods set in the prefs, we ignore it. 99 comes through as meaning just load the default set in the prefs.
if(isset($_GET['p']) && ($_GET['p'] <= $globalPrefs[0]['numPeriods'])) {
    $getPeriod = $_GET['p'];
}  else {
    $getPeriod = 99;
}
?>
<html lang="en-us">
<head>
<title>colderCalls <?php echo $globalPrefs[0]['version']; ?></title>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1" name="viewport">
<link href="static/bootstrap-4.3.1-dist/css/bootstrap.min.css" rel="stylesheet">
<script src="static/jquery-3.4.1.min.js"></script>
<script src="static/popper.js"></script>
<script src="static/bootstrap-4.3.1-dist/js/bootstrap.min.js"></script>
<script src="colderCall.js"></script>
<script>
// All we are going to do here is handle the DOM ready function.
"use strict";
// These are all the globals: the student table, the user preferneces, the current period, and the array of recently
// called kiddos

//Get the student data from the database via PHP
let students = JSON.parse('<?php echo json_encode($students,JSON_NUMERIC_CHECK ); ?>',(k, v) => v === "true" ? true : v === "false" ? false : v);
let periodPreferences = JSON.parse('<?php echo json_encode($periodPrefs,JSON_NUMERIC_CHECK ); ?>',(k, v) => v === "true" ? true : v === "false" ? false : v);
// This should make the period preferences indexes correspond with their number, saving a lot of headache.
periodPreferences.unshift(null);

//Have PHP write in the preferences from the database into a JSON array
let globalPreferences = JSON.parse('<?php echo json_encode($globalPrefs[0], JSON_NUMERIC_CHECK); ?>',(k, v) => v === "true" ? true : v === "false" ? false : v);
let lastID = JSON.parse('<?php echo $lastID[0]["lastID"]; ?>');
let currentPeriod;


//GET overrides user default. 99 means use prefs
let getPeriod = <?php echo $getPeriod ?>;
if (getPeriod===99)
    {
        currentPeriod = globalPreferences["defaultPeriod"];
    } else {
    currentPeriod = getPeriod;
}

//When the page loads we start with our first person and prepare the table, but hide it.
$(document).ready(function () {

    //Initialize the student table
    $("#bigTable").hide();
    updateTable();

    //Initialize the preferences table
    $("#prefsContent").hide();
    updatePrefs();

    $('#globalPrefsTab').on('click', function (e) {
        e.preventDefault();
        $(".tab-pane").fadeOut('fast');
        $("#globalPrefs").tab('show');
        $("#globalPrefs").fadeIn('fast');

    });


   // $("#1periodPrefsTab").on('click', (e) => {e.preventDefault(); $("#1periodPrefs").show();});

    //Pick the first victim on load
    $("#victim").html(selectStudent2(currentPeriod, Array.from(lastID[currentPeriod])));

    //Hook the action of the correct button to choosing a new person, updating their correct tally
    //Don't try and update the Volunteer's count
    $("#correct").click(function () {
        const statusBarText = $("#statusBar").html();
        $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
        if (lastID[lastID[0]][0] !== 0) {

            for (let i=0; i <Object.keys(students).length; i++) {

                if (students[i]["id"] === lastID[lastID[0]][0]) {
                    students[i]["correct"]++;
                    $.post("random.php",
                        {
                            action: "correct",
                            id: students[i]["id"],
                            student_id: students[i]["id"],
                            correcto: students[i]["correct"],
                            isCorrect: "true"
                        }, () => {$("#statusBar").html(statusBarText);}
                    );
                }
            }
            updateTable();
        }
       $("#victim").html(selectStudent2(currentPeriod, Array.from(lastID[currentPeriod])));

    });

    //Hook the action of the incorrect button to pickign a new person, updating their incorrect tally
    //Don't try and update the Volunteer's count
    $("#incorrect").click(function () {
        const statusBarText = $("#statusBar").html();
        $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
        if (lastID[lastID[0]][0] !== 0) {

            for (let i=0; i <Object.keys(students).length; i++) {

                if (students[i]["id"] === lastID[lastID[0]][0]) {
                    students[i]["incorrect"]++;
                    $.post("random.php",
                        {
                            action: "incorrect",
                            id: students[i]["id"],
                            student_id: students[i]["id"],
                            incorrecto: students[i]["incorrect"],
                            isCorrect: "false"
                        }, () => {$("#statusBar").html(statusBarText);}
                    );
                }
            }
        }
        updateTable();
        $("#victim").html(selectStudent2(currentPeriod, lastID[currentPeriod]));

    });

    //The skip button just gets a new student
    $("#skipButton").click(function () {
        $("#victim").html(selectStudent2(currentPeriod, lastID[currentPeriod]));
    });

    //The table button toggles the appearance of the student table
    $("#tableButton").click(function () {
        $("#bigTable").fadeToggle();
    });

    //The preferences button shows the preferences tabs
    $("#prefsButton").click(function () {
        $("#prefsContent").fadeToggle();
    });

    //The preferences button shows the preferences tabs
    $("#globalPrefLink").click(function () {
        $("#globalPrefs").fadeToggle();
    });


    //Programatically fill the period dropdown menu
    periodMenuDropDownf();

});
</script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="jumbotron-fluid" id="victim" style="font-size: 10vw; text-align: center; width: 98%"></div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button type="button" class="btn btn-block btn-success" id="correct">Correct</button>
            <button type="button" class="btn btn-block btn-secondary" id="skipButton">Skip</button>
            <button type="button" class="btn btn-block btn-danger" id="incorrect">Incorrect</button>
        </div>
    </div>
    <div class="row pt-1">
        <div class="col">
            <div class="btn-group">
                <button type="button" class="btn btn-primary" id="tableButton">Students</button>
                <button type="button" class="btn btn-primary" id="prefsButton">Options</button>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        Periods
                    </button>
                    <div class="dropdown-menu" id="periodDropDownMenu" onchange="changePeriod();"></div>
                </div>
            </div>
            <div class="btn-group float-right">
             <button class="btn btn-outline-danger" id="absentButton" type="button" onclick="toggleStudentAbsent(getIndexByID(lastID[lastID[0]][0]));">Mark Absent</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col" id="statusBar"></div>
    </div>
    <div class="row">
        <div class="col" id="prefsContent">
            <ul class="nav nav-tabs" id="prefsTabs">

            </ul>
            <div class="tab-content" id="prefsTabsContent">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="table-responsive-sm border" id="bigTable">
                <table class="table table-hover">
                <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Bias</th>
                    <th>% Correct</th>
                    <th>Absent</th>
                    <th>Enabled</th>
                </tr>
                </thead>
                <tbody id="studentTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>