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

function local_navnotice_before_http_headers() {
    global $USER, $PAGE, $DB;

    if (!get_config('local_navnotice', 'enable')) {
        return;
    }

    // Get the current URL path from $_SERVER['REQUEST_URI']
    $current_url = $_SERVER['REQUEST_URI'];
    $url_path = parse_url($current_url, PHP_URL_PATH);

    // Check if we are on the settings page
    if ($url_path === '/local/navnotice/manage.php') {
        // Include JavaScript for contrast checking
        $PAGE->requires->js('/local/navnotice/js/check_contrast.js');
    }

    // Function to determine user type from email
    if (!function_exists('get_user_type_from_email')) {
        function get_user_type_from_email($email) {
            $student_pattern = get_config('local_navnotice', 'student_email_pattern');
            $staff_pattern = get_config('local_navnotice', 'staff_email_pattern');

            if (preg_match("/$student_pattern/", $email)) {
                return 'student';
            } elseif (preg_match("/$staff_pattern/", $email)) {
                return 'staff';
            } else {
                return 'external';
            }
        }
    }

    // Get the user type based on the email address
    if (isloggedin() && !isguestuser()) {
        $user_type_class = get_user_type_from_email($USER->email);
    } else {
        $user_type_class = 'external';
    }

    // Inject the class into the body tag
    if ($user_type_class) {
        $PAGE->add_body_class($user_type_class);
    }

    // Fetch navbar items and notifications from the database
    $items = $DB->get_records('local_navnotice_items');
    $colorData = [];

    foreach ($items as $item) {
        // Show to all users or specific user type
        if ($item->usertype === 'all' || $item->usertype === $user_type_class) {
            if ($item->type === 'navitem' && isloggedin() && !isguestuser()) {
                // Adding navbar items
                add_navbar_item($item);
                $colorData['navnotice-id-' . $item->id] = $item->navcolor;
            } elseif ($item->type === 'notification') {
                // Adding notifications
                add_notification($item->content, $item->alerttype);
            }
        }
    }

    // Inline JavaScript injection
    inject_nav_colors($colorData);
    
}

function inject_nav_colors($colorData) {
    global $PAGE;
    if (!empty($colorData)) {
        $colors_json = json_encode($colorData);
        $jsCode = <<<JS
        
        document.addEventListener('DOMContentLoaded', function() {
            var navItemColors = JSON.parse('$colors_json');
            for (var key in navItemColors) {
                var navItems = document.querySelectorAll('li[data-key="' + key + '"]');
                navItems.forEach(function(navItem) {
                    navItem.style.backgroundColor = navItemColors[key];
                });
            }
        });
        
JS;
        $PAGE->requires->js_init_code($jsCode);
    }
}

function add_navbar_item($item) {
    global $PAGE;

    // Render Font Awesome icon as HTML if provided
    if (!empty($item->icon)) {
        $iconhtml = html_writer::tag('i', '', ['class' => 'navicon fa '.$item->icon, 'aria-hidden' => 'true']) . ' ';
    } else {
        $iconhtml = '';
    }

    // Add the node to the primary navigation
    $node = $PAGE->primarynav->add(
        $iconhtml . $item->title, // Add icon HTML and text
        new moodle_url($item->url),
        navigation_node::TYPE_CUSTOM
    );

    if ($node) {
        $node->showinflatnavigation = true; // Make sure it shows up in the flat navigation (Boost-based themes).
        
        // Add a custom data attribute to store the navcolor
        $node->key = 'navnotice-id-'.$item->id;
        $node->title = $item->title;
    }

    // Ensure the navigation is initialized
    $PAGE->navigation->initialise();

}

function add_notification($message, $type) {
    switch ($type) {
        case 'success':
            $notification_type = \core\output\notification::NOTIFY_SUCCESS;
            break;
        case 'warning':
            $notification_type = \core\output\notification::NOTIFY_WARNING;
            break;
        case 'danger':
            $notification_type = \core\output\notification::NOTIFY_ERROR;
            break;
        default:
            $notification_type = \core\output\notification::NOTIFY_INFO;
            break;
    }

    \core\notification::add($message, $notification_type);
}