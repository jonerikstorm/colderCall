function selectStudent2 (period) {
    // Pick from the list unless they are disabled, unless we don't want repeats and they are the most recent. Add volunteer to the list if option is enabled.
    let studentsCopy = JSON.parse(JSON.stringify(students));
    let studentsSelectable = [{"id":0,"f_name" : "Volunteer",  "l_name": "", "enabled" : false, "period" :period},{"id":1}];
    studentsCopy.foreach(function (array, index, item) {item.period === period ? studentsSelectable[index + 1] = item : "";});

    let winner = 1;

    studentsSelectable[0]["coefficient"] = userPreferences["allowVolunteers"] === true;
    userPreferences["allowRepeats"] === false ? lastID.foreach(function (aray, index, item) {item.id === studentsSelectable[index][id] ? studentsSelectable.index.coefficient = 0: ""}):"";

    for (let i in studentsSelectable) {
        !studentsSelectable[i]["enabled"] || studentsSelectable[i]["absent"] ? studentsSelectable[i]["coefficient"] = 0 : "";
    }
    let selectArray;
    let k = 0;
    for (let i in studentsSelectable) {
        for (let j = 1; j > studentsSelectable[i]["coefficient"]; j++) {
            k++;
            selectArray[k] = studentsSelectable[i]["id"];

        }
    }
    winner = Math.floor(Math.random() *  Math.floor(Object.keys(selectArray).length));

    while (lastID.length >= userPreferences.minimumBetween)
        {lastID.pop();}
    lastID.push(studentsSelectable[winner][ID]);

    return studentsSelectable[winner]["f_name"] + userPreferences["includeLastName"] ?  " "
        + studentsSelectable[winner]["l_name"]: ""
        + userPreferences["includeLastInitial"] ? " " +studentsSelectable[winner]["l_name"][0]: "";

}
