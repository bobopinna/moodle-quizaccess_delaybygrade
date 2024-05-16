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

use mod_quiz\local\access_rule_base;
use mod_quiz\quiz_settings;

/**
 * A rule imposing the delay for next attempt by grade get in last attempt settings.
 *
 * @package   quizaccess_delaybygrade
 * @copyright 2024 UPO www.uniupo.it
 * @author    Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_delaybygrade extends access_rule_base {

    /**
     * Create a new quizaccess_delaybygrade instance.
     *
     * @param quiz_settings $quizobj The quiz object.
     * @param int $timenow Current timestamp.
     * @param boolean $canignoretimelimits Can skip time limit checks.
     * @return quizaccess_delaybygrade The new instance.
     */
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->delaybygradedelay)) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    /**
     * Return a message if user must wait for next attempt or if user can't wait because quiz close before delay pass.
     *
     * @param int $numprevattempts number of previous attempts.
     * @param stdClass $lastattempt information about the previous attempt.
     * @return string or false Waiting time message.
     */
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        if ($this->quiz->attempts > 0 && $numprevattempts >= $this->quiz->attempts) {
            // No more attempts allowed anyway.
            return false;
        }
        if ($this->quiz->timeclose != 0 && $this->timenow > $this->quiz->timeclose) {
            // No more attempts allowed anyway.
            return false;
        }
        $nextstarttime = $this->compute_next_start_time($numprevattempts, $lastattempt);
        if ($this->timenow < $nextstarttime) {
            if ($this->quiz->timeclose == 0 || $nextstarttime <= $this->quiz->timeclose) {
                return get_string('youmustwait', 'quizaccess_delaybygrade', userdate($nextstarttime));
            } else {
                return get_string('youcannotwait', 'quizaccess_delaybygrade');
            }
        }
        return false;
    }

    /**
     * Compute the next time a student would be allowed to start an attempt,
     * according to this rule.
     *
     * @param int $numprevattempts number of previous attempts.
     * @param stdClass $lastattempt information about the previous attempt.
     * @return number the time.
     */
    protected function compute_next_start_time($numprevattempts, $lastattempt) {
        if ($numprevattempts == 0) {
            return 0;
        }

        $lastattemptfinish = $lastattempt->timefinish;
        if ($this->quiz->timelimit > 0) {
            $lastattemptfinish = min($lastattemptfinish, $lastattempt->timestart + $this->quiz->timelimit);
        }

        if ($numprevattempts >= 1) {
            if (!empty($this->quiz->grade)
                    && !empty($this->quiz->delaybygradedelay)
                    && !empty($this->quiz->delaybygradegrade)) {
                $lastgrade = $lastattempt->sumgrades * $this->quiz->grade / $this->quiz->sumgrades;
                if ($lastgrade < $this->quiz->delaybygradegrade) {
                    return $lastattemptfinish + $this->quiz->delaybygradedelay;
                }
            }
        }
        return 0;
    }

    /**
     * Check if no more attempts could be started now.
     *
     * @param int $numprevattempts number of previous attempts.
     * @param stdClass $lastattempt information about the previous attempt.
     * @return boolen True if user could not start a new attempt before quiz close time or false otherwise.
     */
    public function is_finished($numprevattempts, $lastattempt) {
        $nextstarttime = $this->compute_next_start_time($numprevattempts, $lastattempt);
        return $this->timenow <= $nextstarttime && $this->quiz->timeclose != 0 && $nextstarttime >= $this->quiz->timeclose;
    }

    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from {@see mod_quiz_mod_form::definition()}, while the
     * security seciton is being built.
     *
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        global $DB;

        $rules = [];
        $rules[] = &$mform->createElement('float', 'delaybygradegrade');
        $rules[] = &$mform->createElement('static', 'delaybygradefor', null,
                get_string('for', 'quizaccess_delaybygrade'));
        $rules[] = &$mform->createElement('duration', 'delaybygradedelay');
        $rules[] = &$mform->createElement('checkbox', 'delaybygradeenabled', get_string('enable'));

        $mform->disabledIf('delaybygradegrade', 'delaybygradeenabled', 'notchecked', 1);
        $mform->disabledIf('delaybygradedelay[number]', 'delaybygradegrade', 'eq', '');
        $mform->disabledIf('delaybygradedelay[timeunit]', 'delaybygradegrade', 'eq', '');
        $mform->disabledIf('delaybygradedelay[number]', 'delaybygradeenabled', 'notchecked', 1);
        $mform->disabledIf('delaybygradedelay[timeunit]', 'delaybygradeenabled', 'notchecked', 1);

        $mform->addGroup($rules, 'delaybygrade', get_string('delaybygrade', 'quizaccess_delaybygrade'),
                ' ', false);
        $mform->addHelpButton('delaybygrade', 'delaybygrade', 'quizaccess_delaybygrade');
        $mform->hideIf('delaybygrade', 'attempts', 'eq', 1);
        $mform->setAdvanced('delaybygrade');
    }

    /**
     * Validate the data from any form fields added using {@see add_settings_form_fields()}.
     * @param array $errors the errors found so far.
     * @param array $data the submitted form data.
     * @param array $files information about any uploaded files.
     * @param mod_quiz_mod_form $quizform the quiz form object.
     * @return array $errors the updated $errors array.
     */
    public static function validate_settings_form_fields(array $errors, array $data, $files, mod_quiz_mod_form $quizform) {
        if (!empty($data['delaybygradeenabled'])) {
            if ($data['delaybygradegrade'] > $data['grade']) {
                $errors['delaybygrade'] = get_string('delaygradeerror', 'quizaccess_delaybygrade');
            }
        }

        return $errors;
    }

    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from {@see quiz_after_add_or_update()} in lib.php.
     *
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     */
    public static function save_settings($quiz) {
        global $DB;

        if (empty($quiz->delaybygradeenabled)) {
            $DB->delete_records('quizaccess_delaybygrade', ['quizid' => $quiz->id]);
        } else {
            if ($record = $DB->get_record('quizaccess_delaybygrade', ['quizid' => $quiz->id])) {
                $record->grade = $quiz->delaybygradegrade;
                $record->delay = $quiz->delaybygradedelay;
                $DB->update_record('quizaccess_delaybygrade', $record);
            } else {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->grade = $quiz->delaybygradegrade;
                $record->delay = $quiz->delaybygradedelay;
                $DB->insert_record('quizaccess_delaybygrade', $record);
            }
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from {@see quiz_delete_instance()} in lib.php.
     *
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @since Moodle 2.7.1, 2.6.4, 2.5.7
     */
    public static function delete_settings($quiz) {
        global $DB;

        $DB->delete_records('quizaccess_delaybygrade', ['quizid' => $quiz->id]);
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of {@see quiz_access_manager::load_settings()}.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return [
            'quizaccess_delaybygrade.delay delaybygradedelay,
                    quizaccess_delaybygrade.grade delaybygradegrade',
            'LEFT JOIN {quizaccess_delaybygrade} quizaccess_delaybygrade
                    ON quizaccess_delaybygrade.quizid = quiz.id',
            []];
    }

    /**
     * Define if enable checkbox is checked.
     *
     * @param int $quizid the quiz id.
     * @return array setting value name => value. The value names should all
     *      start with the name of your plugin to avoid collisions.
     */
    public static function get_extra_settings($quizid) {
        global $DB;

        if ($DB->record_exists('quizaccess_delaybygrade', ['quizid' => $quizid])) {
            return ['delaybygradeenabled' => 1];
        }

        return [];
    }
}
