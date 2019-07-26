function writePrefs() {
    $.post("random.php",
        {
            action: "updatePrefs",
            defaultPeriod: userPreferences["defaultPeriod"],
            allowVolunteers: userPreferences["allowVolunteers"],
            allowRepeats: userPreferences["allowRepeats"],
            numPeriods: userPreferences["numPeriods"],
            minimumBetween: userPreferences["minimumBetween"],
            nameSelection: userPreferences["nameSelection"]
        });
}

function toggleVolunteers()
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
    writePrefs();
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
