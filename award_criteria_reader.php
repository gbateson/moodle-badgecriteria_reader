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
 * This file contains the reader completion badge award criteria type class
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/reader/renderer.php');

/**
 * Reader completion badge award criteria
 *
 */
class award_criteria_reader extends award_criteria {

    /* @var int Criteria [BADGE_CRITERIA_TYPE_READER] */
    public $criteriatype = BADGE_CRITERIA_TYPE_READER;

    public $required_param = 'readinggoal';
    public $optional_params = array('absolutetime_start', 'absolutetime_end',
                                    'relativetime_start', 'relativetime_end', 'enrolmenttype',
                                    'wordcount_min',    'wordcount_max',
                                    'publishers',      'difficulties',  'genres',
                                    'bookinclude',     'bookexclude',
                                    'usernameinclude', 'usernameexclude',
                                    'activityinclude', 'activityexclude',
                                    'courseinclude',   'courseexclude',
                                    'categoryinclude', 'categoryexclude');

    const TEXT_NUM_SIZE = 10;
    const MULTI_SELECT_SIZE = 5;
    const MAX_READING_LEVEL = 15;
    const ENROLMENT_TYPE_SITE = 0;
    const ENROLMENT_TYPE_COURSE = 1;

    /**
     * Add appropriate new criteria options to the form
     *
     */
    public function get_options(&$mform) {
        global $DB;

        $none = false;
        $plugin = 'badges';
        $strman = get_string_manager();

        $dateoptions = array('optional' => true);
        $textoptions = array('size' => self::TEXT_NUM_SIZE);
        $selectoptions = array('multiple' => 'multiple', 'size' => self::MULTI_SELECT_SIZE);
        $durationoptions = array('optional' => true, 'defaultunit' => 86400);
        $enrolmentoptions = array(
            self::ENROLMENT_TYPE_SITE => get_string('enrolmenttype_site', $plugin),
            self::ENROLMENT_TYPE_COURSE => get_string('enrolmenttype_course', $plugin)
        );

        $difficulties = array();
        for ($i=0; $i<=self::MAX_READING_LEVEL; $i++) {
            if ($i==0) {
                $difficulties[$i] = get_string('difficulty_any', $plugin);
            } else {
                $difficulties[$i] = get_string('difficulty_short', $plugin, $i);
            }
        }
        if ($publishers = $DB->get_records_sql('SELECT DISTINCT publisher FROM {reader_books} WHERE publisher <> ?', array('Extra points'))) {
            $publishers = array_keys($publishers);
            $publishers = array_combine($publishers, $publishers);
        } else {
            $publishers = array();
        }
        $publishers = array_merge(array('' => get_string('publisher_any', $plugin)), $publishers);
        $genres = mod_reader_renderer::valid_genres();

        //-----------------------------------------------------------------------------
        // this header must be called "first_header" so that error messages
        // can be added to the top of the form by the parent class
        $name = 'first_header';
        $label = $this->get_title();
        $mform->addElement('header', $name, $label);
        $mform->addHelpButton($name, 'criteria_' . $this->criteriatype, 'badges');
        //-----------------------------------------------------------------------------

        $name = 'readinggoal_min';
        $label = get_string($name, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);

        $name = 'readinggoal_max';
        $label = get_string($name, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);

        //-----------------------------------------------------------------------------
        $name = 'timing';
        $label = get_string($name, 'form');
        $mform->addElement('header', $name, $label);
        //-----------------------------------------------------------------------------

        $name = 'absolutetime_start';
        $label = get_string($name, $plugin);
        $mform->addElement('date_time_selector', $name, $label, $dateoptions);
        $mform->addHelpButton($name, $name, $plugin);

        $name = 'absolutetime_end';
        $label = get_string($name, $plugin);
        $mform->addElement('date_time_selector', $name, $label, $dateoptions);
        $mform->addHelpButton($name, $name, $plugin);

        $name = 'relativetime_start';
        $label = get_string($name, $plugin);
        $mform->addElement('duration', $name, $label, $durationoptions);
        $mform->addHelpButton($name, $name, $plugin);

        $name = 'relativetime_end';
        $label = get_string($name, $plugin);
        $mform->addElement('duration', $name, $label, $durationoptions);
        $mform->addHelpButton($name, $name, $plugin);

        $name = 'enrolmenttype';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $enrolmentoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);
        $mform->setDefault($name, self::ENROLMENT_TYPE_SITE);

        //-----------------------------------------------------------------------------
        $name = 'bookfilters';
        $label = get_string($name, $plugin);
        $mform->addElement('header', $name, $label);
        //-----------------------------------------------------------------------------

        $name = 'wordcount_min';
        $label = get_string($name, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);

        $name = 'wordcount_max';
        $label = get_string($name, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);

        $name = 'publishers';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $publishers, $selectoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_TEXT);

        $name = 'difficulties';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $difficulties, $selectoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);

        $name = 'genres';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $genres, $selectoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_TEXT);

        $this->add_field_include_exclude($mform, $textoptions, $plugin, 'book', 'include');
        $this->add_field_include_exclude($mform, $textoptions, $plugin, 'book', 'exclude');

        //-----------------------------------------------------------------------------
        $this->add_section_filters($mform, $textoptions, $plugin, 'username');
        $this->add_section_filters($mform, $textoptions, $plugin, 'activity');
        $this->add_section_filters($mform, $textoptions, $plugin, 'course');
        $this->add_section_filters($mform, $textoptions, $plugin, 'category');
        //-----------------------------------------------------------------------------

        return array($none, get_string('noparamstoadd', 'badges'));
    }

    /**
     * add a filter section to the $mform
     *
     * @return string
     */
    protected function add_section_filters($mform, $textoptions, $plugin, $name) {
        $header = $name.'filters';
        $mform->addElement('header', $header, get_string($header, $plugin));
        $this->add_field_include_exclude($mform, $textoptions, $plugin, $name, 'include');
        $this->add_field_include_exclude($mform, $textoptions, $plugin, $name, 'exclude');
    }

    /**
     * add an include/exclude field to the $mform
     *
     * @return string
     */
    protected function add_field_include_exclude($mform, $textoptions, $plugin, $name, $type) {
        $name = $name.$type;
        $label = get_string($type, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $type, $plugin);
        $mform->setType($name, PARAM_TEXT);
    }

    /**
     * Get criteria details for displaying to users
     *
     * @return string
     */
    public function get_details($short = '') {
        $details = array();
        $strman = get_string_manager();
        $plugin = 'badges'; // badgecriteria_reader
        foreach ($this->params as $type => $values) {
            foreach ($values as $name => $value) {
                $str = $name.'_'.$type; // e.g. readinggoal_min
                if ($strman->string_exists($str, $plugin)) {
                    $str = $strman->get_string($str, $plugin);
                }
                switch ($name) {
                    case 'readinggoal': $value = number_format($value); break;
                    case 'absolutedate': $value = userdate($value); break;
                }
                $details[] = $str.': '.$value;
            }
        }
        if ($short) {
            return implode(', ', $details);
        } else {
            return html_writer::alist($details, array(), 'ul');
        }
    }

    /**
     * Review this criteria and decide if it has been completed
     *
     * @param int $userid User whose criteria completion needs to be reviewed.
     * @param bool $filtered An additional parameter indicating that user list
     *        has been reduced and some expensive checks can be skipped.
     *
     * @return bool Whether criteria is complete
     */
    public function review($userid, $filtered = false) {
        global $DB;

        // Users were already filtered by criteria completion, no checks required.
        if ($filtered) {
            return true;
        }

        $join = '';
        $whereparts = array();
        $sqlparams = array();
        $rule = ($this->method == BADGE_CRITERIA_AGGREGATION_ANY) ? ' OR ' : ' AND ';

        foreach ($this->params as $param) {
            if (is_numeric($param['field'])) {
                // This is a custom field.
                $idx = count($whereparts) + 1;
                $join .= " LEFT JOIN {user_info_data} uid{$idx} ON uid{$idx}.userid = u.id AND uid{$idx}.fieldid = :fieldid{$idx} ";
                $sqlparams["fieldid{$idx}"] = $param['field'];
                $whereparts[] = "uid{$idx}.id IS NOT NULL";
            } else {
                // This is a field from {user} table.
                $whereparts[] = $DB->sql_isnotempty('u', "u.{$param['field']}", false, true);
            }
        }

        $sqlparams['userid'] = $userid;

        if ($whereparts) {
            $where = " AND (" . implode($rule, $whereparts) . ")";
        } else {
            $where = '';
        }
        $sql = "SELECT 1 FROM {user} u " . $join . " WHERE u.id = :userid $where";
        $overall = $DB->record_exists_sql($sql, $sqlparams);

        return $overall;
    }

    /**
     * Returns array with sql code and parameters returning all ids
     * of users who meet this particular criterion.
     *
     * @return array list($join, $where, $params)
     */
    public function get_completed_criteria_sql() {
        global $DB;

        $join = '';
        $whereparts = array();
        $params = array();
        $rule = ($this->method == BADGE_CRITERIA_AGGREGATION_ANY) ? ' OR ' : ' AND ';

        foreach ($this->params as $param) {
            if (is_numeric($param['field'])) {
                // This is a custom field.
                $idx = count($whereparts);
                $join .= " LEFT JOIN {user_info_data} uid{$idx} ON uid{$idx}.userid = u.id AND uid{$idx}.fieldid = :fieldid{$idx} ";
                $params["fieldid{$idx}"] = $param['field'];
                $whereparts[] = "uid{$idx}.id IS NOT NULL";
            } else {
                $whereparts[] = $DB->sql_isnotempty('u', "u.{$param['field']}", false, true);
            }
        }

        if ($whereparts) {
            $where = " AND (" . implode($rule, $whereparts) . ")";
        } else {
            $where = '';
        }
        return array($join, $where, $params);
    }
}
