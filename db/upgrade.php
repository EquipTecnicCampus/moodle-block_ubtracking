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

function xmldb_block_ubtracking_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2013060200) {
        // Define field completiondiscussions to be added to cafeteria
        $table = new xmldb_table('block_ubtracking_grpactiv');
        // <FIELD NAME="instanceid" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" SEQUENCE="false" COMMENT="id on typetable" PREVIOUS="type"/>

        $userid = new xmldb_field('userid');
        $userid->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'groupid');

        if (!$dbman->field_exists($table, $userid)) {
            $dbman->add_field($table, $userid);
        }

    }

    if ($oldversion < 2013060201) {
        // Define field completiondiscussions to be added to cafeteria
        $table = new xmldb_table('block_ubtracking_grpactiv');
        // <FIELD NAME="instanceid" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" SEQUENCE="false" COMMENT="id on typetable" PREVIOUS="type"/>

        $courseid = new xmldb_field('courseid');
        $courseid->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');

        if (!$dbman->field_exists($table, $courseid)) {
            $dbman->add_field($table, $courseid);
        }

    }

        return true;

}