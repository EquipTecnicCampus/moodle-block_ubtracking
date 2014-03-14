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

class block_ubtracking extends block_list {

    public function init() {
        // This method must be implemented for all blocks
        $this->title = get_string('pluginname', 'block_ubtracking');
    }

    public function get_content() {

        global $CFG, $COURSE, $DB;

        // This method should, when called, populate the $this->content variable of your block
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->items  = array();
        $this->content->icons  = array();
        $this->content->footer = '';

        if (has_capability('block/ubtracking:teacherview', $this->context)) {

            $balgroupsize = 5;
            $lvlngroups = 4;
            $groupscfg = '';

            if (!empty($this->config->balgroupsize)) {
                $groupscfg = '&groupscfg=1';
                $balgroupsize = $this->config->balgroupsize;
            }
            if (!empty($this->config->lvlngroups)) {
                 $lvlngroups = $this->config->lvlngroups;
            }
            $this->content->items[] = '<a href="'.$CFG->wwwroot.
                        '/blocks/ubtracking/initialeval.php?id='.$this->instance->id.'&courseid='.$COURSE->id.'">'.
                        get_string('initialeval', 'block_ubtracking').'</a>';
            if ($course_quiz = $DB->get_record('block_ubtracking_coursequiz', array('course'=>$COURSE->id))) {
                $this->content->items[] = '<a href="'.$CFG->wwwroot.
                        '/blocks/ubtracking/studentdistribution.php?id='.$this->instance->id.
                        '&courseid='.$COURSE->id.'&groupsize='.$balgroupsize.
                        '&lvlngroups='.$lvlngroups.$groupscfg.'">'.
                        get_string('studentdistribution', 'block_ubtracking').'</a>';
                $this->content->items[] = '<a href="'.$CFG->wwwroot.
                        '/blocks/ubtracking/assignactivity.php?id='.$this->instance->id.
                        '&courseid='.$COURSE->id.'">'.get_string('assignactivity', 'block_ubtracking').'</a>';
                $this->content->items[] = '<a href="'.$CFG->wwwroot.
                    '/blocks/ubtracking/statistics.php?id='.$this->instance->id.
                    '&courseid='.$COURSE->id.'">'.get_string('statistics', 'block_ubtracking').'</a>';

            }
        }
        if (has_capability('block/ubtracking:studentview', $this->context)) {
            if ($course_quiz = $DB->get_record('block_ubtracking_coursequiz', array('course'=>$COURSE->id))) {
                $query = "  SELECT cm.id as quizid
                            FROM {$CFG->prefix}course_modules cm, {$CFG->prefix}modules m,
                                 {$CFG->prefix}quiz q
                            WHERE m.name = 'quiz' AND m.id = cm.module
                            AND cm.instance = q.id AND q.id = $course_quiz->quiz
                            AND q.course = cm.course AND cm.course = $COURSE->id";
                $result = $DB->get_record_sql($query);
                if (!empty($result)) {
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.
                                '/mod/quiz/view.php?id='.$result->quizid.'">'.
                                get_string('initialstuquiz', 'block_ubtracking').'</a>';
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.
                                '/blocks/ubtracking/activitylist.php?id='.$this->instance->id.
                                '&courseid='.$COURSE->id.'">'.get_string('activitylist', 'block_ubtracking').'</a>';
                }
            }
        }

        return $this->content;
    }

    public function applicable_formats() {
        return array('site' => false, 'course-view' => true);
    }
}
