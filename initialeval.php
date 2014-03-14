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

require_once($CFG->dirroot.'/blocks/ubtracking/initialeval_form.php');

$id = required_param('id', PARAM_INT); // blockid
$courseid = required_param('courseid', PARAM_INT); // courseid
$quizid = optional_param('quizid', 0, PARAM_INT); // quizid when updating

$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));

require_login();

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/initialeval.php?id='.$id.'&courseid='.$courseid);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('initialeval', 'block_ubtracking'));
$PAGE->navbar->add(get_string('initialeval', 'block_ubtracking'));

$statusmsg = '';

if (!has_capability('block/ubtracking:teacherview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}

$course_quiz = $DB->get_record('block_ubtracking_coursequiz', array('course'=>$courseid));

$form = new initialeval_form('', $courseid);

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('cancel', 'block_ubtracking'));
} else if ($fromform = $form->get_data()) {

    $intodb = new stdClass();
    $intodb->quiz = $fromform->quiz;

    if (!empty($course_quiz)) {
        $intodb->id = $course_quiz->id;

        if (!$DB->update_record('block_ubtracking_coursequiz', $intodb)) {
            print_error('update_error', 'block_ubtracking');
        }
    } else {
        $intodb->course = $fromform->courseid;
        if (!$DB->insert_record('block_ubtracking_coursequiz', $intodb)) {
            print_error('insert_error', 'block_ubtracking');
        }
    }

    // redirect($PAGE->url,get_string('initialevalsuccess', 'block_ubtracking'));
    $statusmsg = get_string('changessaved');

} else {
    $toform = new stdClass();
    $toform->id = $id;
    $toform->courseid = $courseid;

    if (!empty($course_quiz)) {
        $toform->quiz = $course_quiz->quiz;
    }

    $form->set_data($toform);
}
echo $OUTPUT->header();

if ($statusmsg) {
    echo $OUTPUT->notification($statusmsg, 'notifysuccess');
}

$form->display();

echo $OUTPUT->footer();
