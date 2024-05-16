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
 * Strings for the quizaccess_delaybygrade plugin.
 *
 * @package    quizaccess_delaybygrade
 * @copyright  2024 UPO www.uniupo.it
 * @author     Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


$string['pluginname'] = 'Delay attempts by last attempt grade quiz access rule';
$string['privacy:metadata'] = 'The delay attempts by grade quiz access rule plugin does not store any personal data.';
$string['youcannotwait'] = 'This quiz closes before you will be allowed to start another attempt.';
$string['youmustwait'] = 'You must wait before you may re-attempt this quiz. You will be allowed to start another attempt after {$a}.';
$string['delaybygrade'] = 'Enforced delay after an attempt with grade less than';
$string['delaybygrade_help'] = 'Users that get less than specified grade in last attempt must wait this time before try a new attempt.';
$string['delaygradeerror'] = 'This grade is bigger that quiz max grade.';
$string['for'] = 'for';
