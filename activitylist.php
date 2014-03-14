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

global $DB, $OUTPUT, $CFG, $USER;

$id = required_param('id', PARAM_INT); // blockid
$courseid = required_param('courseid', PARAM_INT); // courseid

require_once($CFG->dirroot.'/blocks/ubtracking/lib.php');

$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));
require_login();

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/activitylist.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('activitylist', 'block_ubtracking'));
$PAGE->navbar->add(get_string('activitylist', 'block_ubtracking'));

if (!has_capability('block/ubtracking:studentview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}

$lvl_table = ut_get_activities_by_type($USER->id, $courseid, 'level');
// Mcagigas
// Existía un error en el string 'balance' --> 'balanced'
// $bal_table = ut_get_activities_by_type($USER->id, $courseid, 'balance');
$bal_table = ut_get_activities_by_type($USER->id, $courseid, 'balanced');
// Fin Mcagigas
$user_table = ut_get_activities_by_type($USER->id, $courseid, '');

echo $OUTPUT->header();


echo $OUTPUT->heading(get_string('balanced', 'block_ubtracking'), 2, 'left');
if (!empty($bal_table->data)) {
        echo html_writer::table($bal_table);
} else {
    echo $OUTPUT->notification(get_string('noactivitiesgroup', 'block_ubtracking'), 'error');
}

echo $OUTPUT->heading(get_string('level', 'block_ubtracking'), 2, 'left');
if (!empty($lvl_table->data)) {
    echo html_writer::table($lvl_table);
} else {
    echo $OUTPUT->notification(get_string('noactivitiesgroup', 'block_ubtracking'), 'error');
}

echo $OUTPUT->heading(get_string('usersasign', 'block_ubtracking'), 2, 'left');
if (!empty($user_table->data)) {
    echo html_writer::table($user_table);
} else {
    echo $OUTPUT->notification(get_string('nouseractivitiesasign', 'block_ubtracking'), 'error');
}

echo $OUTPUT->footer();