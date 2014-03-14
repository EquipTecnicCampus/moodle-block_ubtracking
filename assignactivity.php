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
$cmid = optional_param('cmid', 0, PARAM_INT); // course module id
$groupid = optional_param('groupid', 0, PARAM_INT); // course module id
$type = optional_param('type', 0, PARAM_ALPHA);
$delete = optional_param('delete', 0, PARAM_INT); // course module id

$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));

require_login($course, false);

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/assignactivity.php?id='.$id.'&courseid='.$courseid);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('assignactivity', 'block_ubtracking'));
$PAGE->navbar->add(get_string('assignactivity', 'block_ubtracking'));

$statusmsg = '';
$statusclass = 'notifysuccess';

if (!has_capability('block/ubtracking:teacherview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}

$bannedmodules = array("'forum'", "'resource'", "'label'");
$form = new assignactivity_form('', array('id' => $id, 'courseid' => $course->id, 'bannedmodules' => $bannedmodules));

if ($delete and confirm_sesskey()) {
    $DB->delete_records('block_ubtracking_grpactiv', array('id' => $delete));
    redirect($CFG->wwwroot.'/blocks/ubtracking/assignactivity.php?id='.$id.'&courseid='.$courseid,
            get_string('deletesuccess', 'block_ubtracking'));
}

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
} else if ($fromform = $form->get_data()) {
    $intodb = new stdClass();
    $intodb->cmid = $fromform->cmid;
    $intodb->activitygroup = $fromform->activitygroup;
    $intodb->courseid = $courseid;
    $levelok = !empty($fromform->level);
    $balancedok = !empty($fromform->balanced);
    $userok = !empty($fromform->user);
    if ($levelok) {
        $intodb->groupid = $fromform->level;
        $intodb->userid = 0;
    }
    if ($balancedok) {
        $intodb->groupid = $fromform->balanced;
        $intodb->userid = 0;
    }
    if ($userok) {
        $intodb->userid = $fromform->user;
        $intodb->groupid = 0;
    }
    if (!$DB->insert_record('block_ubtracking_grpactiv', $intodb)) {
        print_error('insert_error', 'block_ubtracking');
    }
    $statusmsg = get_string('changessaved');
    $statusclass = 'notifysuccess';
    // guardados

    redirect($CFG->wwwroot.'/blocks/ubtracking/assignactivity.php?id='.$id.'&courseid='.$courseid,
            get_string('assignsuccess', 'block_ubtracking'));

}


echo $OUTPUT->header();

if ($statusmsg) {
    echo $OUTPUT->notification($statusmsg, $statusclass).'<br/>';
}

$form->display();

echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');

$table = new html_table();

$table->head = array(get_string('group'), get_string('activities'));
$table->align = array('center', 'left');
$table->width = '100%';
$table->data = array();
$sql = 'SELECT a.id, a.cmid, g.name as groupname, g.id as groupid '.
        'FROM {block_ubtracking_groups} g JOIN {block_ubtracking_grpactiv} a ON g.id = a.groupid WHERE g.course = :courseid ORDER BY groupname ASC';

if ($activities = $DB->get_records_sql($sql, array('courseid' => $course->id))) {
    $currentmods = array();
    $groupsnames = array();

    foreach ($activities as $activity) {
        $currentmods[$activity->groupid][] = array('id' => $activity->id, 'cmid' =>$activity->cmid);
        $groupsnames[$activity->groupid] = $activity->groupname;
    }

    $strdelete = get_string('delete');

    foreach ($currentmods as $groupid => $mods) {
        $links = '';
        foreach ($mods as $mod) {
            $cm = get_coursemodule_from_id('', $mod['cmid']);
            $links .= '<p>' . $cm->name.' <a title="'.$strdelete.'" href="'.$FULLME.'&amp;delete='. $mod['id'].'&amp;sesskey='.sesskey().'"><img'.
                   ' src="'.$OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="'.$strdelete.'" /></a></p>';
        }
        $groupname = html_writer::link("", "", array('name'=>'group'.$groupid)) .
            html_writer::link("studentdistribution.php?id=$id&courseid=$courseid#group$groupid", $groupsnames[$groupid]);
        $table->data[] = array($groupname, $links);
    }

    echo html_writer::table($table);
}
echo $OUTPUT->box_end();

echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');
// tabla de actividades asociadas a alumnos
$table = new html_table();

$table->head = array(get_string('users'), get_string('activities'));
$table->align = array('center', 'left');
$table->width = '100%';
$table->data = array();

if ($activities = $DB->get_records('block_ubtracking_grpactiv', array('courseid' => $course->id, 'groupid' => 0))) {
    $currentmods = array();
    $usernames = array();

    foreach ($activities as $activity) {
        $currentmods[$activity->userid][] = array('id' => $activity->id, 'cmid' =>$activity->cmid);
        $user = $DB->get_record('user', array('id' => $activity->userid));
        $usernames[$activity->userid] = fullname($user);
    }

    $strdelete = get_string('delete');

    foreach ($currentmods as $userid => $mods) {
        $links = '';
        foreach ($mods as $mod) {
            $cm = get_coursemodule_from_id('', $mod['cmid']);
            $links .= '<p>' . $cm->name.' <a title="'.$strdelete.'" href="'.$FULLME.'&amp;delete='. $mod['id'].'&amp;sesskey='.sesskey().'"><img'.
                   ' src="'.$OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="'.$strdelete.'" /></a></p>';
        }
        $groupname = html_writer::link("", "", array('name'=>'group'.$userid)) .
            html_writer::link($CFG->wwwroot."/user/profile.php?id=$userid", $usernames[$userid]);
        $table->data[] = array($groupname, $links);
    }

    echo html_writer::table($table);

}

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
