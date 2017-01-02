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
 * This file contains the version information for the Reader badge criteria
 *
 * @package   badgecriteria_reader
 * @copyright 2016 Gordon Bateson {@link http://github.com/gbateson}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->cron      = 0;
$plugin->component = 'badgecriteria_reader';
$plugin->maturity  = MATURITY_BETA; // ALPHA=50, BETA=100, RC=150, STABLE=200
$plugin->requires  = 2012062500;    // Moodle 2.3
$plugin->version   = 2017010205;
$plugin->release   = '2017-01-02 (05)';