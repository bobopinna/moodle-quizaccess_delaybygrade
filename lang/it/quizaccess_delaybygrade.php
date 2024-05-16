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

$string['delaybygrade'] = 'Obbliga l\'attesa dopo un tentativo con una valutazione minore di';
$string['delaybygrade_help'] = 'Gli utenti che hanno ottenuto meno della valutazione indicata nell\'ultimo tentativo dovranno attendere questo tempo prima di avviare un nuovo tentativo.';
$string['delaygradeerror'] = 'Questo voto è maggiore della valutazione massima del quiz.';
$string['for'] = 'per';
$string['pluginname'] = 'Regola di accesso quiz Ritardo per il voto';
$string['privacy:metadata'] = 'Il plugin \'Regola di accesso quiz Ritardo per il voto\' non memorizza dati personali.';
$string['youcannotwait'] = 'Il quiz chiude prima che tu possa iniziare un altro tentativo.';
$string['youmustwait'] = 'Devi aspettare prima di poter ritentare questo quiz. Potrai tentarlo di nuovo dopo il {$a}.';
