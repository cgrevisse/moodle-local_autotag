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
 * Callback implementations for AutoTag
 *
 * @package    local_autotag
 * @copyright  2025 Christian Grévisse <christian.grevisse@uni.lu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Add the AutoTag button to the add/edit resource page
 *
 * @param moodleform $formwrapper Moodleform wrapper
 * @param MoodleQuickForm $mform Moodle Mform that we want to add our code to.
 */
function local_autotag_coursemodule_standard_elements($formwrapper, $mform) {
    $context = context_course::instance($formwrapper->get_course()->id);
    $resourceid = $formwrapper->get_current()->coursemodule;

    // Only enable this for resource modules.
    if ($formwrapper->get_current()->modulename != 'resource') {
        return;
    }

    $mform->addElement('header', 'autotagsection', get_string('pluginname', 'local_autotag'));

    // Add a button to the form.
    $mform->addElement('button', 'autotagbutton', get_string('pluginname', 'local_autotag'));
    $mform->addHelpButton('autotagbutton', 'autotagbutton', 'local_autotag');

    // Add Javascript module.
    global $PAGE;
    $PAGE->requires->js_call_amd('local_autotag/autotag', 'init', [$resourceid]);
}

/**
 * Get the name, extension and path on the file storage for the first file associated to a resource (if any).
 *
 * @param int $resourceid The ID of the resource
 * @return stdClass The file info
 */
function local_autotag_get_fileinfo_for_resource(int $resourceid) {
    $fs = get_file_storage();
    $cmid = context_module::instance($resourceid)->id;
    $files = $fs->get_area_files($cmid, 'mod_resource', 'content', 0, 'filename', false);
    $file = reset($files);

    if ($file) {
        $filename = $file->get_filename();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $path = $fs->get_file_system()->get_remote_path_from_storedfile($file);

        return (object) [
            "file" => $file,
            "name" => $filename,
            "extension" => strtolower($extension),
            "path" => $path,
        ];
    } else {
        return null;
    }
}
