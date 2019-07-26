function writeStudent(id)
{
    $.post("random.php",
        {
            action: "writeStudent",
            id: id,
            enabled: students[getIndexByID(id)]["enabled"],
            absent: students[getIndexByID(id)]["absent"],
            coefficient: students[getIndexByID(id)]["coefficient"]
        });
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
