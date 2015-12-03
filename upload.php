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
//require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... virtualclass instance ID - it should be named as the first character of the module.
$delete       = optional_param('delete', 0, PARAM_INT);
$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash

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
require_once('upload_form.php');

$submiturl = new moodle_url('/mod/virtualclass/upload.php', array('id' => $cm->id));

$mform = new mod_virtualclass_upload_file($submiturl, $cm, $virtualclass, $context);

if ($mform->is_cancelled()) {
    // Do nothing.
} else if ($fromform = $mform->get_data()) {
    // Redirect($nexturl).
    $vcsession = generateRandomString();
    $name = $mform->get_new_filename('userfile');
    $filepath = "{$CFG->dataroot}/virtualclass/{$course->id}/{$virtualclass->id}/".$vcsession;
    if (!file_exists ($filepath) ) {
        mkdir($filepath, 0777, true);
    }

    // Object to save file info in db
    $vcfile = new stdClass();
    $vcfile->courseid = $course->id;
    $vcfile->vcid = $virtualclass->id;
    $vcfile->userid = $USER->id;
    $vcfile->vcsessionkey = $vcsession;
    if(empty($fromform->name)){
        $vcfile->vcsessionname = 'vc-'.$course->shortname.'-'.$virtualclass->name.$cm->id.'-'.date("Ymd").'-'.date('Hi');
    } else {
        $vcfile->vcsessionname = $fromform->name;
    }
    $vcfile->numoffiles = 1;
    $vcfile->timecreated = time();

    $content = $mform->get_file_content('userfile');
    $decode_data = json_decode($content);
    $file_length = count($decode_data);
    //print_r($decode_data);exit;

    if($file_length > 1) {
        //Break larage file in multiple files
        $filenum = 1;
        for ($i = 0; $i < $file_length; $i++) {
            if (array_key_exists('rdata', $decode_data[$i])) {
                $filename = "vc.".$filenum;
                $new_cunk = json_encode($decode_data[$i]);

                if (file_put_contents($filepath.'/'.$filename, $new_cunk) != false) {
                    if ($filenum > 1) {
                    // Update file count
                        $vcfile = $DB->get_record('virtualclass_files', 
                        array ('vcid'=> $virtualclass->id, 'vcsessionkey' => $vcsession));
                        $vcfile->numoffiles = $filenum;
                        $DB->update_record('virtualclass_files', $vcfile);
                    } else {
                        $DB->insert_record('virtualclass_files', $vcfile);
                    }
                    $filenum++;
                } else {
                    print_error('Error occurred during file upload.');
                    //echo 'There was an error creating the file name';
                }
            }
        }
    } else {
        // Upload a single file
        $fullpath = $filepath."/".$name;
        if($success = $mform->save_file('userfile', $fullpath)){
            //save file record in database
            $DB->insert_record('virtualclass_files', $vcfile);
            //redirect( new moodle_url('/mod/virtualclass/view.php', array('id' => $cm->id)));
        }
    }
    redirect( new moodle_url('/mod/virtualclass/view.php', array('id' => $cm->id)));
}
// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading($virtualclass->name);
$mform->display();
// Finish the page.
echo $OUTPUT->footer();
