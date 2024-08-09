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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/navnotice/classes/form/edit_form.php');

admin_externalpage_setup('local_navnotice_manage');

$PAGE->set_title(get_string('managesettings', 'local_navnotice'));
$PAGE->set_heading(get_string('managesettings', 'local_navnotice'));

echo $OUTPUT->header();

$editid = optional_param('edit', 0, PARAM_INT);
$deleteid = optional_param('delete', 0, PARAM_INT);
$addingnew = optional_param('add', 0, PARAM_BOOL);

// Handle deletions
if ($deleteid) {
    $DB->delete_records('local_navnotice_items', ['id' => $deleteid]);
    redirect(new moodle_url('/local/navnotice/manage.php'));
}

// Create or edit form instance
$is_editing = $editid > 0;
$mform = new manage_navnotice_form(null, ['is_editing' => $is_editing]);

$showform = false;
$formhtml = '';
if ($editid) {
    $record = $DB->get_record('local_navnotice_items', ['id' => $editid]);
    if (!$record) {
        print_error('errorrecordnotfound', 'local_navnotice', '', null, 'Record not found');
    }

    // Ensure editor content is correctly set
    $record->content = ['text' => $record->content, 'format' => FORMAT_HTML];
    $mform->set_data($record);
    $showform = true;
    ob_start();
    $mform->display();
    $formhtml = ob_get_clean();
} elseif ($addingnew) {
    $showform = true;
    ob_start();
    $mform->display();
    $formhtml = ob_get_clean();
}

// Handle form submission
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/navnotice/manage.php'));
} else if ($fromform = $mform->get_data()) {
    $record = new stdClass();
    $record->type = $fromform->type;
    $record->usertype = $fromform->usertype;

    // Handling title
    $record->title = !empty($fromform->title) ? $fromform->title : NULL;

    // Handling URL
    $record->url = !empty($fromform->url) ? $fromform->url : NULL;

    // Handling navcolor
    $record->navcolor = !empty($fromform->navcolor) ? $fromform->navcolor : NULL;

    // Handling editor content
    $record->icon = !empty($fromform->icon) ? $fromform->icon : '';

    // Handling editor content
    $record->content = !empty($fromform->content['text']) ? $fromform->content['text'] : '';

    // Handling alerttype
    $record->alerttype = !empty($fromform->alerttype) ? $fromform->alerttype : NULL;

    if (empty($fromform->id)) {
        // Inserting new record
        $DB->insert_record('local_navnotice_items', $record);
    } else {
        // Updating existing record
        $record->id = $fromform->id;
        $DB->update_record('local_navnotice_items', $record);
        global $SESSION;
        unset($SESSION->notifications);
    }

    // Redirect after processing
    redirect(new moodle_url('/local/navnotice/manage.php'));
}


// Display existing entries with edit/delete options
$entries = $DB->get_records('local_navnotice_items');
foreach ($entries as $entry) {
    echo html_writer::start_div('entry card mb-3 p-3');
    echo html_writer::div('<strong>Type:</strong> ' . $entry->type, 'mb-1 cap');
    echo html_writer::div('<strong>User Type:</strong> ' . $entry->usertype, 'mb-1 cap');

    if ($entry->type === 'navitem') {
        echo html_writer::div('<strong>Title:</strong> ' . $entry->title, 'mb-1 cap');
        echo html_writer::div('<strong>URL:</strong> ' . '<a href="'.$entry->url.'" target="_blank">'.$entry->url.'</a>', 'mb-1');
        echo html_writer::div('<strong>Icon:</strong> ' . (!empty($entry->icon) ? '<i class="fa ' . $entry->icon . '"></i> ('. $entry->icon . ')' : 'None'), 'mb-1');
    } else if ($entry->type === 'notification') {
        // Use format_text to safely display HTML content
        echo html_writer::div('<strong>Alert Type:</strong> ' . $entry->alerttype, 'mb-1 cap');
        echo html_writer::empty_tag('br');
        echo html_writer::start_div('alert alert-'.$entry->alerttype);
        echo html_writer::div(format_text($entry->content, FORMAT_HTML));
        echo html_writer::end_div();
    }

    // Icons for actions
    echo html_writer::start_div('card-actions text-right mt-2');
    echo html_writer::link(new moodle_url('/local/navnotice/manage.php', ['edit' => $entry->id], 'formContainer'), 
        html_writer::tag('i', '', ['class' => 'fa fa-pencil fa-lg']),
        ['class' => 'btn btn-dark btn-sm mr-1', 'title' => get_string('edit')]
    );
    echo html_writer::link(new moodle_url('/local/navnotice/manage.php', ['delete' => $entry->id]), 
        html_writer::tag('i', '', ['class' => 'fa fa-trash fa-lg']),
        ['class' => 'btn btn-danger btn-sm', 'title' => get_string('delete')]
    );
    echo html_writer::end_div(); // card-actions

    echo html_writer::end_div(); // card
}

// Show add new button
if (!$showform) {
    echo html_writer::link(new moodle_url('/local/navnotice/manage.php', ['add' => 1], 'formContainer'), 
        get_string('additem', 'local_navnotice'), 
        ['class' => 'btn btn-success mb-3', 'id' => 'addNewItemButton']
    );
}

// Ensure a hidden form container is always present to prevent JavaScript errors
echo '<div id="formContainer" style="' . ($showform ? '' : 'display:none;') . '">';
if ($showform) {
    echo $formhtml;
}
echo '</div>';

echo html_writer::script("
    document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.getElementById('addNewItemButton');
        const formContainer = document.getElementById('formContainer');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                if (formContainer) {
                    formContainer.innerHTML = " . json_encode($formhtml) . ";
                    formContainer.style.display = 'flex'; // Show the form container
                    const form = document.getElementById('manageForm');
                    if (form) {
                        form.style.display = 'flex'; // Show the form
                    }
                    addBtn.style.display = 'none'; // Hide the add button
                }
            });
        }

        // Add JavaScript to dynamically show/hide elements based on type
        const typeElement = document.getElementById('id_type');
        if (typeElement) {
            typeElement.addEventListener('change', function() {
                const selectedType = this.value;
                updateVisibility(selectedType);
                document.getElementById('id_type_hidden').value = selectedType; // Update hidden input
            });
            // Call updateVisibility on initial load to set correct visibility
            updateVisibility(typeElement.value);
        }

        function updateVisibility(type) {
            const titleElement = document.getElementById('fitem_id_title');
            const urlElement = document.getElementById('fitem_id_url');
            const iconElement = document.getElementById('fitem_id_icon');
            const contentElement = document.getElementById('fitem_id_content');
            const alerttypeElement = document.getElementById('fitem_id_alerttype');
            const navcolorElement = document.getElementById('fitem_id_navcolor');

            if (type === 'navitem') {
                titleElement.style.display = 'flex';
                urlElement.style.display = 'flex';
                iconElement.style.display = 'flex';
                navcolorElement.style.display = 'flex';
                contentElement.style.display = 'none';
                alerttypeElement.style.display = 'none';
            } else if (type === 'notification') {
                titleElement.style.display = 'none';
                urlElement.style.display = 'none';
                iconElement.style.display = 'none';
                navcolorElement.style.display = 'none';
                contentElement.style.display = 'flex';
                alerttypeElement.style.display = 'flex';
            }
        }
            const selectTypeElement = document.getElementById('id_type');
    const selectAlertTypeElement = document.getElementById('id_alerttype');

    // Function to update class on the contenteditable element
    function updateClass() {
        const contentEditableElement = document.querySelector('#id_contenteditable');
        if (contentEditableElement) {
            // Remove existing alert classes
            contentEditableElement.classList.remove('alert-info', 'alert-success', 'alert-warning', 'alert-danger');

            // Add new class based on the selected alert type option
            const selectedAlertValue = selectAlertTypeElement.value;
            const alertClass = 'alert-' + selectedAlertValue;
            contentEditableElement.classList.add(alertClass);

            // Update CSS styles
            contentEditableElement.style.minHeight = '80px';
            contentEditableElement.style.height = '80px';
            contentEditableElement.style.padding = '.75rem 1.25rem';
            contentEditableElement.style.border = '1px solid #8f959e';
        }
    }

    // Monitor the DOM for changes and apply the class update if the contenteditable element exists
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes) {
                updateClass();
            }
        });
    });

    // Configuration for the observer (which parts of the DOM to monitor)
    const config = { childList: true, subtree: true };
    // Start observing the body for added elements
    observer.observe(document.body, config);

    // Handle type dropdown changes that may affect the rendering of contenteditable
    selectTypeElement.addEventListener('change', function() {
        if (this.value === 'notification') {
            updateClass();
        }
    });

    // Ensure the class is updated based on initial load and dropdown changes
    selectAlertTypeElement.addEventListener('change', updateClass);
    });
");

echo $OUTPUT->footer();
