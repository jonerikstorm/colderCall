<!DOCTYPE html>
<?php
include 'initdb.php';
//all the SQLite3 queries we will use:
$load_sql = "SELECT * FROM `STUDENTS`;";
$prefs_sql = "SELECT * FROM `userPreferences`;";
$answer_sql = "INSERT INTO `ANSWERS` (`timestamp`,`student_id`,`correct`) VALUES(:timestamp,:student_id,:isCorrect);";
$incrementCorrect_sql = "UPDATE `STUDENTS` SET `correct` = :correcto WHERE `id`=:id;";
$incrementIncorrect_sql = "UPDATE `STUDENTS` SET `incorrect` = :incorrecto WHERE `id` = :id;";
$saveEnabled_sql = "UPDATE `STUDENTS` SET `enabled` = :enabled WHERE `id` = :id;";
$saveAbsent_sql = "UPDATE `STUDENTS` SET `absent` = :absent, `absentDate` = :date WHERE `id` = :id;";
$updatePrefs_sql = "UPDATE `userPreferences` SET `numPeriods` = :numPeriods, `defaultPeriod` = :defaultPeriod, `allowVolunteers` = :allowVolunteers, `allowRepeats` = :allowRepeats WHERE `id`=1;";

// Check $_POST for self-AJAXing to update who was called
if (!file_exists('coldcalls.sqlite3')) {
    $db_init = new PDO("sqlite:coldcalls.sqlite3");
    $init_queries = explode (";",$init_sql);
    foreach ($init_queries as $explodedQuery) {$db_init->query($explodedQuery.";");}
    echo "Initializing database.";
    }

$timeZone = new DateTimeZone('America/Los_Angeles');
$dt = new DateTime();
$dt->setTimezone($timeZone);
if (!$_POST) {
    try {
        $db = new PDO("sqlite:coldcalls.sqlite3");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }	catch (Exception $e) {
        echo "Unable to connect to database.";
        echo $e->getMessage();
        exit;
    }
	$students =  $db->query($load_sql)->fetchAll(PDO::FETCH_OBJ);
    foreach ($students as $student) {
        if ($student->absentDate != $dt->format('Y-m-d')) {
            $student->absent = false; $student->absentDate = null;
            $db->prepare("UPDATE `STUDENTS` SET absent = 'false', absentDate = null where `id` = :id;")->execute(['id' => $student->id]);
         }
    }
	$userPrefs = $db->query($prefs_sql)->fetchAll(PDO::FETCH_ASSOC);
} else {

    //add features that makes it so that nothing but this page on this server can POST

    //do I really need a new PDO for each switch case, or do we just need it for the new page load?
    //why did INSERT work on the original PDO but UPDATE did not? Weird.
    switch ($_POST["action"]) {
        case "correct":
            $db2 = new PDO("sqlite:coldcalls.sqlite3");
            $db2->prepare($incrementCorrect_sql)->execute(['correcto' => $_POST['correcto'], 'id' => $_POST['id']]);
            $db2->prepare($answer_sql)->execute(['student_id' => $_POST['student_id'], 'timestamp' => $dt->format(DateTimeInterface::W3C), 'isCorrect' => $_POST['isCorrect']]);
            exit;
            break;
        case "incorrect":
            $db3 = new PDO("sqlite:coldcalls.sqlite3");
            $db3->prepare($incrementIncorrect_sql)->execute(['incorrecto' => $_POST['incorrecto'], 'id' => $_POST['id']]);
            $db3->prepare($answer_sql)->execute(['student_id' => $_POST['student_id'], 'timestamp' => $dt->format(DateTimeInterface::W3C ), 'isCorrect' => $_POST['isCorrect']]);
            exit;
            break;
        case "saveEnabled":
            $db5 = new PDO("sqlite:coldcalls.sqlite3");
            $db5->prepare($saveEnabled_sql)->execute(['enabled' => $_POST['enabled'], 'id' => $_POST['id']]);
            exit;
            break;
        case "saveAbsent":
            $db5 = new PDO("sqlite:coldcalls.sqlite3");
            $db5->prepare($saveAbsent_sql)->execute(['absent' => $_POST['absent'], 'id' => $_POST['id'],'date' => $dt->format('Y-m-d')]);
            exit;
            break;
        case "updatePrefs":
            $db4 = new PDO("sqlite:coldcalls.sqlite3");
            $db4->prepare($updatePrefs_sql)->execute(['numPeriods' => $_POST['numPeriods'],'defaultPeriod' => $_POST['defaultPeriod'], 'allowVolunteers' => $_POST['allowVolunteers'], 'allowRepeats' => $_POST['allowRepeats']]);
            exit;
            break;
    }
}
// Use $_GET to specify period so we can bookmark it 
if(isset($_GET['p'])) {

    switch ($_GET['p']) {

        case 1:
            $getPeriod = 1;
            break;
        case 2:
            $getPeriod = 2;
            break;
        case 3:
            $getPeriod = 3;
            break;
        case 4:
            $getPeriod = 4;
            break;
        case 5:
            $getPeriod = 5;
            break;
        case 6:
            $getPeriod = 6;
            break;
    }

}  else {$getPeriod = 99;}
?>
<html lang="english">
<head>
    <title>Coldcalls</title>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1" name="viewport">
<link href="static/bootstrap-4.3.1-dist/css/bootstrap.min.css" rel="stylesheet">
<script src="static/jquery-3.4.1.min.js"></script>
<script src="static/popper.js"></script>
<script src="static/bootstrap-4.3.1-dist/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="static/fontawesome-free-5.9.0-web/css/all.min.css">
<script src="select.js"></script>
<script>
"use strict";
//Try to limit the use of globals. Better yet, eliminate them.
//Get the data from the database via PHP
let students = JSON.parse('<?php echo json_encode($students,JSON_NUMERIC_CHECK ); ?>',(k, v) => v === "true" ? true : v === "false" ? false : v);
//reset absences
let userPreferences = JSON.parse('<?php echo json_encode($userPrefs[0], JSON_NUMERIC_CHECK); ?>',(k, v) => v === "true" ? true : v === "false" ? false : v);
let currentPeriod;


//Set the globals; a GET overrides user default.
let getPeriod = <?php echo $getPeriod ?>;
if (getPeriod===99)
    {
        currentPeriod = userPreferences["defaultPeriod"];
    } else {
    currentPeriod = getPeriod;
}
let lastID = new Array;

//When the page loads we start with our first person and prepare the table, but hide it.
$(document).ready(function () {

    //Initialize the fancy dropdown menu
    $("#p1").click(function () {
        currentPeriod = 1;
        updateTable();
    });
    $("#p2").click(function () {
        currentPeriod = 2;
        updateTable();
    });
    $("#p3").click(function () {
        currentPeriod = 3;
        updateTable();
    });
    $("#p4").click(function () {
        currentPeriod = 4;
        updateTable();
    });
    $("#p5").click(function () {
        currentPeriod = 5;
        updateTable();
    });
    $("#p6").click(function () {
        currentPeriod = 6;
        updateTable();
    });

    //Initialize the student table
    $("#bigTable").hide();
    updateTable();

    //Initialize the preferences table
    $("#preferencesTable").hide();
    updatePrefs();

    //Pick the first victim on load
    $("#victim").html(selectStudent2(currentPeriod));

    //Hook the action of the correct button to choosing a new person, updating their correct tally
    //Don't try and update the Volunteer's count
    $("#correct").click(function () {
        if (lastID !== 0) {
            for (let i in students) {
                if (students[i]["id"] === lastID) {
                    students[i]["correct"]++;
                    $.post("random.php",
                        {
                            action: "correct",
                            id: students[i]["id"],
                            student_id: students[i]["id"],
                            correcto: students[i]["correct"],
                            isCorrect: "true"
                        }
                    );
                }
            }
            updateTable();
        }
        $("#victim").html(selectStudent2(currentPeriod));
    });

    //Hook the action of the incorrect button to pickign a new person, updating their incorrect tally
    //Don't try and update the Volunteer's count
    $("#incorrect").click(function () {
        if (lastID !== 0) {
            for (let i in students) {
                if (students[i]["id"] === lastID) {
                    students[i]["incorrect"]++;
                    $.post("random.php",
                        {
                            action: "incorrect",
                            id: students[i]["id"],
                            student_id: students[i]["id"],
                            incorrecto: students[i]["incorrect"],
                            isCorrect: "false"
                        }
                    );
                }
            }
        }


        $("#victim").html(selectStudent2(currentPeriod));
    });

    //The skip button just gets a new student
    $("#skipButton").click(function () {
        $("#victim").html(selectStudent2(currentPeriod));
    });

    //The table button toggles the appearance of the student table
    $("#tableButton").click(function () {
        $("#bigTable").toggle();
    });

    //The preferences button shows the preferences table
    $("#prefsButton").click(function () {
        $("#preferencesTable").toggle();
    });

    //Programatically fill the period dropdown menu
    periodMenuDropDownf();

});

function saveEnabled() {
    for(let i in students) {
        if (students[i]["period"] === currentPeriod) {
            $.post("random.php",
                {
                    action: "saveEnabled",
                    id: students[i]["id"],
                    enabled: students[i]["enabled"]
                }
            );
        }
    }
}

function periodMenuDropDownf() {
//Erase what's there.
    $("#periodDropDownMenu").empty();
    for (let i=1; (i-1) < userPreferences.numPeriods;i++) {

            $("#periodDropDownMenu").append('<span class="dropdown-item" id="p'
                + i
                + '">'
                + i
                + "</span>");

        }

}

function updatePrefs () {
    //Erase what's there and draw again
	$("#preferencesTableItems").empty().append('<tr><td>Default Period</td><td><div class="form-group">'
		+'<select class="form-control-sm" id="defaultPeriodSelector" onchange="updatePeriod();"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select></div>'
                +'</td></tr><tr><td>Include Volunteer<td><div class="form-check-inline"><label class="form-check-label"><input type="checkbox" onclick="toggleVolunteers();" class="form-check-input"'
		+ ((userPreferences["allowVolunteers"])? "checked":"unchecked")
		+' id="allowVolunteersCheckBox"></label></div></td></tr>'
                +'</td></tr><tr><td>Allow Repeats<td><div class="form-check-inline"><label class="form-check-label"><input type="checkbox" onclick="toggleRepeats();" class="form-check-input"'
		+ ((userPreferences["allowRepeats"])? "checked":"unchecked")
		+' id="allowRepeatsCheckBox"></label></div></td></tr>'
        +'<tr><td>Number of Periods</td><td><div class="form-group">'
    +'<select class="form-control-sm" id="numPeriodSelector" onchange="updateNumPeriods();"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option></select></div>'
    +'</td></tr>');
	$("#defaultPeriodSelector").val(userPreferences["defaultPeriod"]);
    $("#numPeriodSelector").val(userPreferences["numPeriods"]);
}

function updateNumPeriods()
{
    userPreferences["numPeriods"] = $("#numPeriodSelector").val();
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"]
        }
    );
    periodMenuDropDownf();
}

function updatePeriod()
{
    userPreferences["defaultPeriod"] = $("#defaultPeriodSelector").val();
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"]
        }
    );
}

function toggleVolunteers()
{
    userPreferences["allowVolunteers"] === true ? userPreferences["allowVolunteers"] = false:userPreferences["allowVolunteers"] = true;
    updatePrefs();
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"]
        }
    );
}
function toggleRepeats()
{
    userPreferences["allowRepeats"] === true ? userPreferences["allowRepeats"] = false:userPreferences["allowRepeats"] = true;
    updatePrefs();
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"]
        }
    );
}
function toggleStudentEnabled(IDnumber)
{
    students[IDnumber]["enabled"] === true ? students[IDnumber]["enabled"] = false:students[IDnumber]["enabled"] = true;
    updateTable();
}

function toggleStudentAbsent(IDnumber) {
    if (IDnumber !== 0) {
        students[IDnumber]["absent"] === true ? students[IDnumber]["absent"] = false : students[IDnumber]["absent"] = true;
        $.post("random.php",
            {
                action: "saveAbsent",
                id: students[IDnumber]["id"],
                absent: students[IDnumber]["absent"]
            }
        );
        updateTable();
    }
}

function getIndexByID(idno)
{
    for (i in students) {
        if (students[i]["id"] === idno) { return i;}
    }
}

function getIDbyIndexBy(idxno)
{
    for (i in students) {
        if (idxno === students[i]["id"]) { return students[i]["id"];}
    }
}
function updateTable () {
    //Erase what's there.
    $("#studentTable").empty();
    for (let i in students) {
        if (students[i]["period"] === currentPeriod) {
            $("#studentTable").append("<tr><td>" + students[i]["id"]
                + "</td><td>"
                + students[i]["f_name"]
                    + " "
                    + students[i]["l_name"]
                + "</td><td>"
                    // add a slider for bias
                + '<div class="slidecontainer"><input type="range" min="-10" max="10" value="0" class="slider" id="slide'
                +  students[i]["id"]
                + '"></div></td><td>'
                + ((students[i]["correct"] > 0 || students[i]["incorrect"] > 0) ? Math.round(((students[i]["correct"]) / (students[i]["correct"] + students[i]["incorrect"])) * 100)+"%" :" ")
                + '</td><td><div class="form-check-inline"><label class="form-check-label">'
                + '<input type="checkbox" class="form-check-input" onclick="toggleStudentAbsent('
                + i
                + ');"'
                + ((students[i]["absent"]) ? "checked" : "unchecked")
                + ' id="absentButton'
                + students[i]["id"]
                + '"></label></div></td>'
                + '</td><td><div class="form-check-inline"><label class="form-check-label">'
                + '<input type="checkbox" class="form-check-input" onclick="toggleStudentEnabled('
                + i
                + ');"'
                + ((students[i]["enabled"]) ? "checked" : "unchecked")
                + ' id="checkButton'
                + students[i]["id"]
                + '"></label></div></td></tr>'
            );

        }
    }
}

//add a function that auto biases, trying to get more involvement from those who answer poorly.

</script>
</head>
<body>
<div class="container-fluid">

		<div class="jumbotron-fluid">
            <div class="container-fluid">
             <div class="display-1" style="text-align:center" id="victim">
             </div>
            </div>
		</div>

	<div class="row">
		<div class="col-sm">
			<button type="button" class="btn btn-block btn-success" id="correct">Correct</button>
	  <button type="button" class="btn btn-block btn-secondary" id="skipButton">Skip</button>
			<button type="button" class="btn btn-block btn-danger" id="incorrect">Incorrect</button>
		</div>
	</div>
    <br/>
    <div class="btn-group">
        <button type="button" class="btn btn-primary" id="tableButton">Students</button>
        <button type="button" class="btn btn-primary" id="prefsButton">Options</button>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                Periods
            </button>
            <div class="dropdown-menu" id="periodDropDownMenu">
             <!--   //Programatically generate, but then make sure they don't go away when in the database -->
            </div>
        </div>
</div>
    <div class="btn-group fa-pull-right">
        <button class="btn btn-outline-danger" id="absentButton" type="button" onclick="toggleStudentAbsent(getIndexByID(lastID));">Mark Absent</button>
    </div>
<!--    //maybe add a timer with an option to countdown and an optional stopwatch widget alonog with
    //confirmation that the updates have been made or errors thrown here. -->
<div id="statusBar"></div>
<div class="table-responsive-sm" id="preferencesTable">
        <table class="table table-hover">
                <thead class="thead-light">
                <tr>
                        <th>Preference</th>
                        <th>Enabled</th>
                </tr>
                </thead>
                <tbody id="preferencesTableItems">
                </tbody>
        </table>
        </div>

<div class="table-responsive-sm" id="bigTable">
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
		<tbody id="studentTable">
		</tbody>
	</table>
    <button class="btn btn-outline-primary" id="storeEnabled" type="button" onclick="saveEnabled();">Save Enabled Statuses</button>
</div>
</body>
</html>