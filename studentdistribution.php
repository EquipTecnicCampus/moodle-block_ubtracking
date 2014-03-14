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

require_once($CFG->dirroot.'/blocks/ubtracking/lib.php');

$id = required_param('id', PARAM_INT); // blockid
$courseid = required_param('courseid', PARAM_INT); // courseid

if ($editblock = optional_param('editblock', 0, PARAM_INT)) {
    $USER->editing = 1;
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid.'&sesskey='.sesskey().'&bui_editid='.$id);
}

$groupsize = optional_param('groupsize', 5, PARAM_INT);
$lvlngroups = optional_param('lvlngroups', 4, PARAM_INT);

$block = $DB->get_record('block_instances', array('id' => $id));
$blockconfig = unserialize(base64_decode($block->configdata));

if (!$groupsize and is_object($blockconfig)) {
    $groupsize = $blockconfig->balgroupsize;
}

if (!$lvlngroups and is_object($blockconfig)) {
    $groupsize = $blockconfig->lvlngroups;
}

$type = optional_param('type', '', PARAM_TEXT);

$context = get_context_instance(CONTEXT_BLOCK, $id);
$course = $DB->get_record('course', array('id'=>$courseid));

$statusmsg = '';

require_login();

// Mostrem capcelera
$PAGE->set_course($course);
$PAGE->set_url('/blocks/ubtracking/studentdistribution.php?id='.$id.'&courseid='.$courseid.'&groupsize='.$groupsize.'&lvlngroups='.$lvlngroups);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('studentdistribution', 'block_ubtracking'));
$PAGE->navbar->add(get_string('studentdistribution', 'block_ubtracking'));

if (!has_capability('block/ubtracking:teacherview', $context)) {
    print_error(get_string('nocapabilities', 'block_ubtracking'));
}



// Miramos a ver si hay usuarios que hayan hecho el quiz

$query = "  SELECT qg.*
            FROM {$CFG->prefix}quiz_grades qg, {$CFG->prefix}quiz q, {$CFG->prefix}user u,
                 {$CFG->prefix}block_ubtracking_coursequiz ucq
            WHERE ucq.quiz = qg.quiz AND u.id = qg.userid
            AND ucq.course = q.course AND ucq.course = $courseid
            AND q.id = ucq.quiz AND q.id = qg.quiz";

$results = $DB->get_records_sql($query);


// En esta variable ponemos los usuarios con sus calificaciones: array(usuario=>calificación)
$usergrades = array();

$nusers = count($results); // número de usuarios

if ($results) {
    foreach ($results as $result) {
        $usergrades[$result->userid] = $result->grade;
    }
}

asort($usergrades);

// si viene type, hay que crear los grupos
if (!empty($type)) {
    $groups = $DB->get_records('block_ubtracking_groups', array('course'=>$courseid, 'type'=>$type));

    if (!empty($groups)) { // comprobación de querer borrarlos y borrarlos, antes de volver
        redirect($CFG->wwwroot.'/blocks/ubtracking/delete_groups.php?id='.$id
                                .'&courseid='.$courseid.'&type='.$type.'&groupsize='.$groupsize
                                .'&lvlngroups='.$lvlngroups, '', 0);
    }

    // Mcagigas
    // Se unifican las tablas.
    if ($type =='balanced') { // creación de bal
        $statusmsg = get_string('changessaved');
        $basegroupname = get_string('group_balanced', 'block_ubtracking');
        $groups = ut_create_balanced_dist($usergrades, $nusers, $groupsize, $courseid);
        // $membersstr = 'block_ubtracking_bal_members';

    } else if ($type == 'level') { // creación de lvl
        $statusmsg = get_string('changessaved');
        $basegroupname = get_string('group_level', 'block_ubtracking');
        $groups = ut_create_level_dist($usergrades, $nusers, $lvlngroups, $courseid);
        // $membersstr = 'block_ubtracking_lvl_members';
    }
    $membersstr = 'block_ubtracking_members';
    // Fin Mcagigas
    if (!empty($groups)) { // guardado de datos $DB
        $groups_db = new stdClass();
        $groups_db->course = $courseid;
        $groups_db->type = $type; // Ok $type esta instanciado ya
        $i = 1;

        foreach ($groups as $group) {
            $groups_db->name = $basegroupname.' '.$i;
            $i++;
            if (!$groupid = $DB->insert_record('block_ubtracking_groups', $groups_db)) {
                print_error('insert_error', 'block_ubtracking');
            }

            $group_members = new stdClass();
            $group_members->groupid = $groupid;
            foreach ($group as $user) {
                $group_members->userid = $user;
                if (!$DB->insert_record($membersstr, $group_members)) {
                    print_error('insert_error', 'block_ubtracking');
                }
            }
        }
    }
}

$groups = $DB->get_records('block_ubtracking_groups', array('course'=>$courseid));

echo $OUTPUT->header();

if ($statusmsg) {
    echo $OUTPUT->notification($statusmsg, 'notifysuccess');
}

// INFORMACIÓN de grupos
$strgroupcfg =  html_writer::tag('p', get_string("groupcfg", "block_ubtracking"));
if (!is_object($blockconfig)) {
    $strgroupcfg .= html_writer::tag('p', get_string("nogroupcfg", "block_ubtracking"));
}
$strgroupcfg .= html_writer::start_tag('p');
$strgroupcfg .= get_string("balgroupsize", "block_ubtracking").': '.$groupsize.'<br/>';
$strgroupcfg .= get_string("lvlgroupsize", "block_ubtracking").': '.$lvlngroups;
$strgroupcfg .= html_writer::end_tag('p');

echo $OUTPUT->box($strgroupcfg, 'generalbox boxaligncenter boxwidthnormal');

$strgroupcfg = html_writer::tag('p', get_string("changegroupcfg", "block_ubtracking"));
$strgroupcfg .= html_writer::tag('p', get_string("changegroupcfgopt1", "block_ubtracking"));
$strgroupcfg .= html_writer::tag('p', get_string("or", "block_ubtracking"));
// redirección activando edición
$linkcfg = '<a href="'.$CFG->wwwroot.'/blocks/ubtracking/studentdistribution.php?courseid='.$courseid.'&id='.$id.'&editblock=1">'.
    get_string("accesgroupcfg", "block_ubtracking").'</a>';

$strgroupcfg .= html_writer::tag('p', get_string("changegroupcfgopt2", "block_ubtracking").$linkcfg);

echo $OUTPUT->box($strgroupcfg, 'generalbox boxaligncenter boxwidthnormal');

// En caso de NO TENER usuarios con el quiz hecho, NOTICE y vuelta al curso
if (!$nusers) {
    notice(get_string("nousersfinishedquiz", "block_ubtracking"), $CFG->wwwroot.'/course/view.php?id='.$courseid);
    echo $OUTPUT->footer();
    die;
}

// Número de usuarios
echo $OUTPUT->box(get_string("usersfinishedquiz", "block_ubtracking", $nusers), 'generalbox boxaligncenter boxwidthnormal');

// Opciones para crear de nuevo las Distros
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');

$selecturl = new moodle_url($PAGE->url, array('id'=>$id, 'courseid'=>$courseid,
                'groupsize'=>$groupsize, 'lvlngroups'=>$lvlngroups));
$options = array ('balanced' => get_string('balanced', 'block_ubtracking'),
            'level'=> get_string('level', 'block_ubtracking'));
echo ut_popup_form($selecturl, 'type', '', get_string('createdist', 'block_ubtracking'), $options);

echo $OUTPUT->box_end();

$groups = $DB->get_records('block_ubtracking_groups', array('course'=>$courseid));

// Resumen de usuarios y grupos existentes
if ($groups) {
    $params= '?id='.$id.'&courseid='.$courseid.'&groupsize='.$groupsize.'&lvlngroups='.$lvlngroups;
    ut_print_usergroups($usergrades, $groups, $params);
}

echo $OUTPUT->footer();