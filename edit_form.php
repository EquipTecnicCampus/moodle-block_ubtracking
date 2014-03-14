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

class block_ubtracking_edit_form extends block_edit_form {
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('html',  get_string('selectgroupsize', 'block_ubtracking'));

        $mform->addElement('text', 'config_balgroupsize', get_string('balgroupsize', 'block_ubtracking'));
        $mform->setDefault('config_balgroupsize', 5);
        $mform->setType('config_balgroupsize', PARAM_INT);

        $mform->addElement('text', 'config_lvlngroups', get_string('lvlgroupsize', 'block_ubtracking'));
        $mform->setDefault('config_lvlngroups', 5);
        $mform->setType('config_lvlngroups', PARAM_INT);

    }
}
