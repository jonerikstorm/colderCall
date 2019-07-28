<?php


function handlepost() {
//all the SQLite3 queries we will use for AJAX
    $answer_sql = "INSERT INTO `ANSWERS` (`timestamp`,`student_id`,`correct`) VALUES( :timestamp, :student_id, :isCorrect);";
    $incrementCorrect_sql = "UPDATE `STUDENTS` SET `correct` = :correcto WHERE `id`=:id;";
    $incrementIncorrect_sql = "UPDATE `STUDENTS` SET `incorrect` = :incorrecto WHERE `id` = :id;";
    $updateGlobalPrefs_sql = "UPDATE `globalPreferences` SET `numPeriods` = :numPeriods, `defaultPeriod` = :defaultPeriod WHERE `id` = 0;";
    $updatePeriodPrefs_sql = "UPDATE `periodPreferences` SET `allowVolunteers` = :allowVolunteers, `minimumBetween`= :minimumBetween, `nameSelection` = :nameSelection WHERE `id`= :period;";
    $writeStudent_sql = "UPDATE `STUDENTS` SET `coefficient` = :coefficient, `enabled` = :enabled, `absent` = :absent, `absentDate` = :date WHERE `id` = :id;";
    $lastID_sql = "REPLACE INTO `globalPreferences` (`lastID`) VALUES (:lastID);";

    $timeZone = new DateTimeZone('America/Los_Angeles');
    $dt = new DateTime();
    $dt->setTimezone($timeZone);
    //add features that makes it so that nothing but this page on this server can POST

    $post_db = new PDO("sqlite:coldcalls.sqlite3");
    switch ($_POST["action"]) {
        case "correct":
            $post_db->prepare($incrementCorrect_sql)->execute(['correcto' => $_POST['correcto'], 'id' => $_POST['id']]);
            $post_db->prepare($answer_sql)->execute(['student_id' => $_POST['student_id'], 'timestamp' => $dt->format(DateTimeInterface::W3C), 'isCorrect' => $_POST['isCorrect']]);
            exit;
            break;
        case "incorrect":
            $post_db->prepare($incrementIncorrect_sql)->execute(['incorrecto' => $_POST['incorrecto'], 'id' => $_POST['id']]);
            $post_db->prepare($answer_sql)->execute(['student_id' => $_POST['student_id'], 'timestamp' => $dt->format(DateTimeInterface::W3C ), 'isCorrect' => $_POST['isCorrect']]);
            exit;
            break;
        case "updateGlobalPrefs":
            $post_db->prepare($updateGlobalPrefs_sql)->execute(['numPeriods' => $_POST['numPeriods'], 'defaultPeriod' => $_POST['defaultPeriod']]);
            exit;
            break;
        case "updatePeriodPrefs":
            $post_db->prepare($updatePeriodPrefs_sql)->execute(['period' => $_POST['period'], 'minimumBetween' => $_POST['minimumBetween'],'nameSelection' => $_POST['nameSelection'], 'allowVolunteers' => $_POST['allowVolunteers']]);
            exit;
            break;
        case "writeLastID":
            $post_db->prepare($lastID_sql)->execute(['lastID' => $_POST['lastID']] );
            exit;
            break;
        case "writeStudent":
            $db_writeStudentArray = ['coefficient' => $_POST['coefficient'],
                'absent' => $_POST['absent'],
                'enabled' => $_POST['enabled']];
            if ($_POST['absent']) {
                array_push($db_writeStudentArray, ['date'=> $dt->format('Y-m-d')]);
            }
            else {
                array_push($db_writeStudentArray, ['date' => null]);
            }
            $post_db->prepare($writeStudent_sql)->execute($db_writeStudentArray);
            exit;
            break;
    }
}
