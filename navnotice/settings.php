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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $ADMIN;

    $settings = new admin_settingpage('local_navnotice', get_string('pluginname', 'local_navnotice'));
    $settings->add(new admin_setting_configcheckbox('local_navnotice/enable',
        get_string('enable', 'local_navnotice'), get_string('enabledesc', 'local_navnotice'), 0));

    // Add settings for email matching criteria
    $settings->add(new admin_setting_configtext('local_navnotice/student_email_pattern',
        get_string('studentemailpattern', 'local_navnotice'),
        get_string('studentemailpatterndesc', 'local_navnotice'),
        '^ec\d+@edinburghcollege.ac.uk$'));

    $settings->add(new admin_setting_configtext('local_navnotice/staff_email_pattern',
        get_string('staffemailpattern', 'local_navnotice'),
        get_string('staffemailpatterndesc', 'local_navnotice'),
        '^[a-z]+\.[a-z]+@edinburghcollege.ac.uk$'));

    // Link to manage page
    $url = new moodle_url('/local/navnotice/manage.php');
    $link = html_writer::link($url, get_string('managesettings', 'local_navnotice'));
    $settings->add(new admin_setting_heading('local_navnotice_manage_heading', get_string('managesettings', 'local_navnotice'), $link));

    // Add an external page for managing the navbar items and notifications.
    $ADMIN->add('root', new admin_category('local_navnotice_category', new lang_string('pluginname', 'local_navnotice')));
    $settingspage = new admin_externalpage('local_navnotice_manage', get_string('managesettings', 'local_navnotice'),
        new moodle_url('/local/navnotice/manage.php'),
        'local/navnotice:manage'); // Ensure the capability exists and is assigned appropriately.

    $ADMIN->add('local_navnotice_category', $settingspage);

    $ADMIN->add('localplugins', $settings);
}

