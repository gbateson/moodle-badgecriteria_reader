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

    public $required_param = 'readinggoal';         // "min" and "max"
    public $optional_params = array('fixedtime',    // "start" and "end"
                                    'relativetime', // "start" and "end"
                                    'enrolment',    // "type'
                                    'wordcount',    // "min" and "max"
                                    'publishers',   // "list"
                                    'difficulties', // "list"
                                    'genres',       // "list"
                                    'book',         // "include" and "exclude"
                                    'username',     // "include" and "exclude"
                                    'activity',     // "include" and "exclude"
                                    'course',       // "include" and "exclude"
                                    'category');    // "include" and "exclude"

    protected $fixmonth = true;
    protected $fixday   = true;
    protected $fixhour  = true;

    protected $fixyearchar  = '';
    protected $fixmonthchar = '';
    protected $fixdaychar   = '';

    protected $customdatefmt = '%Y %b %d (%a) %H:%M';
    protected $moodledatefmt = 'strftimerecent';

    const TEXT_NUM_SIZE = 10;
    const MULTI_SELECT_SIZE = 5;
    const MAX_READING_LEVEL = 15;
    const ENROLMENT_TYPE_SITE = 1;
    const ENROLMENT_TYPE_COURSE = 2;

    /*
     * standard contructor modified to expand multiselect elements to arrays
     */
    public function __construct($params) {
        parent::__construct($params);
        foreach ($this->get_multiselect_element_names() as $name) {
            if (isset($this->params[$name]) && is_string($this->params[$name])) {
                $this->params[$name] = explode(',', $this->params[$name]);
            }
        }
    }

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
        $enrolmentoptions = $this->get_enrolment_types($plugin);

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
        $this->set_default($mform, 'readinggoal', 'min');

        $name = 'readinggoal_max';
        $label = get_string($name, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);
        $this->set_default($mform, 'readinggoal', 'max');

        //-----------------------------------------------------------------------------
        $name = 'timing';
        $label = get_string($name, 'form');
        $mform->addElement('header', $name, $label);
        //-----------------------------------------------------------------------------

        $name = 'fixedtime_start';
        $label = get_string($name, $plugin);
        $mform->addElement('date_time_selector', $name, $label, $dateoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $this->set_default($mform, 'fixedtime', 'start');

        $name = 'fixedtime_end';
        $label = get_string($name, $plugin);
        $mform->addElement('date_time_selector', $name, $label, $dateoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $this->set_default($mform, 'fixedtime', 'end');

        $name = 'enrolment_type';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $enrolmentoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);
        $this->set_default($mform, 'enrolment', 'type');

        $name = 'relativetime_start';
        $label = get_string($name, $plugin);
        $mform->addElement('duration', $name, $label, $durationoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $this->set_default($mform, 'relativetime', 'start');

        $name = 'relativetime_end';
        $label = get_string($name, $plugin);
        $mform->addElement('duration', $name, $label, $durationoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $this->set_default($mform, 'relativetime', 'end');

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
        $this->set_default($mform, 'wordcount', 'min');

        $name = 'wordcount_max';
        $label = get_string($name, $plugin);
        $mform->addElement('text', $name, $label, $textoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);
        $this->set_default($mform, 'wordcount', 'max');

        $name = 'publishers_list';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $publishers, $selectoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_TEXT);
        $this->set_default($mform, 'publishers', 'list');

        $name = 'difficulties_list';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $difficulties, $selectoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_INT);
        $this->set_default($mform, 'difficulties', 'list');

        $name = 'genres_list';
        $label = get_string($name, $plugin);
        $mform->addElement('select', $name, $label, $genres, $selectoptions);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setType($name, PARAM_TEXT);
        $this->set_default($mform, 'genres', 'list');

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
     * set a default value for an $mform element
     *
     * @param string $name of $mform element, e.g. "readinggoal"
     * @param string $type of $mform element, e.g. "min" or "max"
     * @return string
     */
    protected function set_default($mform, $name, $type) {
        if (array_key_exists($type, $this->params)) {
            if (array_key_exists($name, $this->params[$type])) {
                $mform->setDefault($name.'_'.$type, $this->params[$type][$name]);
            }
        }
    }

    /**
     * Get names of multi-select form elements.
     *
     * @param array $params Values from the form or any other array.
     */
    protected function get_multiselect_element_names() {
        return array('publishers_list', 'difficulties_list', 'genres_list');
    }

    /**
     * Get array of enrolment types
     *
     * @param string  $plugin name
     * @param integer $type (optional, default=null)
     * @return string
     */
    protected function get_enrolment_types($plugin, $type=null) {
        $types = array(
            self::ENROLMENT_TYPE_SITE => get_string('enrolment_type_site', $plugin),
            self::ENROLMENT_TYPE_COURSE => get_string('enrolment_type_course', $plugin)
        );
        if ($type===null) {
            return $types;
        }
        if (array_key_exists($type, $types)) {
            return $types[$type];
        }
        return 'oops - '.$type; // shouldn't happen !!
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
        $elementname = $name.'_'.$type;
        $label = get_string($type, $plugin);
        $mform->addElement('text', $elementname, $label, $textoptions);
        $mform->addHelpButton($elementname, $type, $plugin);
        $mform->setType($elementname, PARAM_TEXT);
        $this->set_default($mform, $name, $type);
    }

    /**
     * Get criteria details for displaying to users
     *
     * @return string
     */
    public function get_details($short = '') {
        $details = array();
        $plugin = 'badges'; // badgecriteria_reader

        // format individual criteria
        $params = array();
        foreach ($this->params as $type => $values) {
            foreach ($values as $name => $value) {
                if ($value) {
                    if (empty($params[$name])) {
                        $params[$name] = array();
                    }
                    switch ($name) {
                        case 'readinggoal':
                        case 'wordcount':
                            $value = number_format($value);
                            break;
                        case 'relativetime':
                            $value = format_time($value);
                            break;
                        case 'enrolment':
                            $value = $this->get_enrolment_types($plugin, $value);
                            break;
                    }
                    $params[$name][$type] = $value;
                }
            }
        }

        // combine criteria groups
        $this->format_params_range($params, $plugin, 'readinggoal',  'min', 'max');
        $this->format_params_range($params, $plugin, 'wordcount',    'min', 'max');
        $this->format_date_range($params, $plugin, 'fixedtime',    'start', 'end');
        $this->format_params_range($params, $plugin, 'relativetime', 'start', 'end');
        $this->format_params_range($params, $plugin, 'book',     'include', 'exclude');
        $this->format_params_range($params, $plugin, 'username', 'include', 'exclude');
        $this->format_params_range($params, $plugin, 'activity', 'include', 'exclude');
        $this->format_params_range($params, $plugin, 'course',   'include', 'exclude');
        $this->format_params_range($params, $plugin, 'category', 'include', 'exclude');

        $strman = get_string_manager();
        foreach ($params as $name => $types) {
            foreach ($types as $type => $value) {
                $str = $name.'_'.$type;
                if ($strman->string_exists($str, $plugin)) {
                    $str = $strman->get_string($str, $plugin, $value);
                }
                if ($type=='range') {
                    $details[] = $str;
                } else {
                    $details[] = $str.': '.$value;
                }
            }
        }

        if ($short) {
            return implode(', ', $details);
        } else {
            return html_writer::alist($details, array(), 'ul');
        }
    }

    /**
     * Formet a parameter range, such as readinggoal_min - readinggoal_max
     *
     * @param array  $params
     * @param string $plugin
     * @param string $name
     * @param string $type1
     * @param string $type2
     */
    protected function format_params_range(&$params, $plugin, $name, $type1, $type2) {
        if (array_key_exists($name, $params)) {
            if (array_key_exists($type1, $params[$name]) && array_key_exists($type2, $params[$name])) {
                $params[$name]['range'] = (object)array(
                    $type1 => $params[$name][$type1],
                    $type2 => $params[$name][$type2]
                );
                unset($params[$name][$type1]);
                unset($params[$name][$type2]);
            }
        }
    }

   /**
     * format_date_range
     *
     * @params string  $dateformat
     * @params integer $timenow
     * @params string  $timestart
     * @params string  $timefinish
     * @return array
     */
    protected function format_date_range(&$params, $plugin, $name, $type1, $type2) {

        $time1 = 0;
        $time2 = 0;
        $dateformat = $this->customdatefmt;

        if (array_key_exists($name, $params)) {

            if (array_key_exists($type1, $params[$name])) {
                $time1 = $params[$name][$type1];
                $time1 = preg_replace('/[^0-9]+/', '', $time1);
            }
            if (array_key_exists($type2, $params[$name])) {
                $time2 = $params[$name][$type2];
                $time2 = preg_replace('/[^0-9]+/', '', $time2);
            }

            $removetime1 = ($time1 && (strftime('%H:%M', $time1)=='00:00'));
            $removetime2 = ($time2 && (strftime('%H:%M', $time2)=='23:55'));
            $removetime = ($removetime1 && $removetime2);
            $removedate = false;

            $date = '';
            if ($time1 && $time2) {
                if (($time2 - $time1) < DAYSECS) {
                    // the dates are less than 24 hours apart, so don't remove times ...
                    $removetime = false;
                    // ... but remove the finish date ;-)
                    $removedate = true;
                }
                $time1 = $this->userdate($time1, $dateformat, $removetime);
                $time2 = $this->userdate($time2, $dateformat, $removetime, $removedate);
            } else if ($time1) {
                $time1 = $this->userdate($time1, $dateformat, $removetime1);
            } else if ($time2) {
                $time2 = $this->userdate($time2, $dateformat, $removetime2);
            }

            $params[$name][$type1] = $time1;
            $params[$name][$type2] = $time2;
        }

        $this->format_params_range($params, $plugin, $name, $type1, $type2);
    }

    /**
     * userdate
     *
     * @param integer $date
     * @param string  $format
     * @param boolean $removetime
     * @param boolean $removedate (optional, default = false)
     * @return string representation of $date
     */
    protected function userdate($date, $format, $removetime, $removedate=false) {

        $current_language = substr(current_language(), 0, 2);

        if ($removetime) {
            // http://php.net/manual/en/function.strftime.php
            $search = '/[ :,\-\.\/]*[\[\{\(]*?%[HkIlMpPrRSTX][\)\}\]]?/';
            $format = preg_replace($search, '', $format);
        }

        if ($removedate) {
            // http://php.net/manual/en/function.strftime.php
            $search = '/[ :,\-\.\/]*[\[\{\(]*?%[AadejuwbBhmCgGyY][\)\}\]]?/';
            $format = preg_replace($search, '', $format);
        }

        // set the $year, $month and $day characters for CJK languages
        list($year, $month, $day) = $this->get_date_chars();

        // add year, month and day characters for CJK languages
        if ($this->fixyearchar || $this->fixmonthchar || $this->fixdaychar) {
            $replace = array();
            if ($this->fixyearchar) {
                $replace['%y'] = '%y'.$year;
                $replace['%Y'] = '%Y'.$year;
            }
            if ($this->fixmonthchar) {
                $replace['%b'] = '%b'.$month;
                $replace['%h'] = '%h'.$month;
            }
            if ($this->fixdaychar) {
                $replace['%d'] = '%d'.$day;
            }
            $format = strtr($format, $replace);
        }

        if ($fixmonth = ($this->fixmonth && is_numeric(strpos($format, '%m')))) {
            $format = str_replace('%m', 'MM', $format);
        }
        if ($fixday = ($this->fixday && is_numeric(strpos($format, '%d')))) {
            $format = str_replace('%d', 'DD', $format);
        }
        if ($fixhour = ($this->fixhour && is_numeric(strpos($format, '%I')))) {
            $format = str_replace('%I', 'II', $format);
        }

        $userdate = userdate($date, $format, 99, false, false);

        if ($fixmonth || $fixday || $fixhour) {
            $search = array(' 0', ' ');
            $replace = array();
            if ($fixmonth) {
                $month = strftime(' %m', $date);
                $month = str_replace($search, '', $month);
                $replace['MM'] = ltrim($month);
            }
            if ($fixday) {
                if ($current_language=='en') {
                    $day = date(' jS', $date);
                } else {
                    $day = strftime(' %d', $date);
                    $day = str_replace($search, '', $day);
                }
                $replace['DD'] = ltrim($day);
            }
            if ($fixhour) {
                $hour = strftime(' %I', $date);
                $hour = str_replace($search, '', $hour);
                $replace['II'] = ltrim($hour);
            }
            $userdate = strtr($userdate, $replace);
        }

        return $userdate;
    }

    /**
     * check_date_fixes
     */
    protected function check_date_fixes() {

        if (! $dateformat = $this->customdatefmt) {
            if (! $dateformat = $this->moodledatefmt) {
                $dateformat = 'strftimerecent'; // default: 11 Nov, 10:12
            }
            $dateformat = get_string($dateformat);
        }

        $date = strftime($dateformat, time());

        // set the $year, $month and $day characters for CJK languages
        list($year, $month, $day) = $this->get_date_chars();

        if ($day && ! preg_match("/[0-9]+$year/", $date)) {
            $this->fixyearchar = true;
        }
        if ($day && ! preg_match("/[0-9]+$month/", $date)) {
            $this->fixmonthchar = true;
        }
        if ($day && ! preg_match("/[0-9]+$day/", $date)) {
            $this->fixdaychar = true;
        }
    }

    /**
     * get_date_chars
     *
     * @return array($year, $month, $day)
     */
    protected function get_date_chars() {
        switch (substr(current_language(), 0, 2)) {
            case 'ja': return array('年', '月', '日'); // Japanese
            case 'ko': return array('년', '월', '일'); // Korean
            case 'zh': return array('年', '月', '日'); // Chinese
            default  : return array('',  '',   '');
        }
    }

    /**
     * Saves intial criteria records with required parameters set up.
     *
     * @param array $params Values from the form or any other array.
     */
    public function save($params = array()) {
        // reduce any multi-select elements to a comma separated list
        foreach ($this->get_multiselect_element_names() as $name) {
            if (array_key_exists($name, $params) && is_array($params[$name])) {
                $params[$name] = array_filter($params[$name]);
                $params[$name] = implode(', ', $params[$name]);
            }
        }
        // Continue with parent save method
        parent::save($params);
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
