
function writeStudent(index)
{
    let id = getIDbyIndex(index);
    const statusBarText = $("#statusBar").html();
    $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
    $.post("random.php",
        {
            action: "writeStudent",
            id: students[index]["id"],
            enabled: students[index]["enabled"],
            absent: students[index]["absent"],
            coefficient: students[index]["coefficient"]
        }, () => {$("#statusBar").html(statusBarText);});
}

function toggleStudentEnabled(index)
{
    students[index]["enabled"] === true ? students[index]["enabled"] = false:students[index]["enabled"] = true;
    writeStudent(index);
    updateTable();
}
/*
function saveEnabled() {
    const statusBarText = $("#statusBar").html();
    for(let i=0; i <Object.keys(students).length; i++) {
        if (students[i]["period"] === currentPeriod) {
            writeStudent(i);
        }
    }
    $("#statusBar").html(statusBarText);
    updateTable();
}
*/

function toggleStudentAbsent(index) {
        students[index]["absent"] === true ? students[index]["absent"] = false : students[index]["absent"] = true;
        writeStudent(index);
        updateTable();
}

function getIndexByID(idno)
{
    for (let i=0; i <Object.keys(students).length; i++) {
        if (students[i]["id"] === idno) { return i;}
    }
}

function updateBias(index)
{
    students[index]["coefficient"] = $('#biasSlide'+index).val();
    writeStudent(index);

}


function getIDbyIndex(idxno)
{
     return students[idxno]["id"];
}

function updateTable() {
    //Erase what's there.
    $("#studentTable").empty();
    for (let i=0; i <Object.keys(students).length; i++) {
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
    if (value === "0") {
        $('#biasSlide'+index).empty(); $('#biasText'+index).text("Won't be called.");
    }

}

//
//
//
function writeGlobalPrefs() {
    const statusBarText = $("#statusBar").html();
    $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
    $.post("random.php",
        {
            action: "updateGlobalPrefs",
            defaultPeriod: globalPreferences["defaultPeriod"],
            numPeriods: globalPreferences["numPeriods"]
        },  () => {$("#statusBar").html(statusBarText);});
}
function writePeriodPrefs(period) {
    const statusBarText = $("#statusBar").html();
    $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
    $.post("random.php",
        {
            action: "updatePeriodPrefs",
            period: period,
            allowVolunteers: periodPreferences[period]["allowVolunteers"],
            allowRepeats: periodPreferences[period]["allowRepeats"],
            minimumBetween: periodPreferences[period]["minimumBetween"],
            nameSelection: periodPreferences[period]["nameSelection"]
        },  () => {$("#statusBar").html(statusBarText);});
}

function toggleVolunteers(period)
{
    periodPreferences[period]["allowVolunteers"] === true ? periodPreferences[period]["allowVolunteers"] = false:periodPreferences[period]["allowVolunteers"] = true;
    //updatePrefs();
    writePeriodPrefs(period);
}

function changePeriod (period) {
    currentPeriod = period;
    updateTable();
    periodMenuDropDownf();
}

function periodMenuDropDownf() {
//Erase what's there.
    $("#periodDropDownMenu").empty();
    for (let i=1; (i-1) < globalPreferences.numPeriods;i++) {

        $("#periodDropDownMenu").append('<span class="dropdown-item" onclick="changePeriod('
            + i
            + ');" id="p'
            + i
            + '">'
            + (i === currentPeriod ? "✓":" ")
            + i
            + "</span>");

    }

}

function periodPref(period)
{
    $("#prefsTabs").append('<li class="nav-item" id="periodPrefsTab'
        + period
        + '"><a class="nav-link inactive" id="PeriodPrefsTabLink'
        + period
        + '" data-toggle="tab" href="" role="tab">Period '
        + period
        + '</a></li>');
    $("#prefsTabsContent").append('<div class="tab-pane fade" id="periodPrefs'
        + period
        + '" role="tabpanel"><div class="table-responsive-sm border" id="periodPrefTable' +
        + period
        + '"><table class="table table-hover"><thead class="thead-light"><tr><th>Period '
        + period
        + ' Preference Name</th><th>Setting</th></tr></thead><tbody id="periodPrefTableItems'
        + period
        + '"></div><tr><td>Minimum calls before repeat</td>'
        + '<td><div class="slidecontainer"><input type="range" oninput="updateMinText('
        + period
        + ');" onchange="updateMin('
        + period
        + ');" min="0" max="11" value="1" class="slider" id="betweenSlide'
        + period
        + '"></div>'
        + '<div id="minimumBetweenDisplay'
        + period
        + '"></div></td></tr><tr><td>Name Format</td><td><div onclick="updateNameSelection('
        + period
        + ');"><div class="form-check-inline"><input type="radio" class="form-check-input" name="nameSelectRadios'
        + period
        + '" value=3>First & Last Name</div><div class="form-check-inline"><input type="radio" class="form-check-input disabled" name="nameSelectRadios'
        + period
        + '" value=5>First Name & Last Initial</div><div class="form-check-inline disabled"><input type="radio" class="form-check-input disabled" name="nameSelectRadios'
        + period
        + '" value=1>First Name Only</div></div></td></tr>'
        + '<tr><td>Include Volunteer<td><div class="form-check-inline"><label class="form-check-label"><input type="checkbox" onclick="toggleVolunteers('
        + period
        + ');" class="form-check-input "'
        + ((periodPreferences[period]["allowVolunteers"])? "checked":"unchecked")
        + ' id="allowVolunteersCheckBox'
        + period
        + '"></label></div></td></tr></div></tbody></table></div>'
    );
    $('input[name=nameSelectRadios'+period+'][value='+periodPreferences[period]["nameSelection"]+']').prop("checked",true);
    $('#betweenSlide'+period).val(periodPreferences[period]["minimumBetween"]);
    updateMinText(period);
}

function updatePrefs () {
    $("#prefsTabs").html('<li class="nav-item" id="globalPrefsTab"><a class="nav-link active" id="globalPrefsTabLink" data-toggle="tab" href="" role="tab">Global Preferences</a></li>');
    $("#prefsTabsContent").html('<div class="tab-pane fade show active" id="globalPrefs" role="tabpanel"><div class="table-responsive-sm border" id="preferencesTable"><table class="table table-hover"><thead class="thead-light"><tr><th>Preference Name</th><th>Setting</th></tr></thead><tbody id="preferencesTableItems"></tbody>                  </table> </div></div>');
    for (let i=1; i < globalPreferences["numPeriods"] + 1; i++){
        periodPref(i);
        $('#periodPrefsTab'+i).on('click', function (e) {
            e.preventDefault();
            //hide all tabs
            $(".tab-pane").hide();
            $('#periodPrefs'+i).tab('show');
            $('#periodPrefs'+i).fadeIn('fast');

        });
    }
    //Erase what's there and draw again
    $("#preferencesTableItems").empty().append('<tr><td>Default Period</td><td><div class="form-group">'
        +'<select class="form-control-sm" id="defaultPeriodSelector" onchange="updatePeriod();"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option></select></div>'
        +'</td></tr>'
        +'<tr><td>Number of Periods</td><td><div class="form-group">'
        +'<select class="form-control-sm" id="numPeriodSelector" onchange="updateNumPeriods();"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option></select></div>'
        +'</td></tr>'
    );
    $("#defaultPeriodSelector").val(globalPreferences["defaultPeriod"]);
    $("#numPeriodSelector").val(globalPreferences["numPeriods"]);
}

function updateNameSelection(period) {
    periodPreferences[period]["nameSelection"] = Number($('input[name=nameSelectRadios'+period+']:checked').val());
    writePeriodPrefs(period);
}

function updateMinText(period)
{
    let value = $('#betweenSlide'+period).val();
    $('#minimumBetweenDisplay'+period).text(value);
    if (value === "0") { $('#minimumBetweenDisplay'+period).empty(); $('#minimumBetweenDisplay'+period).text("Repeats Allowed.");}
    if (value === "11") { $('#minimumBetweenDisplay'+period).empty(); $('#minimumBetweenDisplay'+period).text("Full Class Before Repeat.");}

}

function updateMin(period)
{
    periodPreferences[period]["minimumBetween"] = $('#betweenSlide'+period).val();
    writePeriodPrefs(period);
}

//
// Handle global perfefences
//
function updateNumPeriods()
{
    globalPreferences["numPeriods"] = $("#numPeriodSelector").val();
    writeGlobalPrefs();
    periodMenuDropDownf();
}

function updatePeriod()
{
    globalPreferences["defaultPeriod"] = $("#defaultPeriodSelector").val();
    writeGlobalPrefs();
}

//
// Student selection routine
//
function selectStudent2 (period, periodLastID) {
    // Since we don't want to write to the original list and we need to keep it global, we have to use this hack to make a copy.
    let studentsCopy = JSON.parse(JSON.stringify(students));

    //Volunteer is a special case that we add in manually.
    let volunteer = {"id":0,"f_name" : "Volunteer",  "l_name": " ", "enabled" : true, "period" :period, "coefficient": 1};

    //We just want the people in this period.
    let studentsSelectable = studentsCopy.filter((value,index,array) => {return (array[index]["period"] === period && array[index]["enabled"] && !array[index]["absent"]);});

    //Stick Volunteer at the beginning if enabled
    if (periodPreferences[period]["allowVolunteers"]) {studentsSelectable.unshift(volunteer);}

    //Pop enough off the lastID list (preferences can change)
    let present = Object.keys(studentsSelectable).length;
    $("#statusBar").text("Total Present: " + present).append(periodPreferences[period]["allowVolunteers"] ? " (including Volunteer)": " ");

    //People in the lastID, less the last one we just popped off, are out.
    //This has to go after the stuff above because those people will not be included again.
    //These people are only temporarily out so we need to adjust the length of lastID accordingly
    for (let i=0; i < Object.keys(periodLastID).length; i++) {
        for (let j = Object.keys(studentsSelectable).length - 1; j > -1;j--) {
            if (studentsSelectable[j]["id"] === lastID[period][i]) {
                studentsSelectable.splice(j, 1);

            }
        }
    }

    // Instead of using the indexes, we'll go by ID so it's easier to copy to lastID
    // This should create an array with the ID present one time for each coefficient
    let selectArray = new Array();
    let k = 0;
    for (let i = 0; i < Object.keys(studentsSelectable).length; i++) {
        for (let j = 0; j < studentsSelectable[i]["coefficient"]; j++) {

            selectArray[k] = studentsSelectable[i]["id"];
            k++;

        }
    }

    // Alternate technique
    //var points = [40, 100, 1, 5, 25, 10];
    //points.sort(function(a, b){return 0.5 - Math.random()});

    let winner = selectArray[Math.floor(Math.random() * selectArray.length)];


    //do we want this to persist? Maybe in $_SESSION?
    //This is the biggest the lastID list can be
    while (Object.keys(periodLastID).length >= present && Object.keys(periodLastID).length > 0) {
        periodLastID.pop();
    }

    //If the user preference goes lower, we can go lower.
    while (Object.keys(periodLastID).length >= periodPreferences[period]["minimumBetween"] && Object.keys(periodLastID).length > 0) {
        periodLastID.pop();
    }
    periodLastID.unshift(winner);
    writeLastID(period, periodLastID);
    lastID[0] = period;
    for (let i=0;i < Object.keys(studentsSelectable).length; i++) {
        if (winner === studentsSelectable[i]["id"]) {
            let output =  studentsSelectable[i]["f_name"];
            if (periodPreferences[period]["nameSelection"] === 3) {
                output += " ";
                output += studentsSelectable[i]["l_name"];
            }
            if (periodPreferences[period]["nameSelection"] === 5) {
                output += " ";
                output += studentsSelectable[i]["l_name"][0];
                if(i!==0) {
                    output +=".";
                }
            }
            return output;

        }
    }

throw("Bummer.");

}

function writeLastID(period, periodLastID)
{
    lastID[period] = periodLastID;
    const statusBarText = $("#statusBar").html();
    $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
    $.post("random.php",
        {
            action: "writeLastID",
            lastID: JSON.stringify(lastID)
        }, () => {
            $("#statusBar").html(statusBarText);
        });
}