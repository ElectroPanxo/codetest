<?php
global $translator;

function renderMenuIcon($iconClass, $textKey, $iconType){
    global $translator;
    $textVal = $translator->trans($textKey);
    $iconTypStr = 'fa';
    if(isset($iconType)){
        $iconTypStr = $iconType;
    }
    return "<span class='$iconTypStr $iconClass marginIcon' aria-hidden='true'></span> <span>$textVal</span>";
};

function addMenuItem($menu, $iconClass, $iconType, $textKey, $submenuOrUrl){
    $menu->addRight(renderMenuIcon($iconClass, $textKey, isset($iconType) ? $iconType : null), $submenuOrUrl);
}

function renderMenuEntry($textKey, $to){
    global $translator;
    return new \Tsugi\UI\MenuEntry($translator->trans($textKey), $to);
}

$menu = false;

if ($USER->instructor) {
    $menu = new \Tsugi\UI\MenuSet();
    $menu->setHome('Code Test', 'index.php');
    if ('student-home.php' != basename($_SERVER['PHP_SELF'])) {
        addMenuItem($menu, 'fa-user-graduate', null, 'navbarmenu.student.view', 'student-home.php');
        addMenuItem($menu, 'fa-clipboard-check', null, 'navbarmenu.grade', 'grade.php');

        $resultsSubmenu = [
            renderMenuEntry("navbarmenu.results.by.student", "results-student.php"),
            renderMenuEntry("navbarmenu.results.by.exercise", "results-exercise.php"),
            renderMenuEntry("navbarmenu.results.download", "results-download.php")
        ];
        addMenuItem($menu, 'fa-poll-h', null, 'navbarmenu.results', $resultsSubmenu);

        $usagesSubmenu = [
            renderMenuEntry("navbarmenu.usage.by.student", "usage-student.php"),
            renderMenuEntry("navbarmenu.usage.by.exercise", "usage-exercise.php"),
        ];
        addMenuItem($menu, 'fa-comments', null, 'navbarmenu.usages', $usagesSubmenu);
        
        $buildSubmenu = [
            renderMenuEntry("navbarmenu.exercises.create", 'create-exercise.php'),
            renderMenuEntry("navbarmenu.exercises.list", 'exercises-list.php'),
            renderMenuEntry("navbarmenu.exercises.authorkit", 'ak-projects-list.php'),
            renderMenuEntry("navbarmenu.exercises.codetest", 'exercises-management.php'),
        ];
        addMenuItem($menu, 'fa-file-code-o', null, 'navbarmenu.build', $buildSubmenu);
    } else {
        addMenuItem($menu, 'fa-sign-out-alt', null, 'navbarmenu.exit.student.view', 'instructor-home.php');
    }
}
