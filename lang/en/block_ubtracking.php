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

// block
$string['pluginname'] = 'UBTracking';
$string['initialeval'] = 'Establecer cuestionario de evaluación de nivel inicial';
$string['studentdistribution'] = 'Administrar distribuciones de alumnos';

$string['initialstuquiz'] = 'Cuestionario de nivel inicial';
$string['selectgroupsize'] = 'Selecciona el número de usuarios de cada grupo según el tipo de distribución:';
$string['balgroupsize'] = 'Distribuciones compensadas';
$string['lvlgroupsize'] = 'Distribuciones equilibradas';

// initaleval
$string['selectquiz'] = 'Selecciona cuestionario inicial para la evaluación';
$string['quizes'] = 'Cuestionarios';
$string['cancel'] = 'Operación cancelada';
$string['initialevalsuccess'] = 'Cuestionario inicial seleccionado';

// errors
$string['nocapabilities'] = 'No tienes permisos para ver esta página';
$string['insert_error'] = 'Error al insertar en la base de datos';
$string['update_error'] = 'Error al actualizar la base de datos';

// studentdistribution
$string['level'] = 'Distribuciones equilibradas';
$string['balanced'] = 'Distribuciones compensadas';
$string['studentdistribution'] = 'Crear distribuciones de alumnos';
$string['delete_groups'] = 'Borrar distribuciones';
$string['delete_groups_level'] = 'Borrar distribuciones equilibradas';
$string['delete_groups_balanced'] = 'Borrar distribuciones compensadas';
$string['balancedgroupsexist'] = 'Las distribuciones compensadas para este curso ya han sido creadas';
$string['levelgroupsexist'] = 'Las distribuciones equilibradas para este curso ya han sido creadas';
$string['groupsexist'] = 'Las distribuciones para este curso ya han sido creadas';
$string['confirmdelete'] = '¿Quieres borrarlas y crearlas de nuevo?';
$string['confirmdelete_level'] = '¿Confirma la eliminación de las distribuciones equilibradas?';
$string['confirmdelete_balanced'] = '¿Confirma la eliminación de las distribuciones compensadas?';
$string['createdist'] = 'Crear distribución: ';
$string['creationsuccess'] = 'creadas satisfactóriamente';
$string['nousersfinishedquiz'] = 'No puede crear grupos porque los usuarios no han realizado el test de nivel.';
$string['usersfinishedquiz'] = '{$a} estudiantes han realizado el test de nivel.';
$string['nogroupcfg'] = 'No se han configurado las características de las distribuciones, por defecto son:';
$string['groupcfg'] = 'Los tamaños de las distribuciones son los siguientes:';
$string['changegroupcfg'] = 'Para modificar las distribuciones puedes:';
$string['changegroupcfgopt1'] = 'Volver a la página principal del curso, activar edición y editar los ajustes del bloque';
$string['or'] = 'o';
$string['changegroupcfgopt2'] = 'Ir al siguiente enlace: ';
$string['accesgroupcfg'] = 'Configurar las distribuciones de grupos';
$string['groupactivities'] = 'Grupo de actividades';

// añadir usuario a distribución
$string['addusers'] = 'Añadir usuarios';
$string['addusersdistribution'] = 'Añadir usuarios a la distribución';
$string['nousersnodistributed_balanced'] = 'No hay estudiantes fuera de distribuciones compensadas.';
$string['nousersnodistributed_level'] = 'No hay estudiantes fuera de distribuciones equilibradas.';
$string['usersnodistributed_balanced'] = '{$a} estudiantes no se encuentran en distribuciones compensadas.';
$string['usersnodistributed_level'] = '{$a} estudiantes no se encuentran en distribuciones equilibradas.';
$string['addusertogroup'] = 'Selecciona un usuario para añadirlo al grupo ';
$string['useraddok'] = 'Usuario añadido correctamente';
$string['useradderror'] = 'El usuario no se ha podido añadir';

// assignactivity
$string['assignactivity'] = 'Asociar actividades';
$string['noassignactivity'] = 'no hay actividades asociadas';
$string['activity'] = 'Actividad';
$string['lvl'] = 'Equilibradas';
$string['bal'] = 'Compensadas';
$string['activitygroup'] = 'Actividad - Distribuciones';
$string['assignsuccess'] = 'Distribuciones asignadas a las actividades';
$string['deletesuccess'] = 'Distribuciones desasignadas correctamente';
$string['selectact'] = 'Selecciona la actividad: ';
$string['selectvoid'] = 'Para des-asociar una actividad de los grupos, selecciona el espacio vacío en la selección';
$string['errorassignmultidistrib'] = 'No se puede asignar una actividad a grupos de disrtibuciones diferentes';

// activitylist
$string['activitylist'] = 'Listado de actividades';
$string['done'] = 'Realizado al menos una vez';
$string['noactivitiesgroup'] = 'No tienes asignadas actividades en estos grupos';

// nombres grupos
$string['group_level'] = 'Equilibrado';
$string['group_balanced'] = 'Compensado';

// estadísticas
$string['statistics'] = 'Estadísticas';
$string['shortactivitygroup'] = 'GA';

// semaforos
$string['grey'] = '-'; // 'Gris';
$string['green'] = 'Verde';
$string['orange'] = 'Naranja';
$string['red'] = 'Rojo';

// nuevas funcionalidades
$string['delete_user_group'] = 'Quitar el usuario';
$string['delete_from_group'] = 'del grupo';
$string['go_delete_user'] = 'Vas a quitar el usuario';
$string['confirm_delete_user'] = '¿Estas seguro que quieres quitar el usuario';
$string['users'] = 'Alumnos matriculados';
$string['students'] = 'Alumnos';
$string['usersasign'] = 'Asignaciones individuales';
$string['nouseractivitiesasign'] = 'No tienes asignadas actividades individuales';


// capabilities
$string['ubtracking:teacherview'] = 'Visión de ubtracking para profesores';
$string['ubtracking:studentview'] = 'Visión de ubtracking para estudiantes';
