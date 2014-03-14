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

global $CFG;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/moodlelib.php');

class initialeval_form extends moodleform {

    public function definition() {
        global $DB, $CFG;

        $mform =& $this->_form;
        $courseid = $this->_customdata;

        $mform->addElement('header', 'quizheader', get_string('selectquiz', 'block_ubtracking'));

        $results = $DB->get_records('quiz', array('course' => $courseid));

        $quizes = array(''=>get_string('select'));
        foreach ($results as $result) {
            $quizes[$result->id] = $result->name;
        }
        $mform->addElement('select', 'quiz', get_string('quizes', 'block_ubtracking'), $quizes);
        $mform->addRule('quiz', null, 'required', null, 'client');

        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'courseid');

        $this->add_action_buttons();
    }
}