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
            if (userPreferences.nameSelection === "3") {
                output += " ";
                output += studentsSelectable[i]["l_name"];
            }
            if (userPreferences.nameSelection === "5") {
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
