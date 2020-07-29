<?php

require_once('../config.php');
require_once('dao/CT_DAO.php');
require_once('dao/CT_Main.php');

use \Tsugi\Core\LTIX;
use \CT\DAO\CT_DAO;
use \CT\DAO\CT_Main;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CT_DAO = new CT_DAO();

$main = new CT_Main($_SESSION["ct_id"]);
$pointsPossible = $main->getPoints();

$students = $CT_DAO->getUsersWithAnswers($_SESSION["ct_id"]);
$studentAndDate = array();
foreach($students as $student) {
    $studentAndDate[$student["user_id"]] = new DateTime($CT_DAO->getMostRecentAnswerDate($student["user_id"], $_SESSION["ct_id"]));
}

$questions = $main->getQuestions();
$totalQuestions = count($questions);

include("menu.php");

// Start of the output
$OUTPUT->header();

include("tool-header.html");

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle('Grade', false, false);

?>
<h3>Set Points Possible <small>Default 100</small></h3>
<form class="form-inline" action="actions/UpdatePointsPossible.php" method="post">
    <div class="form-group">
        <label for="points_possible">Points Possible: </label>
        <input type="text" class="form-control" id="points_possible" name="points_possible" value="<?=$pointsPossible?>">
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
<h3>Grade Students</h3>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
        <th class="col-sm-5">Student Name</th>
        <th class="col-sm-2">Last Updated</th>
        <th class="col-sm-2">Completed</th>
        <th class="col-sm-3">Grade</th>
        </thead>
        <tbody>
<?php
// Sort students by mostRecentDate desc
arsort($studentAndDate);
foreach ($studentAndDate as $student_id => $mostRecentDate) {
    if (!$CT_DAO->isUserInstructor($CONTEXT->id, $student_id)) {
        $formattedMostRecentDate = $mostRecentDate->format("m/d/y") . " | " . $mostRecentDate->format("h:i A");
        $numberAnswered = $CT_DAO->getNumberQuestionsAnswered($student_id, $_SESSION["ct_id"]);
        $grade = $CT_DAO->getStudentGrade($_SESSION["ct_id"], $student_id);
        ?>
        <tr>
            <td><?= $CT_DAO->findDisplayName($student_id) ?></td>
            <td><?= $formattedMostRecentDate ?></td>
            <td><?= $numberAnswered . '/' . $totalQuestions ?></td>
            <td>
                <form class="form-inline" action="actions/GradeStudent.php" method="post">
                    <input type="hidden" name="student_id" value="<?=$student_id?>">
                    <div class="form-group">
                        <label>
                        <input type="text" class="form-control" name="grade" value="<?=$grade?>">/<?=$pointsPossible?>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-default">Update</button>
                </form>
            </td>
        </tr>
        <?php
    }
}
?>
        </tbody>
    </table>
</div>
<?php

echo ("</div>"); // End container

$OUTPUT->footerStart();

include("tool-footer.html");

$OUTPUT->footerEnd();
