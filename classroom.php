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
require_once('auth.php');

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

$event = \mod_virtualclass\event\course_module_viewed::create(array(
    'objectid' => $virtualclass->id,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot('virtualclass', $virtualclass);
$event->trigger();

// Print the page header.
$PAGE->set_url('/mod/virtualclass/classroom.php', array('id' => $cm->id));
$PAGE->set_popup_notification_allowed(false); // No popup notifications in virtual classroom.
$PAGE->set_title(format_string($virtualclass->name));
$PAGE->set_context($context);

$PAGE->set_pagelayout('popup');
$PAGE->requires->jquery(true);
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/css/styles.css'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/bundle/jquery/css/base/jquery-ui.css'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/css/jquery.ui.chatbox.css'));

// Chrome extension for desktop sharing.
echo '<link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/ijhofagnokdeoghaohcekchijfeffbjl">';

// Mark viewed by user (if required).
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Checking moodle deugger is unable or disable.
$info = 0;
if ($CFG->debug == 32767 && $CFG->debugdisplay == 1) {
    $info = 1;
}

$whiteboardpath = $CFG->wwwroot . "/mod/virtualclass/bundle/virtualclass/";
$sid = $USER->sesskey;

$r = 's'; // Default role.
$role  = 'student';
$dap = "false";
$classes = "audioTool deactive";
$speakermsg = get_string('enablespeaker', 'virtualclass');

$pressingimg = $whiteboardpath . "images/speakerpressing.png";

if (has_capability('mod/virtualclass:addinstance', $context)) {
    if ($USER->id == $virtualclass->moderatorid) {
        $r = 't';
        $role  = 'teacher';
        $classes = "audioTool active";
        $dap = "true";
        $speakermsg = get_string('disablespeaker', 'virtualclass');
        $pressingimg = $whiteboardpath . "images/speakerpressingactive.png";
    }
}

// Output starts here.
echo $OUTPUT->header();
// Default image if webcam disable.
if ($USER->id) {
    $userpicture = moodle_url::make_pluginfile_url(context_user::instance($USER->id)->id, 'user', 'icon', null, '/', 'f2');
    $src = $userpicture->out(false);
} else {
    $src = 'bundle/virtualclass/images/quality-support.png';
}

// Javascript variables.
?> <script type="text/javascript">    
    wbUser.imageurl =  '<?php echo $src; ?>';
    wbUser.id =  '<?php echo $USER->id; ?>';
    wbUser.socketOn =  '<?php echo $info; ?>';
    wbUser.dataInfo =  '<?php echo $info; ?>';
    wbUser.room =  '<?php echo $course->id . "_" . $cm->id; ?>';
    wbUser.sid =  '<?php echo $sid; ?>';
    wbUser.role =  '<?php echo $r; ?>';
//    wbUser.fname =  '<?php // echo $USER->firstname; ?>';
    wbUser.lname =  '<?php echo $USER->lastname; ?>';
    wbUser.name =  '<?php echo $USER->firstname; ?>';
    
    window.whiteboardPath =  '<?php echo $whiteboardpath; ?>';
    if (!!window.Worker) {
        var sworker = new Worker("<?php echo $whiteboardpath."src/screenworker.js" ?>");
    }
</script> <?php

$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/bundle/io/src/iolib.js');
$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/wb.min.js');
$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/index.js');

echo html_writer::start_tag('div', array('id' => 'vAppCont', 'class' => "$role"));
    echo html_writer::start_tag('div', array('id' => 'vAppWhiteboard', 'class' => 'vmApp'));
        echo html_writer::start_tag('div', array('id' => 'vcanvas', 'class' => 'canvasMsgBoxParent'));
            echo html_writer::tag('div', '', array('id' => 'containerWb'));
                echo html_writer::start_tag('div', array('id' => 'mainContainer'));
                    echo html_writer::tag('div', '', array('id' => 'packetContainer'));
                    echo html_writer::tag('div', '', array('id' => 'informationCont'));
                echo html_writer::end_tag('div');
            echo html_writer::tag('div', '', array('class' => 'clear'));
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

echo html_writer::start_tag('div', array('id' => 'audioWidget'));
    echo html_writer::start_tag('div', array('id' => 'mainAudioPanel'));
        echo html_writer::start_tag('div', array('id' => 'alwaysPress'));

            echo html_writer::start_tag('div', array('id' => 'speakerPressing', 'class' => $classes));
                echo html_writer::start_tag('a', array('id' => 'speakerPressingAnch', 'class' => 'tooltip', 'data-title' => get_string('pressalways', 'virtualclass')));
//                    $pressingimg = $whiteboardpath . "images/speakerpressingative.png";
                    echo get_string("pushtotalk", "virtualclass") ;
                    echo html_writer::tag('img', '', array('id' => 'speakerPressingButton', 'src' => $pressingimg));

                echo html_writer::end_tag('a');

            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

        $pressonceimg = $whiteboardpath . "images/speakerpressonce.png";


        echo html_writer::start_tag('div', array('id' => 'speakerPressOnce', 'class' => $classes, 'data-audio-playing' => $dap));
            echo html_writer::start_tag('a', array('id' => 'speakerPressonceAnch', 'class' => 'tooltip', 'data-title' => $speakermsg));
                 //echo html_writer::tag('img', '', array('id' => 'speakerPressonceImg', 'src' => $pressonceimg));
                 echo "press once";
            echo html_writer::end_tag('a');
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('id' => 'audioTest', 'class' => 'audioTool'));
        $audioimg = $whiteboardpath . "images/audiotest.png";
        echo html_writer::start_tag('a', array('id' => 'audiotestAnch', 'class' => 'tooltip', 'data-title' => get_string('audiotest', 'virtualclass')));
             echo html_writer::tag('img', '', array('id' => 'audiotestImg', 'src' => $audioimg));
        echo html_writer::end_tag('a');
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('id' => 'silenceDetect', 'class' => 'audioTool', 'data-silence-detect' => 'stop'));
        echo "sd";
    echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('id' => 'chatWidget'));
        echo html_writer::tag('div', '', array('id' => 'stickycontainer'));
    echo html_writer::end_tag('div');
echo html_writer::end_tag('div');


// Finish the page.
echo $OUTPUT->footer();


 
