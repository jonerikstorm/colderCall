
function writeStudent(id)
{
    const statusBarText = $("#statusBar").html();
    $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
    $.post("random.php",
        {
            action: "writeStudent",
            id: id,
            enabled: students[getIndexByID(id)]["enabled"],
            absent: students[getIndexByID(id)]["absent"],
            coefficient: students[getIndexByID(id)]["coefficient"]
        }, () => {$("#statusBar").html(statusBarText);});
}

function toggleStudentEnabled(index)
{
    students[index]["enabled"] === true ? students[index]["enabled"] = false:students[index]["enabled"] = true;
    updateTable();
}

function saveEnabled() {
    for(let i in students) {
        if (students[i]["period"] === currentPeriod) {
            writeStudent(getIDbyIndex(i));
        }
    }
}


function toggleStudentAbsent(index) {
    if (index !== 0) {
        students[index]["absent"] === true ? students[index]["absent"] = false : students[index]["absent"] = true;
        writeStudent(getIDbyIndex(index));
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
    writeStudent(getIDbyIndex(index));

}


function getIDbyIndex(idxno)
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
    if (value === "0") {
        $('#biasSlide'+index).empty(); $('#biasText'+index).text("Won't be called.");
    }

}

//
//
//
function writePrefs() {
    const statusBarText = $("#statusBar").html();
    $("#statusBar").html(statusBarText+'<div class="spinner-border spinner-border-sm"></div>');
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
        },  () => {$("#statusBar").html(statusBarText);});
}

function toggleVolunteers(period)
{
    userPreferences["allowVolunteers"] === true ? userPreferences["allowVolunteers"] = false:userPreferences["allowVolunteers"] = true;
    updatePrefs();
    writePrefs();
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

function periodPref(period)
{
    $("#prefsTabs").append('<li class="nav-item" id="periodPrefsTab'
        + period
        + '"><a class="nav-link inactive" id="PeriodPrefsTabLink'
        + period
        + '" data-toggle="tab" href="" role="tab">Period '
        + period
        + '</a></li>');
    // All of this has to be made programmatic for each period
    $("#prefsTabsContent").append('<div class="tab-pane fade" id="periodPrefs'
        + period
        + '" role="tabpanel"><div class="table-responsive-sm border" id="periodPrefTable' +
        + period
        + '"><table class="table table-hover"><thead class="thead-light"><tr><th>Preference Name</th><th>Setting</th></tr></thead><tbody id="periodPrefTableItems'
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
        + ');"><div class="form-check-inline"><label class="form-check-label"><input type="radio" class="form-check-input" name="nameSelectRadios'
        + period
        + '" value=3>First & Last Name</label></div><div class="form-check-inline"><label class="form-check-label">'
        + '<input type="radio" class="form-check-input" name="nameSelectRadios" value=5>First Name & Last Initial</label></div><div class="form-check-inline disabled"><label class="form-check-label"><input type="radio" class="form-check-input" name="nameSelectRadios" value=1>First Name Only</label></div></div></td></tr>'
        + '<tr><td>Include Volunteer<td><div class="form-check-inline"><label class="form-check-label"><input type="checkbox" onclick="toggleVolunteers(' +
        + period
        + ');" class="form-check-input"'
        + ((userPreferences["allowVolunteers"])? "checked":"unchecked")
        + ' id="allowVolunteersCheckBox'
        + period
        + '"></label></div></td></tr></div></tbody></table></div>'
    );
    $('input[name=nameSelectRadios'+period+'][value='+userPreferences.nameSelection+']').prop("checked",true);
    $('#betweenSlide'+period).val(userPreferences["minimumBetween"]);
    $('#minimumBetweenDisplay'+period).text($('#betweenSlide'+period).val());
}

function updatePrefs () {
    for (let i=1; i < userPreferences["numPeriods"] + 1; i++){
        periodPref(i);
        $('#periodPrefsTab'+1).on('click', function (e) {
            e.preventDefault();
            //hide all tabs
            $(".tab-pane").hide();
            $('#periodPrefs'+i).show();
            $('#periodPrefs'+i).tab('show');
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
    $("#defaultPeriodSelector").val(userPreferences["defaultPeriod"]);
    $("#numPeriodSelector").val(userPreferences["numPeriods"]);
}

function updateNameSelection(period) {
    userPreferences["nameSelection"] = $('input[name=nameSelectRadios'+period+']:checked').val();
    writePrefs();
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
    userPreferences["minimumBetween"] = $('#defaultPeriodSelector'+period).val();
    writePrefs();
}


function updateNumPeriods()
{
    userPreferences["numPeriods"] = $("#numPeriodSelector").val();
    writePrefs();
    periodMenuDropDownf();
}

function updatePeriod()
{
    userPreferences["defaultPeriod"] = $("#defaultPeriodSelector").val();
    writePrefs();
}
//
//
//
function selectStudent2 (period) {
    // Since we don't want to write to the original list and we need to keep it global, we have to use this hack to make a copy.
    let studentsCopy = JSON.parse(JSON.stringify(students));

    //Volunteer is a special case that we add in manually.
    let volunteer = {"id":0,"f_name" : "Volunteer",  "l_name": " ", "enabled" : true, "period" :period, "coefficient": 1};

    //We just want the people in this period.
    let studentsSelectable = studentsCopy.filter((value,index,array) => {return (array[index]["period"] === period && array[index]["enabled"] && !array[index]["absent"]);});

    //Stick Volunteer at the beginning if enabled
    if (userPreferences["allowVolunteers"]) {studentsSelectable.unshift(volunteer);}

    //Pop enough off the lastID list (preferences can change)
    let present = Object.keys(studentsSelectable).length;
    $("#statusBar").text("Total Present: " + present);
    $("#statusBar").append(userPreferences["allowVolunteers"] ? " (including Volunteer)": " ");



    //People in the lastID, less the last one we just popped off, are out.
    //This has to go after the stuff above because those people will not be included again.
    //These people are only temporarily out so we need to adjust the length of lastID accordingly
    for (let i=0; i < Object.keys(lastID).length; i++) {
        for (let j = Object.keys(studentsSelectable).length - 1; j > -1;j--) {
            if (studentsSelectable[j]["id"] === lastID[i]) {
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
    while (lastID.length >= present) {
        lastID.pop();
    }

    //If the user preference goes lower, we can go lower.
    while (lastID.length >= userPreferences["minimumBetween"]) {
        lastID.pop();
    }
    lastID.unshift(winner);
    for (let i=0;i < Object.keys(studentsSelectable).length; i++) {
        if (winner === studentsSelectable[i]["id"]) {
            let output =  studentsSelectable[i]["f_name"];
            if (userPreferences.nameSelection === 3) {
                output += " ";
                output += studentsSelectable[i]["l_name"];
            }
            if (userPreferences.nameSelection === 5) {
                output += " ";
                output += studentsSelectable[i]["l_name"][0];
                if(i!==0) {
                    output +=".";
                }
            }
            return output;

        }
    }

//throw("Bummer.");

}