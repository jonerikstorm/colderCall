function selectStudent2 (period) {
    // Pick from the list unless they are disabled, unless we don't want repeats and they are the most recent. Add volunteer to the list if option is enabled.
    let studentsCopy = JSON.parse(JSON.stringify(students));
    let studentsSelectable = [{"id":0,"f_name" : "Volunteer",  "l_name": " ", "enabled" : false, "period" :period, "coefficient": 1},{"id":1}];
    studentsCopy.forEach(function (item, index, array) {item.period === period ? studentsSelectable[index + 1] = item : "";});


    // How will we adjust the volunteer's coefficent? by period?
    studentsSelectable[0]["coefficient"] = userPreferences["allowVolunteers"] === true;

    // Do we stil even need this pref? allowRepeats false = minimumBetween 0
    userPreferences["allowRepeats"] === false ? lastID.forEach(function (item, index, array) {item.id === studentsSelectable[index]["id"] ? studentsSelectable[index]["coefficient"] = 0: ""}):"";

    // For this run only, turn this student's coefficient to zero if absent or disabled.
    for (let i in studentsSelectable) {
        !studentsSelectable[i]["enabled"] || studentsSelectable[i]["absent"] ? studentsSelectable[i]["coefficient"] = 0 : "";
    }

    // Instead of using the indexes, we'll go by ID so it's easier to copy to lastID
    let selectArray = new Array();
    let k = 0;
    for (let i in studentsSelectable) {
        for (let j = 0; j < studentsSelectable[i]["coefficient"]; j++) {

            selectArray[k] = studentsSelectable[i]["id"];
            k++;

        }
    }
    let winner = Math.floor(Math.random() * Math.floor(Object.keys(selectArray).length));
    //do we want this to persist? Maybe in $_SESSION?
    while (lastID.length >= userPreferences["minimumBetween"])
        {lastID.pop();}
   lastID.push(winner);
    for (let i=0;i < Object.keys(studentsSelectable).length; i++) {
        if (winner === studentsSelectable[i]["id"]) {
            let output =  studentsSelectable[i]["f_name"];
            if (userPreferences.includeLastName) {
                output += " ";
                output += studentsSelectable[i]["l_name"];}
            if (userPreferences.includeLastInitial) {
                output += " ";
                output += studentsSelectable[i]["l_name"][0];
                output += ".";
                }
            return output;
        }
    }

throw("Bummer.");

}
