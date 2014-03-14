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

$id = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$type = required_param('type', PARAM_TEXT);
$groupsize = required_param('groupsize', PARAM_INT);
$lvlngroups = required_param('lvlngroups', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$onlydel = optional_param('onlydel', 0, PARAM_INT);
$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));

require_login();

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/delete_groups.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');

$strdel = get_string('delete_groups', 'block_ubtracking');
if ($onlydel) {
    $strdel = get_string('delete_groups_'.$type, 'block_ubtracking');
}
$PAGE->set_title($strdel);
$PAGE->navbar->add($strdel);

// comprobamos los permisos
if (!has_capability('block/ubtracking:teacherview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}

if (!$confirm) {
    // imprimimos la pregunta de confirmacion con sus botones de yes o no
    $strconfirm = get_string('confirmdelete', 'block_ubtracking');
    if (!$onlydel) {
        $optionsyes = array ('id'=>$id, 'courseid'=>$courseid, 'type'=>$type,
                            'groupsize'=>$groupsize, 'lvlngroups'=>$lvlngroups,
                            'confirm'=>1, 'sesskey'=>sesskey());
    } else {
        $strconfirm = get_string('confirmdelete_'.$type, 'block_ubtracking');
        $optionsyes = array ('id'=>$id, 'courseid'=>$courseid, 'type'=>$type,
                            'groupsize'=>$groupsize, 'lvlngroups'=>$lvlngroups,
                            'confirm'=>1, 'sesskey'=>sesskey(), 'onlydel'=>1);
    }
    $continue = new moodle_url('/blocks/ubtracking/delete_groups.php', $optionsyes);
    $optionsno = array('id'=>$id, 'courseid'=>$courseid, 'groupsize'=>$groupsize, 'lvlngroups'=>$lvlngroups);
    $cancel = new moodle_url($CFG->wwwroot.'/blocks/ubtracking/studentdistribution.php', $optionsno);

} else {
    // si se confirma la clave de sesion se borran de la base de datos

    if (confirm_sesskey()) { // borramos todos los grupos de las distros (no importa type) -> comentarios para type concreto
        $groups = $DB->get_records('block_ubtracking_groups',
                                   array('course'=>$courseid, 'type'=>$type));

        $DB->delete_records('block_ubtracking_groups', array('course'=>$courseid, 'type'=>$type));


        foreach ($groups as $group) {
            // Mcagigas
            // Se unifican las tablas no es necesario discriminarlas.
            $membersstr = 'block_ubtracking_members';
            /*$membersstr = 'block_ubtracking_lvl_members';
            if ($group->type == 'balanced') {
                $membersstr = 'block_ubtracking_bal_members';
            }*/
            $DB->delete_records($membersstr, array('groupid'=>$group->id));

            // Elimino el prefijo de la tabla block_ubtracking_grpactiv
            // $DB->delete_records('mdl_block_ubtracking_grpactiv', array('groupid'=>$group->id));
            $DB->delete_records('block_ubtracking_grpactiv', array('groupid'=>$group->id));
            // Fin Mcagigas
        }
    }
    if (!$onlydel) {
        redirect($CFG->wwwroot.'/blocks/ubtracking/studentdistribution.php?id='.$id
                .'&courseid='.$courseid.'&groupsize='.$groupsize.'&lvlngroups='.$lvlngroups
                .'&type='.$type, '', 0);
    } else {
        redirect($CFG->wwwroot.'/blocks/ubtracking/studentdistribution.php?id='.$id
                .'&courseid='.$courseid.'&groupsize='.$groupsize.'&lvlngroups='.$lvlngroups
                .'&groupscfg=1', '', 0);
    }
}

echo $OUTPUT->header();

if (!$confirm) {
    echo $OUTPUT->heading(get_string('groupsexist', 'block_ubtracking'));
    echo $OUTPUT->confirm($strconfirm, $continue, $cancel);
}

echo $OUTPUT->footer();