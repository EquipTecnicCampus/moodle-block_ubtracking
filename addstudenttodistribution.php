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
require_once('lib.php');

global $DB, $OUTPUT, $CFG;

$id = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$groupsize = required_param('groupsize', PARAM_INT);
$lvlngroups = required_param('lvlngroups', PARAM_INT);
$type = required_param('type', PARAM_RAW);
$groupid = required_param('g', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));

require_login();

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/addstudenttodistribution.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');

$stradduser = get_string('addusersdistribution', 'block_ubtracking');
$PAGE->set_title($stradduser);
$PAGE->navbar->add($stradduser);

// comprobamos los permisos
if (!has_capability('block/ubtracking:teacherview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}

echo $OUTPUT->header();

// si hay userid por parámetro lo metemos en la distro
if ($userid) {
    // si se confirma la clave de sesion se borran de la base de datos

    if (confirm_sesskey()) { // borramos todos los grupos de las distros (no importa type) -> comentarios para type concreto
        $record = new Object();
        if ($DB->get_record('block_ubtracking_members', array('groupid'=>$groupid, 'userid'=>$userid))) {
            echo $OUTPUT->notification(get_string('useradderror', 'block_ubtracking'), 'error');
        } else {
            $record->groupid = $groupid;
            $record->userid = $userid;
            $DB->insert_record('block_ubtracking_members', $record);
            echo $OUTPUT->notification(get_string('useraddok', 'block_ubtracking'), 'notifysuccess');
        }
    }
}


// formulario
// Miramos a ver si hay usuarios que no estén repartidos

$query = "SELECT u.*
          FROM {context} c, {role_assignments} ra, {user} u
          WHERE c.contextlevel = 50 AND c.instanceid = $courseid AND ra.contextid = c.id AND u.id = ra.userid AND u.id not in(
          SELECT utm.userid
          FROM {block_ubtracking_members} utm,
            {block_ubtracking_groups} utg
            WHERE utg.course = $courseid AND utm.groupid = utg.id AND utg.type = '$type')
          order by u.lastname";

$results = $DB->get_records_sql($query);

$users = array();

$back = '<a href="'.$CFG->wwwroot.
     '/blocks/ubtracking/studentdistribution.php?id='.$id.
     '&courseid='.$COURSE->id.'&groupsize='.$groupsize.
     '&lvlngroups='.$lvlngroups.'">'.
     get_string('back').'</a>';

// En esta variable ponemos los usuarios con sus calificaciones: array(usuario=>calificación)

if ($results) {
    $nusers = count($results); // número de usuarios
    foreach ($results as $user) {
        $users[$user->id] = fullname($user);
    }
} else {
    echo $OUTPUT->box(get_string("nousersnodistributed_".$type, "block_ubtracking"), 'generalbox boxaligncenter boxwidthnormal');
    echo $back;
    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->box(get_string("usersnodistributed_".$type, "block_ubtracking", $nusers), 'generalbox boxaligncenter boxwidthnormal');

// Selector para añadir usuarios
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');

$selecturl = new moodle_url($PAGE->url, array('id'=>$id, 'courseid'=>$courseid,
    'groupsize'=>$groupsize, 'lvlngroups'=>$lvlngroups, 'type'=>$type, 'g'=>$groupid, 'sesskey'=>sesskey()));
$gname = $DB->get_field('block_ubtracking_groups', 'name', array('id'=>$groupid));

$stradduser = get_string('addusertogroup', 'block_ubtracking').'"'.$gname.'": </br>';
echo ut_popup_form($selecturl, 'userid', '', $stradduser, $users);

echo $OUTPUT->box_end();

echo $back;

echo $OUTPUT->footer();