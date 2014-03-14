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

class assignactivity_form extends moodleform {

    public function definition() {
        global $DB, $CFG;

        $mform =& $this->_form;
        $id = $this->_customdata['id'];
        $courseid = $this->_customdata['courseid'];
        $bannedmodules = $this->_customdata['bannedmodules'];

        $activities = ut_get_activities($bannedmodules, $courseid);

        $mform->addElement('select', 'cmid', get_string('activity'), $activities);

        $groupsgroup = array();

        if ($results = $DB->get_records('block_ubtracking_groups', array('course'=>$courseid, 'type'=>'level'))) {
            $lvlgroups = array(''=>'');
            foreach ($results as $result) {
                $lvlgroups[$result->id] = $result->name;
            }
            $mform->addElement('select', 'level', get_string('level', 'block_ubtracking'), $lvlgroups);
            $mform->disabledif ('level', 'balanced', 'neq', '');
            $mform->disabledif ('level', 'user', 'neq', '');
        }

        if ($results = $DB->get_records('block_ubtracking_groups', array('course'=>$courseid, 'type'=>'balanced'))) {
            $balgroups = array(''=>'');
            foreach ($results as $result) {
                $balgroups[$result->id] = $result->name;
            }
            $mform->addElement('select', 'balanced', get_string('balanced', 'block_ubtracking'), $balgroups);
            $mform->disabledif ('balanced', 'level', 'neq', '');
            $mform->disabledif ('balanced', 'user', 'neq', '');
        }
        $query = "SELECT u.*
          FROM {context} c, {role_assignments} ra, {user} u
          WHERE c.contextlevel = 50 AND c.instanceid = $courseid AND ra.contextid = c.id AND u.id = ra.userid
          order by u.lastname";

        if ($results = $DB->get_records_sql($query)) {
            $users = array(''=>'');
            foreach ($results as $user) {
                $users[$user->id] = fullname($user);
            }

            $mform->addElement('select', 'user', get_string('users', 'block_ubtracking'), $users);
            $mform->disabledif ('user', 'balanced', 'neq', '');
            $mform->disabledif ('user', 'level', 'neq', '');
        }

        $mform->addGroup($groupsgroup, 'groupsgroup', '', '', false);

        $mform->addElement('select', 'activitygroup', get_string('groupactivities', 'block_ubtracking'), range(0, 100));
        $mform->addElement('hidden', 'id', $id);
        $mform->addElement('hidden', 'courseid', $courseid);

        $this->add_action_buttons();
    }
    public function validation($grpactiv) {
        $err = array();

        if (empty($grpactiv['level']) && empty($grpactiv['balanced']) && empty($grpactiv['user'])){
            $err['level'] = get_string('required');
        }

        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }

    }
}