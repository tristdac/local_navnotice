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

function xmldb_local_navnotice_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024071200) {

        // Define table local_navnotice_items to be altered.
        $table = new xmldb_table('local_navnotice_items');

        // Adding a new field to the table.
        $field = new xmldb_field('navcolor', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'url');

        // Conditionally launch add field navcolor.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Navnotice savepoint reached.
        upgrade_plugin_savepoint(true, 2024071200, 'local', 'navnotice');
    }

    return true;
}
