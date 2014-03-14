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
/**
 * Esta función crea las distribuciones equilibradas de los alumnos
 *
 * @param array $usergrades array con las notas de los alumnos
 * @param int $nusers número de usuarios
 * @param int $groupsize tamaño de los grupos, configurable para cada instancia del bloque
 * @param int $courseid ID el curso para los grupos equilibrados
 * @return array
 */
function ut_create_balanced_dist($usergrades, $nusers, $groupsize, $courseid) {

    global $DB;

    // Esta variable es la variable donde quedarán los grupos formados
    $groups = array();
    // En esta variable añadiremos los usuarios sobrantes para hacer que el número de usuarios sea
    // múltiplo de 5
    $overgroups = array();

    $ngroups = floor($nusers / $groupsize); // número de grupos

    $users = array_keys($usergrades); // cogemos los usuarios y los ponemos en un array aparte

    $mod = $nusers % 5; // hacemos mod 5 para saber por cuantos usuarios nos pasamos

    switch ($mod) {
        case 1:
            // 1 usuario: cogemos el del principio y lo ponemos en un array aparte
            $overgroups[] = $users[0];
            array_splice($users, 0, 1);
            break;
        case 2:
            // 2 usuarios: cogemos el del final y el del principio y los ponemos en un array aparte
            $overgroups[] = $users[$nusers-1];
            array_splice($users, $nusers-1, 1);
            $overgroups[] = $users[0];
            array_splice($users, 0, 1);
            break;
        case 3:
            // 3 usuarios: cogemos el del final y 2 del principio y los ponemos en un array aparte
            $overgroups[] = $users[$nusers-1];
            array_splice($users, $nusers-1, 1);
            $overgroups[] = $users[0];
            array_splice($users, 0, 1);
            $overgroups[] = $users[0];
            array_splice($users, 0, 1);
            break;
        case 4:
            // 3 usuarios: cogemos 2 del final y 2 del principio y los ponemos en un array aparte
            $overgroups[] = $users[$nusers-1];
            array_splice($users, $nusers-1, 1);
            $overgroups[] = $users[$nusers-2];
            array_splice($users, $nusers-2, 1);
            $overgroups[] = $users[0];
            array_splice($users, 0, 1);
            $overgroups[] = $users[0];
            array_splice($users, 0, 1);
            break;
        default:
            break;
    }

    // recorreremos el array de los usuarios $ngroups veces
    for ($i = 0; $i < $ngroups; $i++) {
        $groups[$i] = array();
        // cogiendo cada vez el usuario que se encuentre en la posición $j del array
        // la posición inicial de $j es 0+$i
        // incrementamos el valor de $j teniendo en cuenta el número de grupos
        for ($j = 0+$i; $j < $nusers; $j = $j + $ngroups) {
            // si el grupo en cuestión no está lleno
            if (count($groups[$i]) < $groupsize) {
                // metemos al usuario que toca
                $groups[$i][] = $users[$j];
            } else {
                // si está lleno, salimos
                break;
            }
        }
    }

    // metemos a los sobrantes en otro grupo
    $groups[$i] = $overgroups;

    return $groups;
}
/**
 * Esta función crea las distribuciones por nivel de los alumnos
 *
 * @param array $usergrades array con las notas de los alumnos
 * @param int $nusers número de usuarios
 * @param int $ngroup número de usuarios en grupo, configurable para cada instancia del bloque
 * @param int $courseid ID el curso para los grupos por nivel
 * @return array
 */
function ut_create_level_dist($usergrades, $nusers, $ngroup, $courseid) {
    global $DB;

    $groups = array();

    $i = 1;
    $j = 0;

    // para cada nota
    foreach ($usergrades as $user => $grade) {
        // si la nota es mayor que los rangos marcados por el índice y el grupo actual
        if ($j < $ngroup) {
            // metemos al usuario en el grupo marcado por la iteración
            $groups[$i][] = $user;
            $j++;
        } else {
            // si no, metemos al usuario en el siguiente grupo y aumentamos la variable que controla
            // en que grupo estamos
            $i++;
            $groups[$i][] = $user;
            $j = 1;
        }
    }
    return $groups;
}

/*

Antigua función de distribución equilibrada lvl
/ **
 * Esta función crea las distribuciones por nivel de los alumnos
 *
 * @param array $usergrades array con las notas de los alumnos
 * @param int $nusers número de usuarios
 * @param int $ngroup número de usuarios en grupo, configurable para cada instancia del bloque
 * @param int $courseid ID el curso para los grupos por nivel
 * @return array
 * /
function ut_create_level_dist($usergrades, $nusers, $ngroup, $courseid) {
    global $DB;

    $groups = array();

    // índice usado para separar los grupos: (nota máxima - nota mínima) / número de grupos
    $index = (max($usergrades) - min($usergrades)) / $ngroup;

    $i = 1;

    // para cada nota
    foreach ($usergrades as $user => $grade) {
        // si la nota es mayor que los rangos marcados por el índice y el grupo actual
        if ($grade >= $index * ($i-1) && $grade <= $index * $i) {
            // metemos al usuario en el grupo marcado por la iteración
            $groups[$i][] = $user;
        } else {
            // si no, metemos al usuario en el siguiente grupo y aumentamos la variable que controla
            // en que grupo estamos
            $groups[$i+1][] = $user;
            $i++;
        }
    }

    return $groups;
}


 */

/**
 * Devuelve un objeto table con las actividades del usuario en el curso y del tipo dado
 *
 * @param int $userid ID del usuario actual
 * @param int $courseid
 * @param string $type level o balance
 * @return table
 */
function ut_get_activities_by_type($userid, $courseid, $type) {

    global $CFG, $DB, $OUTPUT;

    $activities = array();
    $table = new html_table();
    // Mcagigas
    // Se unifican las tablas lvl_members y bal_members
    // Se discrimina el tipo de grupo en la consulta
    /*
    if ($type == 'level') {
        $strtype = 'block_ubtracking_lvl_members';
    } else {
        $strtype = 'block_ubtracking_bal_members';
    }*/
    $query = "  SELECT cm.id, cm.instance, m.name
                FROM {block_ubtracking_grpactiv} ga,
                     {block_ubtracking_groups} g,
                     {block_ubtracking_members} t,
                     {course_modules} cm,
                     {modules} m
                WHERE t.userid = $userid AND t.groupid = ga.groupid
                AND ga.groupid = g.id AND ga.cmid = cm.id
                AND g.course = $courseid
                AND m.id = cm.module
                AND g.type ='".$type."'";

    if ($type == '') {
        $query = "  SELECT cm.id, cm.instance, m.name
                FROM {block_ubtracking_grpactiv} ga,
                     {course_modules} cm,
                     {modules} m
                WHERE ga.cmid = cm.id
                AND ga.courseid = $courseid
                AND ga.userid = $userid
                AND m.id = cm.module";

    }

    // Fin MCagigas
    $results = $DB->get_records_sql($query);

    if (!empty($results)) {

        $course = $DB->get_record('course', array('id'=>$courseid));
        $modinfo = get_fast_modinfo($course);

        $table->head = array(get_string('name'));
        $table->align = array('left');
        $table->width = '50%';
        $table->data = array();

        foreach ($results as $activity) {
            $cm = $modinfo->get_cm($activity->id);
            if ($cm->uservisible and coursemodule_visible_for_user($cm)) {
                $row = array();
                $icon = '';
                // $icon = html_writer::tag('img', '', array('src' => $OUTPUT->pix_url($cm->icon)));
                $row[] = html_writer::link($CFG->wwwroot . "/mod/$cm->modname/view.php?id=$activity->id", $icon.$cm->name);
                $table->data[] = $row;
            }
        }
    }

    return $table;
}
/**
 * html select que redirige al elegir opción
 * esta función fue codificada porque no había una opcion por defecto en moodle 2.0 que hiciera
 * lo que quería hacer
 *
 * @param moodleurl | string $url
 * @param string $name
 * @param string $selected
 * @param string $label
 * @param array $options
 * @return string
 */

function ut_popup_form($url, $name, $selected, $label, $options=array()) {
    $form = '<form action="'.$url.'" method="post" name="'.$name.'form">';
    $form .= '<label>'.$label.'</label>&nbsp;&nbsp;';
    $form .= '<select onChange="this.form.submit();" name="'.$name.'">';
    $form .= '<option value="">'.get_string('choosedots').'</option>';
    foreach ($options as $key => $option) {
        $form .= ($key == $selected) ? '<option selected="'.$selected.'" value="'.$key.'">'.$option.'</option>'
                                        : '<option value="'.$key.'">'.$option.'</option>';
    }
    $form .= '</select>';
    $form .= '</form>';

    return $form;
}
/**
 * devuelve las actividades del curso dado
 *
 * @param array $actnamearray array con el nombre de las actividades
 * @param int $courseid
 * @return array
 */
function ut_get_activities($actnamearray, $courseid) {

    global $CFG, $DB;
    $cmids = array();

    // name de las actividades que no se quieran incluir
    $bannedmodules = implode(' AND name != ', $actnamearray);

    // consulta para tener las actividades requeridas
    $modules = "SELECT id,name
                FROM {modules}
                WHERE name != $bannedmodules";

    $query = "  SELECT cm.id,cm.instance,cm.module,m.name
                FROM {course_modules} cm, ($modules) m
                WHERE cm.module = m.id AND cm.course = :courseid";

    $results = $DB->get_records_sql($query, array('courseid' => $courseid));

    foreach ($results as $result) {
        $activity = $DB->get_record($result->name, array('id'=>$result->instance));
        $cmids[$result->id] = $activity->name;
    }

    return $cmids;
}

/**
 * Devuelve un array de objetos con los grupos del curso depensidendo del tipo de distribución
 *
 * @param array $usergrades usuarios con nota en el quiz inicial
 * @param object $groups agrupaciones del curso
 * @param string $type level o balance
 * @return array $gdata array de objetos con los datos del gurpo y usuarios
 */
function ut_get_usergroups($usergrades, $groups, $type) {
    global $DB;

    $gdata = array();
    // Mcagigas
    // Se unifican las tablas
    // Se discrimina el tipo de grupo en el sql
    /*
    if ($type == 'balanced') {
        $gtable = 'block_ubtracking_bal_members';
    } else {
        $gtable = 'block_ubtracking_lvl_members';
    }*/
    foreach ($groups as $group) {
        if ($group->type != $type) {
            continue;
        }
        // $sql = 'SELECT u.* FROM {'.$gtable.'} m, {user} u where m.groupid = :gid AND u.id = m.userid';
        $sql = 'SELECT u.* FROM {block_ubtracking_members} m, {user} u, {block_ubtracking_groups} g '.
            'where m.groupid = :gid AND u.id = m.userid and g.id = m.groupid and g.type =  '."'".$type."'";
        // Fin MCagigas
        $values = array('gid' => $group->id);
        $gusers = $DB->get_records_sql($sql, $values);
        if (!empty($gusers)) {
            $tmp = new Object();
            $tmp->id = $group->id;
            $tmp->name = $group->name;
            $tmp->users = $gusers;
            $gdata[] = $tmp;
        }
    }

    return $gdata;

}

/**
 * función que muestra el contenido de las distribuciones que tenga el curso
 *
 * @param array $usergrades usuarios con nota en el quiz inicial
 * @param object $groups agrupaciones del curso
 */
function ut_print_usergroups($usergrades, $groups, $params) {
    global $OUTPUT;

    echo $OUTPUT->box_start('generalbox distros boxwidthnormal boxaligncenter clearfix');

    if ($glvl =  ut_get_usergroups($usergrades, $groups, 'level')) {
        ut_print_distribution($usergrades, $glvl, 'level', $params);
    }
    if ($gbal = ut_get_usergroups($usergrades, $groups, 'balanced')) {
        ut_print_distribution($usergrades, $gbal, 'balanced', $params);
    }
    echo $OUTPUT->box_end();
}

/**
 * función que muestra los listados de usuarios la distribución pasada
 *
 * @param array $usergrades usuarios con nota en el quiz inicial
 * @param object $groups agrupaciones del curso
 * @param string $type level o balance
 */
function ut_print_distribution($usergrades, $groups, $type, $params) {
    global $OUTPUT, $COURSE;

    echo $OUTPUT->box_start('distro generalbox boxaligncenter boxwidthnormal');
    echo html_writer::start_tag('h2', array('class'=>'titdistro'));

    $imagedel = html_writer::tag('img', '', array('alt'=>'x', 'src'=>$OUTPUT->pix_url('t/delete')));
    echo html_writer::start_tag('div', array('class'=>'del_distro'));
    echo html_writer::link('delete_groups.php'.$params.'&onlydel=1&type='.$type, $imagedel, array('name' => get_string('delete_groups_'.$type, 'block_ubtracking'),
     'title' => get_string('delete_groups_'.$type, 'block_ubtracking')));
    echo html_writer::end_tag('div');
    echo get_string($type, 'block_ubtracking');
    echo html_writer::end_tag('h2');

    foreach ($groups as $g) {
        echo html_writer::link('', '', array('name' => "group$g->id"));
        echo html_writer::start_tag('h3', array('class'=>'group'));
          $imageuser = html_writer::tag('img', '', array('alt'=>get_string('addusers', 'block_ubtracking'), 'src'=>$OUTPUT->pix_url('t/groupn')));
          echo html_writer::start_tag('div', array('class'=>'add_user'));
           echo html_writer::link('addstudenttodistribution.php'.$params.'&type='.$type.'&g='.$g->id, $imageuser, array('name' => get_string('addusers', 'block_ubtracking'),
           'title' => get_string('addusers', 'block_ubtracking')));
          echo html_writer::end_tag('div');
          echo $g->name;
        echo html_writer::end_tag('h3');
        foreach ($g->users as $gu) {
            $gradeuser = 0;
            if (isset($usergrades[$gu->id])) {
                $gradeuser = $usergrades[$gu->id];
            }
            $fullname = html_writer::link(new moodle_url('/user/view.php', array('id' => $gu->id, 'courseid' => $COURSE->id)), fullname($gu));
            $deluser = html_writer::link('delete_user.php'.$params.'&userid='.$gu->id.'&groupid='.$g->id, $imagedel,
             array('name' => get_string('delete_user_group', 'block_ubtracking'),
             'title' => get_string('delete_user_group', 'block_ubtracking')));

            echo html_writer::tag('p', $fullname.' ('.round($gradeuser, 2).') '.$deluser, array('class'=>'user'));
        }
    }
    echo $OUTPUT->box_end();
}

/**
 * Devuelve el semáforo para un grupo compensado para un determinado grupo de actividades
 *
 * @param int $groupid El identificador de grupo
 * @param int $activitigroup El identificador del grupo de actividades
 * @return text Devuelve el html con el semáforo
 */
function ut_get_semaphore_group ($courseid, $groupid, $type, $activitygroup) {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->dirroot . "/lib/gradelib.php");

    $passed = false;
    $pending = false;

    $sqlactivities = "SELECT ga.* FROM {block_ubtracking_grpactiv} ga,  {block_ubtracking_groups} g ".
            "WHERE g.id = :levelgroupid AND g.id = ga.groupid AND type = :type AND course = :course AND activitygroup = :activitygroup";
    $activities = $DB->get_records_sql($sqlactivities, array('course'=>$courseid, 'type'=>$type, 'activitygroup' => $activitygroup, 'levelgroupid' => $groupid));

    $key = '_'.$courseid.'_'.$groupid.'_'.$activitygroup;
    $infogroup = '<div class="group_info">
                    <a href="javascript:mostrar(infoga'.$key.');"><img src="'.$OUTPUT->pix_url('docs').'" alt="+" /></a>
                </div>
                <div id="infoga'.$key.'" class="allgroup_info" style="display:none">';
    $infogroup .= ut_get_info_activities($activities);
    $infogroup .= '</div>';

    // obtenemos usuarios del grupo

    $usersgroup = $DB->get_records('block_ubtracking_members', array('groupid'=>$groupid), '', 'userid');

    if (!empty($activities)) {
        foreach ($activities as $activity) {
            $cm = get_coursemodule_from_id('', $activity->cmid);
            // para el primer ususario guardaremos valores básicos
            $values = 0;
            $grademax = 0;
            $gradepass = 0;

            foreach ($usersgroup as $userid => $u) {
                if ($cm->modname == 'assignment') {
                    if ($submission = $DB->get_record('assignment_submissions',
                        array('assignment' => $cm->instance, 'userid' => $userid))) {
                        if (!$submission->teacher) { // falta la validación del profe (pending)
                            $pending = true;
                        } else {
                            $passed = true;
                        }
                    }
                } else {
                    $grades = grade_get_grades($courseid, 'mod', $cm->modname, $cm->instance, $userid);
                    if (!$values) {
                        $grademax = $grades->items[0]->grademax;
                        $gradepass = $grades->items[0]->gradepass;
                        if ($gradepass <= 0) {
                            $gradepass = $grademax/2;
                        }
                        $values = 1;
                    }
                    if (!empty($grades->items[0]->grades)) {
                        $finalgrade = reset($grades->items[0]->grades);
                        if ($finalgrade->str_grade >= $gradepass) { // un usuario no lo ha superado
                            $passed = true;
                        }
                    }
                }
            }
            if (!$pending && !$passed) {
                return '<div class="textred">'.$infogroup.'</div>';
            }
        }
    } else {
        return '<div class="textgrey">&nbsp;</div>';
    }

    if ($pending) {
        return '<div class="textorange">'.$infogroup.'</div>';
    } else if ($passed) {
        return '<div class="textgreen">'.$infogroup.'</div>';
    }

    return '<div class="textred">'.$infogroup.'</div>';
}

/**
 * [ut_get_info_activities description]
 * @param  [type] $activities [description]
 * @return [type]             [description]
 */
function ut_get_info_activities($activities) {
    global $DB;
    $return = '';
    $activitiesstr = array();
    foreach ($activities as $activity) {
        $cm = get_coursemodule_from_id('', $activity->cmid, 0, true, MUST_EXIST);
        $module = $DB->get_record('modules', array('id'=>$cm->module), '*', MUST_EXIST);
        $data = $data = $DB->get_record($module->name, array('id'=>$cm->instance), '*', MUST_EXIST);
        $activityurl = new moodle_url("/mod/$module->name/view.php", array('id' => $cm->id));

        $activitiesstr[] = html_writer::link($activityurl, $data->name);
    }
    if ($activitiesstr) {
        $return = join('<br/>', $activitiesstr);
    } else {
        $return = get_string('noassignactivity', 'block_ubtracking');
    }

    return $return;
}

/**
 * Devuelve el semáforo para un grupo equilibrado para un determinado usuario
 *
 * @param int $userid El identificador del usuario
 * @param int $activitygroup El identificador de grupo de actividades
 * @return string Devuelve el html con el semáforo
 */
function ut_get_semaphore_user ($courseid, $userid, $activitygroup) {
    global $CFG, $DB,$OUTPUT;
    require_once($CFG->dirroot . "/lib/gradelib.php");

    $pending = false;
    $passed = false;

    $sql = "SELECT ga.* FROM {block_ubtracking_grpactiv} ga WHERE ".
        "ga.userid = :userid AND ga.courseid = :course AND ga.activitygroup = :activitygroup";
    $activities = $DB->get_records_sql($sql, array('userid' => $userid, 'course' => $courseid, 'activitygroup' => $activitygroup));

    $key = '_'.$userid.'_'.$courseid.'_'.$activitygroup;
    $infogroup = '<div class="group_info">
                    <a href="javascript:mostrar(infoga'.$key.');"><img src="'.$OUTPUT->pix_url('docs').'" alt="+" /></a>
                </div>
                <div id="infoga'.$key.'" class="allgroup_info" style="display:none">';
    $infogroup .= ut_get_info_activities($activities);
    $infogroup .= '</div>';

    if ($activities) {
        foreach ($activities as $activity) {
            $cm = get_coursemodule_from_id('', $activity->cmid);

            if ($cm->modname == 'assignment') {
                if ($submission = $DB->get_record('assignment_submissions', array('assignment' => $cm->instance,
                    'userid' => $userid))) {
                    if (!$submission->teacher) { // Si falta la validación del profe (pending).
                        $pending = true;
                    } else {
                        $passed = true; // (entregado)
                    }
                } else { // sin entregar
                    return '<div class="textred">'.$infogroup.'</div>';
                }
            } else {
                $grades = grade_get_grades($courseid, 'mod', $cm->modname, $cm->instance, $userid);
                if (!empty($grades->items[0]->grades)) {
                    $max = $grades->items[0]->grademax;
                    $pass = $grades->items[0]->gradepass;
                    if ($pass <= 0) {
                        $pass = $max/2;
                    }
                    $finalgrade = reset($grades->items[0]->grades);

                    if ($finalgrade->str_grade < $pass) { // sin aprobar
                        return '<div class="textred">'.$infogroup.'</div>';
                    } else {
                        $passed = true;
                    }
                } else {
                    return '<div class="textred">'.$infogroup.'</div>';
                }
            }
        }
    } else {
        return '<div class="textgrey">&nbsp;</div>';
    }

    if ($pending) {
        return '<div class="textorange">'.$infogroup.'</div>';
    } else if ($passed) {
        return '<div class="textgreen">'.$infogroup.'</div>';
    }

    return '<div class="textred">'.$infogroup.'</div>';
}