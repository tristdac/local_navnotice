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
 *
 * @package    local_navnotice
 * @copyright  2024 Tristan daCosta, Edinburgh College <moodle@edinburghcollege.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

class manage_navnotice_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', '');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('select', 'type', get_string('type', 'local_navnotice'), [
            'navitem' => get_string('navitem', 'local_navnotice'),
            'notification' => get_string('notification', 'local_navnotice')
        ]);
        $mform->setType('type', PARAM_TEXT);

        $mform->addElement('select', 'usertype', get_string('usertype', 'local_navnotice'), [
            'all' => get_string('all', 'local_navnotice'),
            'student' => get_string('student', 'local_navnotice'),
            'staff' => get_string('staff', 'local_navnotice'),
            'external' => get_string('external', 'local_navnotice')
        ]);
        $mform->setType('usertype', PARAM_TEXT);

        $mform->addElement('text', 'title', get_string('title', 'local_navnotice'));
        $mform->setType('title', PARAM_TEXT);
        $mform->hideIf('title', 'type', 'neq', 'navitem');

        $mform->addElement('text', 'url', get_string('url', 'local_navnotice'));
        $mform->setType('url', PARAM_URL);
        $mform->hideIf('url', 'type', 'neq', 'navitem');

        $mform->addElement('text', 'icon', get_string('icon', 'local_navnotice'));
        $mform->setType('icon', PARAM_TEXT);
        $mform->hideIf('icon', 'type', 'neq', 'navitem');
        $mform->addHelpButton('icon', 'icon', 'local_navnotice');

        $mform->addElement('editor', 'content', get_string('content', 'local_navnotice'));
        $mform->setType('content', PARAM_RAW);
        $mform->hideIf('content', 'type', 'neq', 'notification');
        $mform->addHelpButton('content', 'content', 'local_navnotice');

        $mform->addElement('select', 'alerttype', get_string('alerttype', 'local_navnotice'), [
            'info' => get_string('info', 'local_navnotice'),
            'success' => get_string('success', 'local_navnotice'),
            'warning' => get_string('warning', 'local_navnotice'),
            'danger' => get_string('danger', 'local_navnotice')
        ]);
        $mform->setType('alerttype', PARAM_TEXT);
        $mform->hideIf('alerttype', 'type', 'neq', 'notification');

        $this->add_action_buttons();
    }

    function definition_after_data() {
        parent::definition_after_data();

        // Ensure the initial visibility of fields is correct
        $this->update_visibility($this->_form->getElement('type')->getValue());
    }

    private function update_visibility($type) {
        $mform = $this->_form;

        if ($type == 'navitem') {
            $mform->getElement('title')->setAttributes(['style' => 'display:flex;']);
            $mform->getElement('url')->setAttributes(['style' => 'display:flex;']);
            $mform->getElement('icon')->setAttributes(['style' => 'display:flex;']);
            $mform->getElement('content')->setAttributes(['style' => 'display:none;']);
            $mform->getElement('alerttype')->setAttributes(['style' => 'display:none;']);
        } else if ($type == 'notification') {
            $mform->getElement('title')->setAttributes(['style' => 'display:none;']);
            $mform->getElement('url')->setAttributes(['style' => 'display:none;']);
            $mform->getElement('icon')->setAttributes(['style' => 'display:none;']);
            $mform->getElement('content')->setAttributes(['style' => 'display:flex;']);
            $mform->getElement('alerttype')->setAttributes(['style' => 'display:flex;']);
        }
    }
}
