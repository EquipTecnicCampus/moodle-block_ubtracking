<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('../../config.php');

global $DB, $OUTPUT, $CFG;

require_once($CFG->dirroot.'/blocks/ubtracking/assignactivity_form.php');
require_once($CFG->dirroot.'/blocks/ubtracking/lib.php');

$id = required_param('id', PARAM_INT); // blockid
$courseid = required_param('courseid', PARAM_INT); // courseid
$filteruser = optional_param('filteruser', 0, PARAM_INT);

$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));

require_login($course, false);

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/statistics.php?id='.$id.'&courseid='.$courseid);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('statistics', 'block_ubtracking'));
$PAGE->navbar->add(get_string('statistics', 'block_ubtracking'));

$statusmsg = '';
$statusclass = 'notifysuccess';

if (!has_capability('block/ubtracking:teacherview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}

$filtergroup = array();
if ($filteruser) {
    $groupsm = $DB->get_records('block_ubtracking_members', array('userid' => $filteruser));
    if (!empty($groupsm)) {
        foreach ($groupsm as $group) {
            $filtergroup[] = $group->groupid;
        }
    }
}

echo $OUTPUT->header();
// echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');


//n.firstChild.childNodes[3];
$infojs = '<script language="javascript"> '.
    'function mostrar(elem) {
        document.getElementById(elem.id).style.display = (document.getElementById(elem.id).style.display == "block")? "none" : "block";
    }
    </script>';
echo $infojs;
// grupos nivelados:
$table = new html_table();
$sql = "SELECT DISTINCT activitygroup FROM {block_ubtracking_grpactiv} ga,  {block_ubtracking_groups} g ".
    "WHERE g.id = ga.groupid AND type = :type AND course = :course ORDER BY activitygroup ASC";

if ($balanced = $DB->get_records('block_ubtracking_groups', array('type' => 'balanced', 'course' => $courseid))) {

    $groups = $DB->get_records_sql($sql,  array('course'=>$courseid, 'type'=>'balanced'));
    $strga = get_string('shortactivitygroup', 'block_ubtracking');
    $table->head[] = get_string('group');

    $table->align = array('center');
    $table->width = '100%';
    $table->data = array();

    foreach ($groups as $g) {
        $table->head[] = $strga.$g->activitygroup;
        $table->align[] = 'left';
    }

    $ncol = count($groups) + 1;
    if ($ncol > 1) {
        $percent = 100/$ncol;
        for ($i=0; $i < $ncol; $i++) {
            $table->size[] = $percent.'%';
        }
    }

    foreach ($balanced as $bal) {
        if (!empty($filtergroup) && !in_array($bal->id, $filtergroup)) {
            continue;
        }
        $row = array();
        $row[] = html_writer::link("$CFG->wwwroot/blocks/ubtracking/assignactivity.php?id=$id&courseid=$courseid#group$bal->id", $bal->name, array('target' => '_blank'));
        foreach ($groups as $g) {
            $row[] = ut_get_semaphore_group($courseid, $bal->id, 'balanced', $g->activitygroup);
        }
        $table->data[] = $row;
    }
    if (!empty($table->data)) {
        echo $OUTPUT->heading(get_string('balanced', 'block_ubtracking'));
        echo html_writer::table($table);
    }
}

// grupos nivelados:
$table = new html_table();
$sql = "SELECT DISTINCT activitygroup FROM {block_ubtracking_grpactiv} ga,  {block_ubtracking_groups} g ".
    "WHERE g.id = ga.groupid AND type = :type AND course = :course ORDER BY activitygroup ASC";

if ($leveled = $DB->get_records('block_ubtracking_groups', array('type' => 'level', 'course' => $courseid))) {

    $groups = $DB->get_records_sql($sql,  array('course'=>$courseid, 'type'=>'level'));
    $strga = get_string('shortactivitygroup', 'block_ubtracking');

    $table->align = array('center');
    $table->width = '100%';
    $table->data = array();

    $table->head[] = get_string('group');
    foreach ($groups as $g) {
        $table->head[] = $strga.$g->activitygroup;
        $table->align[] = 'left';
    }

    $ncol = count($groups) + 1;
    if ($ncol > 1) {
        $percent = 100/$ncol;
        for ($i=0; $i < $ncol; $i++) {
            $table->size[] = $percent.'%';
        }
    }

    foreach ($leveled as $lvl) {
        if (!empty($filtergroup) && !in_array($lvl->id, $filtergroup)) {
            continue;
        }
        $row = array();
        $row[] = html_writer::link("$CFG->wwwroot/blocks/ubtracking/assignactivity.php?id=$id&courseid=$courseid#group$lvl->id", $lvl->name, array('target' => '_blank'));
        foreach ($groups as $g) {
            $row[] = ut_get_semaphore_group($courseid, $lvl->id, 'level', $g->activitygroup);
        }
        $table->data[] = $row;
    }
    if (!empty($table->data)) {
        echo $OUTPUT->heading(get_string('level', 'block_ubtracking'));
        echo html_writer::table($table);
    }
}

// usuarios asignados individualmente:

if ($gausers = $DB->get_records('block_ubtracking_grpactiv', array('courseid' => $course->id, 'groupid' => 0))) {
    $table = new html_table();

    $table->align = array('center');
    $table->width = '100%';
    $table->data = array();

    // header:
    $strga = get_string('shortactivitygroup', 'block_ubtracking');
    $table->head[] = get_string('students');
    $gactivities = array();
    foreach ($gausers as $gaid => $gauser) {
        if (!in_array($strga.$gauser->activitygroup, $table->head)) {
            $table->head[] = $strga.$gauser->activitygroup;
            $gactivities[] = $gauser->activitygroup;
            $table->align[] = 'left';
        }
    }
    $ncol = count($gausers) + 1;
    if ($ncol > 1) {
        $percent = 100/$ncol;
        for ($i=0; $i < $ncol; $i++) {
            $table->size[] = $percent.'%';
        }
    }
    $users = array();
    foreach ($gausers as $gauser) {
        if ($filteruser and $filteruser != $gauser->userid) {
            continue;
        }
        if (in_array($gauser->userid, $users)) {
            continue;
        }
        $users[] = $gauser->userid;
        $user = $DB->get_record('user', array('id' => $gauser->userid));

        $row = array();
        $row[] = html_writer::link("$CFG->wwwroot/blocks/ubtracking/statistics.php?id=$id&courseid=$courseid&filteruser=$gauser->userid",
            fullname($user), array('target' => '_blank'));
        foreach ($gactivities as $g) {
            $row[] = ut_get_semaphore_user($courseid, $user->id, $g);
        }
        $table->data[] = $row;
    }
    if (!empty($table->data)) {
        echo $OUTPUT->heading(get_string('usersasign', 'block_ubtracking'));
        echo html_writer::table($table);
    }
}

// echo $OUTPUT->box_end();
echo $OUTPUT->footer();
