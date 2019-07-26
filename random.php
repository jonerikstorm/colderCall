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
$updateBias_sql = "UPDATE `STUDENTS` SET `coefficient` = :coefficient WHERE `id` = :id;";
$saveAbsent_sql = "UPDATE `STUDENTS` SET `absent` = :absent, `absentDate` = :date WHERE `id` = :id;";
$updatePrefs_sql = "UPDATE `userPreferences` SET `numPeriods` = :numPeriods, `defaultPeriod` = :defaultPeriod, `allowVolunteers` = :allowVolunteers, `allowRepeats` = :allowRepeats, `minimumBetween`= :minimumBetween, `nameSelection` = :nameSelection;";

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
            $db4->prepare($updatePrefs_sql)->execute(['minimumBetween' => $_POST['minimumBetween'],'nameSelection' => $_POST['nameSelection'], 'numPeriods' => $_POST['numPeriods'],'defaultPeriod' => $_POST['defaultPeriod'], 'allowVolunteers' => $_POST['allowVolunteers'], 'allowRepeats' => $_POST['allowRepeats']]);
            exit;
            break;
        case "updateBias":
            $db6 = new PDO("sqlite:coldcalls.sqlite3");
            $db6->prepare($updateBias_sql)->execute(['coefficient' => $_POST['coefficient'], 'id' => $_POST['id']]);
            exit;
            break;
    }
}
// Use $_GET to specify period so we can bookmark it
if(isset($_GET['p']) && ($_GET['p'] <= $userPrefs[0]['numPeriods'])) {
    $getPeriod = $_GET['p'];
}  else {$getPeriod = 99;}
?>
<html lang="en-us">
<head>
    <title>colderCalls 0.2</title>
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
var currentPeriod;


//Set the globals; a GET overrides user default.
let getPeriod = <?php echo $getPeriod ?>;
if (getPeriod===99)
    {
        currentPeriod = userPreferences["defaultPeriod"];
    } else {
    currentPeriod = getPeriod;
}
var lastID = new Array;

//When the page loads we start with our first person and prepare the table, but hide it.
$(document).ready(function () {

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
        if (lastID[0]["id"] !== 0) {
            $("#statusBar").prepend("<div class=\"spinner-border spinner-border-sm\"></div>");
            for (let i in students) {
                if (students[i]["id"] === lastID[Object.keys(lastID).length - 1]) {
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
        if (lastID[0]["id"] !== 0) {
            $("#statusBar").html("<div class=\"spinner-border spinner-border-sm\"></div>");
            for (let i in students) {
                if (students[i]["id"] === lastID[Object.keys(lastID).length - 1]) {
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

        updateTable();
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

function changePeriod (period) {
    currentPeriod = period;
    updateTable();
}
function periodMenuDropDownf() {
//Erase what's there.
    $("#periodDropDownMenu").empty();
    for (let i=1; (i-1) < userPreferences.numPeriods;i++) {

            $("#periodDropDownMenu").append('<span class="dropdown-item" onclick="changePeriod('
                + i
                + ');" id="p'
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
        +'<tr><td>Number of Periods</td><td><div class="form-group">'
        +'<select class="form-control-sm" id="numPeriodSelector" onchange="updateNumPeriods();"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option></select></div>'
        +'</td></tr>'
        +'<tr><td>Minimum calls before repeat</td>'
        +'<td><div class="slidecontainer"><input type="range" oninput="updateMinText();" onchange="updateMin();" min="0" max="11" value="1" class="slider" id="betweenSlide"></div>'
        +'<div id="minimumBetweenDisplay"></div></td></tr>'
        + '<tr><td>Name Format</td><td><div onclick="updateNameSelection();"><div class="form-check-inline"><label class="form-check-label"><input type="radio" class="form-check-input" name="nameSelectRadios" value=3>First & Last Name</label></div><div class="form-check-inline"><label class="form-check-label">'
        + '<input type="radio" class="form-check-input" name="nameSelectRadios" value=5>First Name & Last Initial</label></div><div class="form-check-inline disabled"><label class="form-check-label"><input type="radio" class="form-check-input" name="nameSelectRadios" value=1>First Name Only</label></div></div></td></tr>'
    );
    $('input[name=nameSelectRadios][value='+userPreferences.nameSelection+']').prop("checked",true);
	$("#betweenSlide").val(userPreferences["minimumBetween"]);
	$("#minimumBetweenDisplay").text($("#betweenSlide").val());
	$("#defaultPeriodSelector").val(userPreferences["defaultPeriod"]);
    $("#numPeriodSelector").val(userPreferences["numPeriods"]);
}

function updateNameSelection() {
    userPreferences["nameSelection"] = $('input[name=nameSelectRadios]:checked').val();
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
        }
    );
}

function updateMinText()
{
    let value = $("#betweenSlide").val();
    $("#minimumBetweenDisplay").text(value);
    if (value === "0") { $("#minimumBetweenDisplay").empty(); $("#minimumBetweenDisplay").text("Repeats Allowed.");}
    if (value === "11") { $("#minimumBetweenDisplay").empty(); $("#minimumBetweenDisplay").text("Full Class Before Repeat.");}

}

function updateMin()
{

    userPreferences["minimumBetween"] = $("#defaultPeriodSelector").val();
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
        }
    );
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
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
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
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
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
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
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
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
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
    for (let i in students) {
        if (students[i]["id"] === idno) { return i;}
    }
}

function updateBias(index)
{
   students[index]["coefficient"] = $('#slide'+index).val();
    $.post("random.php",
        {
            action: "updateBias",
            id: students[index]["id"],
            coefficient: students[index]["coefficient"]
        }
    );
  //  updateTable();
}


function getIDbyIndexBy(idxno)
{
    for (let i in students) {
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
                + '<div class="slidecontainer"><input type="range" oninput="updateBiasText('
                + i
                 + ');" onchange="updateBias('
                + i
                + ');" min="0" max="10" value="1" class="slider" id="biasSlide'
                +  i
                + '"></div><div id="biasText'
                + i
                + '"</div></td><td>'
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
            $('#biasSlide'+i).val(students[i]["coefficient"]);
            $('#biasText'+i).text(students[i]["coefficient"]);

        }
    }
}

function updateBiasText(index)
{
    let value = $('#biasSlide'+index).val();
    $('#biasText'+index).text(value);
    if (value === "0") { $('#biasSlide'+index).empty(); $('#biasText'+index).text("Won't be called.");}

}

//add a function that auto biases, trying to get more involvement from those who answer poorly.

</script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="jumbotron-fluid">
                <div class="display-2" style="text-align:center" id="victim"></div>
            </div>
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
             <button class="btn btn-outline-danger" id="absentButton" type="button" onclick="toggleStudentAbsent(getIndexByID(lastID[Object.keys(lastID).length - 1]));">Mark Absent</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col" id="statusBar"></div>
    </div>
    <div class="row">
        <div class="col">
            <div class="table-responsive-sm border" id="preferencesTable">
        <table class="table table-hover">
            <thead class="thead-light">
                    <tr>
                            <th>Preference Name</th>
                            <th>Setting</th>
                    </tr>
            </thead>
            <tbody id="preferencesTableItems"></tbody>
        </table>
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
                <button class="btn btn-outline-primary" id="storeEnabled" type="button" onclick="saveEnabled();">Save Enabled Statuses</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>