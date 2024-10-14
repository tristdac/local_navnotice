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

    if (isloggedin() && !isguestuser()) {
        $user_type_class = get_user_type_from_email($USER->email);
    } else {
        $user_type_class = 'external';
    }

    if ($user_type_class) {
        $PAGE->add_body_class($user_type_class);
    }

    // Fetch navbar items and notifications from the database
    $items = $DB->get_records('local_navnotice_items');

    $navStyles = [];
    foreach ($items as $item) {
        if ($item->usertype === 'all' || $item->usertype === $user_type_class) {
            if ($item->type === 'navitem' && isloggedin() && !isguestuser()) {
                add_navbar_item($item);

                $backgroundColor = $item->backgroundcolor ?? '';
                $textColor = $item->textcolor ?? '';

                // Add item ID with both colours to the array
                $navStyles[$item->id] = [
                    'backgroundColor' => $backgroundColor,
                    'textColor' => $textColor
                ];
            } elseif ($item->type === 'notification') {
                add_notification($item->content, $item->alerttype);
            }
        }
    }

    $navStylesJson = json_encode($navStyles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $jsCode = <<<JS
    document.addEventListener('DOMContentLoaded', function() {
        var navItemColors = JSON.parse('$navStylesJson');
        console.log('Parsed navItemColors:', navItemColors); // Log the parsed color data

        for (var key in navItemColors) {
            console.log('Processing key:', key); // Log the current key

            var selector = 'li[data-key="navnotice-id-' + key + '"]';
            console.log('Selector:', selector); // Log the selector being used

            var navItems = document.querySelectorAll(selector);
            console.log('Found navItems:', navItems); // Log the found navigation items

            navItems.forEach(function(navItem) {
                console.log('Applying background to:', navItem); // Log the specific <li> item being styled
                navItem.style.backgroundColor = navItemColors[key].backgroundColor;

                var link = navItem.querySelector('a'); // Select the <a> tag within the <li>
                if (link) {
                    console.log('Applying text color to:', link); // Log the specific <a> tag being styled
                    link.style.color = navItemColors[key].textColor;
                }

                console.log('Applied backgroundColor:', navItemColors[key].backgroundColor);
                console.log('Applied textColor:', navItemColors[key].textColor);
            });
        }
    });
    JS;

    $PAGE->requires->js_init_code($jsCode);


    // Include the JavaScript file
    $PAGE->requires->js('/local/navnotice/js/navnotice.js');
}

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

// function inject_nav_colors($colorData) {
//     global $PAGE;
//     if (!empty($colorData)) {
//         $colors_json = json_encode($colorData);
//         $jsCode = <<<JS
//         document.addEventListener('DOMContentLoaded', function() {
//             var navItemColors = JSON.parse('$colors_json');
//             console.log('Parsed navItemColors:', navItemColors); // Log the parsed color data

//             for (var key in navItemColors) {
//                 console.log('Processing key:', key); // Log the current key

//                 var selector = 'li[data-key="navnotice-id-' + key + '"]';
//                 console.log('Selector:', selector); // Log the selector being used

//                 var navItems = document.querySelectorAll(selector);
//                 console.log('Found navItems:', navItems); // Log the found navigation items

//                 navItems.forEach(function(navItem) {
//                     console.log('Applying styles to:', navItem); // Log the specific item being styled
//                     navItem.style.backgroundColor = navItemColors[key].backgroundColor;
//                     navItem.style.color = navItemColors[key].textColor;
//                     console.log('Applied backgroundColor:', navItemColors[key].backgroundColor);
//                     console.log('Applied textColor:', navItemColors[key].textColor);
//                 });
//             }
//         });
// JS;
//         $PAGE->requires->js_init_code($jsCode);
//     }
// }

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