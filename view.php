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
 * @copyright  2014 Pinky Sharma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');


$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... virtualclass instance ID - it should be named as the first character of the module.

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
$PAGE->set_url('/mod/virtualclass/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($virtualclass->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading($virtualclass->name);

// If vidya.io API key missing.
if (!$licen = get_config('local_getkey', 'keyvalue')) {
    $url = new moodle_url('/local/getkey/index.php');
    echo $OUTPUT->notification(get_string('notsavekey', 'virtualclass', $url->out(false)));
    echo $OUTPUT->footer();
    exit();
} else {
    require_once('auth.php');
}

$a = new stdClass();
$a->open = userdate($virtualclass->opentime);
$a->close = userdate($virtualclass->closetime);
$user = $DB->get_record('user', array('id' => $virtualclass->moderatorid));

echo html_writer::tag('div', get_string('virtualclasstiming', 'mod_virtualclass', $a));
echo html_writer::tag('div', get_string('teachername', 'mod_virtualclass', $user));
// Conditions to show the intro can change to look for own settings or whatever.
if ($virtualclass->intro) {
    echo $OUTPUT->box(format_module_intro('virtualclass', $virtualclass, $cm->id), 'generalbox mod_introbox', 'virtualclassintro');
}

// Check virtualclass is open.
if ($virtualclass->closetime > time() && $virtualclass->opentime <= time()) {
    echo html_writer::script('', $CFG->wwwroot.'/mod/virtualclass/popup.js');
    $popupname = 'Virtualclasspopup';
    $popupwidth = 'window.screen.width';
    $popupheight = 'window.screen.height';
    $popupoptions = "toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $room = $course->id . "_" . $cm->id;

    if ($CFG->virtualclass_serve) {

        // Serve local files.
        $url = new moodle_url($CFG->wwwroot.'/mod/virtualclass/classroom.php', array('id' => $id));
        $vcpopup = js_writer::function_call('virtualclass_openpopup', Array($url->out(false),
                                                       $popupname, $popupoptions,
                                                       $popupwidth, $popupheight));

        echo html_writer::start_tag('button', array('value' => get_string('joinroom', 'virtualclass'),
                     'id' => 'vc', 'onclick' => $vcpopup));
        echo get_string('joinroom', 'virtualclass');
        echo html_writer::end_tag('button');
    } else {
        // Serve online at vidya.io.
        $url = "https://l.vidya.io";  // Online url
        $role = 's'; // Default role.
        $info = false; // Debugging off.

        if (has_capability('mod/virtualclass:addinstance', $context)) {
            if ($USER->id == $virtualclass->moderatorid) {
                $role = 't';
            }
        }
        if ($CFG->debug == 32767 && $CFG->debugdisplay == 1) {
            $info = true;
        }
        $form = virtualclass_online_server($url, $authusername, $authpassword, $role, $rid, $room,
                    $popupoptions, $popupwidth, $popupheight);
        echo $form;
    }
} else {
    // Virtualclass closed.
    echo $OUTPUT->heading(get_string('sessionclosed', 'virtualclass'));
}

// Finish the page.
echo $OUTPUT->footer();
