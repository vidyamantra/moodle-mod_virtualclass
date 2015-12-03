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
 * Prints a particular instance of virtualclass
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_virtualclass
 * @copyright  2015 Pinky Sharma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/edit_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... virtualclass instance ID - it should be named as the first character of the module.
$update = optional_param('update', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('virtualclass', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $virtualclass  = $DB->get_record('virtualclass', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $virtualclass  = $DB->get_record('virtualclass', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $virtualclass->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('virtualclass', $virtualclass->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Print the page header.
$PAGE->set_url('/mod/virtualclass/edit.php', array('id' => $cm->id, 'update' => $update));
$PAGE->set_title(format_string($virtualclass->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
//require_once('upload_form.php');

$vc_file = $DB->get_record('virtualclass_files', array('id' => $update));
$mform = new mod_virtualclass_edit_name($CFG->wwwroot.'/mod/virtualclass/edit.php?id='.$cm->id.'&update='.$update);

if ($mform->is_cancelled()) {
    // Do nothing.
    redirect( new moodle_url('/mod/virtualclass/view.php', array('id' => $cm->id)));
} else if ($fromform = $mform->get_data()) {   
    $vc_file->vcsessionname = $fromform->name;
    //print_r($vc_file);exit;
    $DB->update_record('virtualclass_files', $vc_file);   
    redirect( new moodle_url('/mod/virtualclass/view.php', array('id' => $cm->id)));
}
// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading($virtualclass->name);
$data = new stdClass;
$data->name = $vc_file->vcsessionname;
$mform->set_data($data);
$mform->display();
// Finish the page.
echo $OUTPUT->footer();