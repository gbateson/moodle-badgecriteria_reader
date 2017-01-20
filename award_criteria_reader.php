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

    /** array($name => array($type)) criteria params */
    protected $criteria = null;

    protected $fixmonth = true;
    protected $fixday   = true;
    protected $fixhour  = true;

    protected $fixyearchar  = '';
    protected $fixmonthchar = '';
    protected $fixdaychar   = '';

    protected $customdatefmt = '%Y %b %d (%a) %H:%M';
    protected $moodledatefmt = 'strftimerecent';

    const REMOVE_NONE  = 0;
    const REMOVE_DAY   = 1;
    const REMOVE_MONTH = 2;
    const REMOVE_YEAR  = 4;

    const TEXT_NUM_SIZE = 10;
    const MULTI_SELECT_SIZE = 5;
    const MAX_READING_LEVEL = 15;
    const ENROLMENT_TYPE_NONE = 0;
    const ENROLMENT_TYPE_SITE = 1;
    const ENROLMENT_TYPE_COURSE = 2;

    /*
     * standard contructor modified to expand multiselect elements to arrays
     */
    public function __construct($params) {
        parent::__construct($params);

        // convert multiselect form elements to arrays
        foreach ($this->get_multiselect_element_names() as $name) {
            if (isset($this->params[$name]) && is_string($this->params[$name])) {
                $this->params[$name] = explode(',', $this->params[$name]);
            }
        }

        // set up $criteria[$name][$type] array
        $this->criteria = array();
        foreach ($this->params as $type => $values) {
            foreach ($values as $name => $value) {
                if ($value) {
                    if (empty($this->criteria[$name])) {
                        $this->criteria[$name] = array();
                    }
                    $this->criteria[$name][$type] = $value;
                }
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

        $dateparams = array('optional' => true);
        $textparams = array('size' => self::TEXT_NUM_SIZE);
        $listparams = array('multiple' => 'multiple', 'size' => self::MULTI_SELECT_SIZE);
        $durationparams = array('optional' => true, 'defaultunit' => 86400);
        $enrolmentparams = $this->get_enrolment_types($plugin);

        $difficulties = array();
        for ($i=0; $i<=self::MAX_READING_LEVEL; $i++) {
            if ($i==0) {
                $difficulties[$i] = get_string('difficulty_any', $plugin);
            } else {
                $difficulties[$i] = get_string('difficulty_short', $plugin, $i);
            }
        }

        $publishers = 'SELECT DISTINCT publisher FROM {reader_books} WHERE publisher <> ?';
        if ($publishers = $DB->get_records_sql($publishers, array('Extra points'))) {
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

        $this->add_fields_string_range($mform, $plugin, $textparams, 'readinggoal');

        //-----------------------------------------------------------------------------
        $name = 'timing';
        $label = get_string($name, 'form');
        $mform->addElement('header', $name, $label);
        //-----------------------------------------------------------------------------

        $this->add_field_date_range($mform, $plugin, $dateparams, 'fixedtime', 'date_time_selector');
        $this->add_field_select($mform, $plugin, null, $enrolmentparams, 'enrolment', 'type', PARAM_INT);
        $this->add_field_date_range($mform, $plugin, $durationparams, 'relativetime', 'duration');

        //-----------------------------------------------------------------------------
        $name = 'bookfilters';
        $label = get_string($name, $plugin);
        $mform->addElement('header', $name, $label);
        //-----------------------------------------------------------------------------

        $this->add_fields_string_range($mform, $plugin, $textparams, 'wordcount');

        $this->add_field_select($mform, $plugin, $listparams, $publishers,   'publishers',   'list', PARAM_TEXT);
        $this->add_field_select($mform, $plugin, $listparams, $difficulties, 'difficulties', 'list', PARAM_INT);
        $this->add_field_select($mform, $plugin, $listparams, $genres,       'genres',       'list', PARAM_ALPHA);

        $this->add_fields_include_exclude($mform, $plugin, $textparams, 'book');

        //-----------------------------------------------------------------------------
        $this->add_section_include_exclude($mform, $plugin, $textparams, 'username');
        $this->add_section_include_exclude($mform, $plugin, $textparams, 'activity');
        $this->add_section_include_exclude($mform, $plugin, $textparams, 'course');
        $this->add_section_include_exclude($mform, $plugin, $textparams, 'category');
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
            self::ENROLMENT_TYPE_NONE => get_string('none'),
            self::ENROLMENT_TYPE_SITE => get_string('enrolment_type_site', $plugin),
            self::ENROLMENT_TYPE_COURSE => get_string('enrolment_type_course', $plugin)
        );
        if ($type===null) {
            return $types;
        }
        if (array_key_exists($type, $types)) {
            return $types[$type];
        }
        return $type; // unknown $type - shouldn't happen !!
    }

    /**
     * add a filter section to $mform
     *
     * @return string
     */
    protected function add_section_include_exclude($mform, $plugin, $displayparams, $name) {
        $header = $name.'filters';
        $mform->addElement('header', $header, get_string($header, $plugin));
        $this->add_fields_include_exclude($mform, $plugin, $displayparams, $name);
    }

    /**
     * add include/exclude fields to $mform
     *
     * @return string
     */
    protected function add_fields_include_exclude($mform, $plugin, $displayparams, $name) {
        $this->add_field_string($mform, $plugin, $displayparams, $name, 'include', PARAM_TEXT, get_string('include', $plugin));
        $this->add_field_string($mform, $plugin, $displayparams, $name, 'exclude', PARAM_TEXT, get_string('exclude', $plugin));
    }

    /**
     * add a filter section to $mform
     *
     * @return string
     */
    protected function add_fields_string_range($mform, $plugin, $displayparams, $name) {
        $this->add_field_string($mform, $plugin, $displayparams, $name, 'min', PARAM_INT);
        $this->add_field_string($mform, $plugin, $displayparams, $name, 'max', PARAM_INT);
    }

    /**
     * add a single text string field to $mform
     *
     * @return string
     */
    protected function add_field_string($mform, $plugin, $displayparams, $name, $type, $PARAM_TYPE, $label='') {
        $elementname = $name.'_'.$type;
        if ($label=='') {
            $label = get_string($elementname, $plugin);
            $helpstring = $elementname; // e.g. readinggoal_min
        } else {
            $helpstring = $type; // e.g. "include", "exclude"
        }
        $mform->addElement('text', $elementname, $label, $displayparams);
        $mform->addHelpButton($elementname, $helpstring, $plugin);
        $mform->setType($elementname, $PARAM_TYPE);
        $this->set_default($mform, $name, $type);
    }

    /**
     * add a single list field to $mform
     *
     * @return string
     */
    protected function add_field_select($mform, $plugin, $displayparams, $options, $name, $type, $PARAM_TYPE) {
        $elementname = $name.'_'.$type;
        $label = get_string($elementname, $plugin);
        $mform->addElement('select', $elementname, $label, $options, $displayparams);
        $mform->addHelpButton($elementname, $elementname, $plugin);
        $mform->setType($elementname, $PARAM_TYPE);
        $this->set_default($mform, $name, $type);
    }

    /**
     * add a single list field to $mform
     *
     * @return string
     */
    protected function add_field_date_range($mform, $plugin, $displayparams, $name, $elementtype) {
        $this->add_field_date($mform, $plugin, $displayparams, $name, 'start', $elementtype);
        $this->add_field_date($mform, $plugin, $displayparams, $name, 'end',   $elementtype);
    }

    /**
     * add a single list field to $mform
     *
     * @return string
     */
    protected function add_field_date($mform, $plugin, $displayparams, $name, $type, $elementtype) {
        $elementname = $name.'_'.$type;
        $label = get_string($elementname, $plugin);
        $mform->addElement($elementtype, $elementname, $label, $displayparams);
        $mform->addHelpButton($elementname, $elementname, $plugin);
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
        $this->format_date_range($params,   $plugin, 'fixedtime',    'start', 'end');
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
            $keep1 = (array_key_exists($type1, $params[$name]) && $params[$name][$type1]);
            $keep2 = (array_key_exists($type2, $params[$name]) && $params[$name][$type2]);
            if ($keep1 && $keep2) {
                $params[$name]['range'] = (object)array(
                    $type1 => $params[$name][$type1],
                    $type2 => $params[$name][$type2]
                );
                $keep1 = false;
                $keep2 = false;
            }
            if ($keep1==false) {
                unset($params[$name][$type1]);
            }
            if ($keep2==false) {
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
                $time1 = intval($time1);
            }
            if (array_key_exists($type2, $params[$name])) {
                $time2 = $params[$name][$type2];
                $time2 = preg_replace('/[^0-9]+/', '', $time2);
                $time2 = intval($time2);
            }

            $removedate = self::REMOVE_NONE;
            if ($time1 && $time2) {
                if (strftime('%Y', $time1)==strftime('%Y', $time2)) {
                    $removedate |= self::REMOVE_YEAR;
                    if (strftime('%m', $time1)==strftime('%m', $time2)) {
                        $removedate |= self::REMOVE_MONTH;
                        if (strftime('%d', $time1)==strftime('%d', $time2)) {
                            $removedate |= self::REMOVE_DAY;
                        }
                    }
                }
            }

            if ($removedate & self::REMOVE_DAY) {
                $removetime1 = false;
                $removetime2 = false;
                $removetime  = false;
            } else {
                $removetime1 = ($time1 && (strftime('%H:%M', $time1)=='00:00'));
                $removetime2 = ($time2 && (strftime('%H:%M', $time2)=='23:55'));
                $removetime  = ($removetime1 && $removetime2);
            }

            $date = '';
            if ($time1 && $time2) {
                $time1 = $this->userdate($time1, $dateformat, $removetime);
                $time2 = $this->userdate($time2, $dateformat, $removetime, $removedate);
            } else if ($time1) {
                $time1 = $this->userdate($time1, $dateformat, $removetime1);
                $time2 = '';
            } else if ($time2) {
                $time1 = '';
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
    protected function userdate($date, $format, $removetime, $removedate=self::REMOVE_NONE) {

        $current_language = substr(current_language(), 0, 2);

        if ($removetime) {
            // http://php.net/manual/en/function.strftime.php
            $search = '/[ :,\-\.\/]*[\[\{\(]*?%[HkIlMpPrRSTX][\)\}\]]?/';
            $format = preg_replace($search, '', $format);
        }

        $search = '';
        if ($removedate & self::REMOVE_YEAR) {
            $search .= 'CgGyY';
        }
        if ($removedate & self::REMOVE_MONTH) {
            $search .= 'bBhm';
        }
        if ($removedate & self::REMOVE_DAY) {
            $search .= 'aAdejuw';
        }
        if ($search) {
            // http://php.net/manual/en/function.strftime.php
            $search = '/[ :,\-\.\/]*[\[\{\(]*?%['.$search.'][\)\}\]]?/';
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
        list($sql, $params) = $this->get_sql_criteria($userid);
        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Returns array with sql code and parameters returning all ids
     * of users who meet this particular criterion.
     *
     * @return array list($join, $where, $params)
     */
    public function get_completed_criteria_sql() {
        list($sql, $params) = $this->get_sql_criteria();
        $sql = " LEFT JOIN ($sql) bcr ON u.id = bcr.userid";
        return array($sql, ' AND bcr.userid IS NOT NULL', $params);
    }

    /**
     * Returns array with sql code and parameters returning all ids
     * of users who meet this particular criterion.
     *
     * @return array list($sql, $where, $params)
     */
    protected function get_sql_criteria($userid=0) {

        $select = array('ra.userid',
                        'SUM(rb.words) AS sumwords');
        $from   = array('{reader_attempts} ra',
                        '{reader_books} rb ON ra.bookid = rb.id');
        $where  = array('ra.deleted = :ra_deleted',
                        'ra.passed = :ra_passed');
        $group  = array('ra.userid');
        $having = array();
        $params = array('ra_deleted' => 0, 'ra_passed' => 'true');

        if ($userid) {
            $where[] = 'ra.userid = :ra_userid';
            $params['ra_userid'] = $userid;
        }
        $where = implode(' AND ', $where);
        $where  = array("($where)");

        $this->get_sql_number($having, $params, $this->criteria, 'readinggoal', 'min', 'max', 'sumwords');
        $this->get_sql_number($where,  $params, $this->criteria, 'wordcount',   'min', 'max', 'rb.words');
        $this->get_sql_number($where,  $params, $this->criteria, 'fixedtime', 'start', 'end', 'ra.timefinish');

        $name = 'relativetime';
        if (! empty($this->criteria[$name])) {
        }

        $this->get_sql_list($where, $params, $this->criteria, 'publishers',   'list', 'rb.publisher');
        $this->get_sql_list($where, $params, $this->criteria, 'difficulties', 'list', 'rb.difficulty');
        $this->get_sql_list($where, $params, $this->criteria, 'genres',       'list', 'rb.genre');

        $this->get_sql_string($where, $params, $this->criteria, 'book', 'include', 'exclude', 'rb.name');
        $require_user = $this->get_sql_string($where, $params, $this->criteria, 'username', 'include', 'exclude', 'ub.username');
        $require_activity = $this->get_sql_string($where, $params, $this->criteria, 'activity', 'include', 'exclude', 'r.name');
        $require_course = $this->get_sql_string($where, $params, $this->criteria, 'course', 'include', 'exclude', 'c.fullname');
        $require_category = $this->get_sql_string($where, $params, $this->criteria, 'category', 'include', 'exclude', 'cc.name');

        if ($require_user) {
            // give this table an alias other than "u", in order to
            // avoid a clash with the "u" alias that is added by
            // the method calling "get_completed_criteria_sql"
            $from[] = '{user} ub ON ra.userid = ub.id';
        }
        if ($require_activity || $require_course || $require_category) {
            $from[] = '{reader} r ON ra.readerid = r.id';
        }
        if ($require_course || $require_category) {
            $from[] = '{course} c ON r.course = c.id';
        }
        if ($require_category) {
            $from[] = '{course_category} cc ON c.category = cc.id';
        }

        $AND = (($this->method==BADGE_CRITERIA_AGGREGATION_ANY) ? 'OR' : 'AND');
        $JOIN = 'LEFT JOIN';

        $select = implode(', ', $select);
        $from   = implode(" $JOIN ", $from);
        $where  = implode(" $AND ", $where);
        $group  = implode(', ', $group);
        $having = implode(" $AND ", $having);

        $sql = "SELECT $select FROM $from WHERE $where GROUP BY $group HAVING $having";
        return array($sql, $params);
    }

    /**
     * get_sql_number
     *
     * @param array  $where  (passed by reference)
     * @param array  $params (passed by reference)
     * @param array  $criteria
     * @param string $name, e.g. "readinggoal", "wordcount", "fixedtime"
     * @param string $type1, e.g. "min", "start"
     * @param string $type2, e.g. "max", "end"
     * @param string $field
     * @return boolean TRUE if $where, $params were updated; otherwise FALSE
     */
    protected function get_sql_number(&$where, &$params, $criteria, $name, $type1, $type2, $field) {
        $localwhere = array();
        $localparams = array();
        if (! empty($criteria[$name])) {
            if (! empty($criteria[$name][$type1])) {
                $alias = $name.'_'.$type1;
                $localwhere[] = "$field >= :$alias";
                $localparams[$alias] = $criteria[$name][$type1];
            }
            if (! empty($criteria[$name][$type2])) {
                $alias = $name.'_'.$type2;
                $localwhere[] = "$field <= :$alias";
                $localparams[$alias] = $criteria[$name][$type2];
            }
        }
        return $this->get_sql_result($where, $params, $localwhere, $localparams);
    }

    /**
     * get_sql_string
     *
     * @param array  $where  (passed by reference)
     * @param array  $params (passed by reference)
     * @param array  $criteria
     * @param string $name, e.g. "username", "(book)name"
     * @param string $type1 e.g. "include"
     * @param string $type2 e.g. "exclude"
     * @param string $field
     * @return boolean TRUE if $where, $params were updated; otherwise FALSE
     */
    protected function get_sql_string(&$where, &$params, $criteria, $name, $type1, $type2, $field) {
        global $DB;
        $localwhere = array();
        $localparams = array();
        if (! empty($criteria[$name])) {
            if (! empty($criteria[$name][$type1])) {
                $alias = $name.'_'.$type1;
                $localwhere[] = $DB->sql_like($field, ":$alias");
                $localparams[$alias] = '%'.$criteria[$name][$type1].'%';
            }
            if (! empty($criteria[$name][$type2])) {
                $alias = $name.'_'.$type2;
                $localwhere[] = $DB->sql_like($field, ":$alias", false, false, true);
                $localparams[$alias] = '%'.$criteria[$name][$type2].'%';
            }
        }
        return $this->get_sql_result($where, $params, $localwhere, $localparams);
    }

    /**
     * get_sql_list
     *
     * @param array  $where  (passed by reference)
     * @param array  $params (passed by reference)
     * @param array  $criteria
     * @param string $name, e.g. "publishers", "difficuulties", or "genres"
     * @param string $type, e.g. "list"
     * @param string $field
     * @return boolean TRUE if $where, $params were updated; otherwise FALSE
     */
    protected function get_sql_list(&$where, &$params, $criteria, $name, $type, $field) {
        global $DB;
        $localwhere = array();
        $localparams = array();
        if (! empty($criteria[$name])) {
            if (! empty($criteria[$name][$type])) {
                $list = $criteria[$name][$type];
                $list = explode(',', $list);
                $list = array_filter($list);
                if (! empty($list)) {
                    $list = $DB->get_in_or_equal($list, SQL_PARAMS_NAMED, $name);
                    list($list, $localparams) = $list;
                    if ($list) {
                        $localwhere[] = "$field $list";
                    }
                }
            }
        }
        return $this->get_sql_result($where, $params, $localwhere, $localparams);
    }

    /**
     * get_sql_result
     *
     * @param array  $where  (passed by reference)
     * @param array  $params (passed by reference)
     * @param array  $localwhere
     * @param array  $localparams
     * @return boolean TRUE if $where, $params were updated; otherwise FALSE
     */
    protected function get_sql_result(&$where, &$params, $localwhere, $localparams) {
        if ($count = count($localwhere)) {
            if ($count==1) {
                $where[] = reset($localwhere);
            } else {
                $where[] = '('.implode(' AND ', $localwhere).')';
            }
            $params = array_merge($params, $localparams);
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}
