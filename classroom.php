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
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$event = \mod_virtualclass\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
// $event->add_record_snapshot($PAGE->cm->modname, $activityrecord);.
$event->trigger();

if (empty($licen)) {
    echo get_string('notsavekey', 'virtualclass')." <a href='".$CFG->wwwroot.'/local/getkey/index.php'."'>Click Here</a>";
} else {
        // Print the page header.
    $PAGE->set_url('/mod/virtualclass/view.php', array('id' => $cm->id));
    $PAGE->set_popup_notification_allowed(false); // No popup notifications in virtual classroom.
    $PAGE->set_title(format_string($virtualclass->name));
    // $PAGE->set_heading(format_string($course->fullname));.
    $PAGE->set_context($context);

    // $PAGE->set_pagelayout('embedded');.
    $PAGE->set_pagelayout('popup');
    $PAGE->requires->jquery(true);
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    $PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/css/styles.css'));
    $PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/bundle/jquery/css/base/jquery-ui.css'));
    $PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/css/jquery.ui.chatbox.css'));

    ?>
    <link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/ijhofagnokdeoghaohcekchijfeffbjl">
    <?php

    // Mark viewed by user (if required).
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

    // Checking moodle deugger is on or not.
    $info = 0;
    // For now we disabling the packet container.
    /* if($CFG->debug == 32767 && $CFG->debugdisplay == 1){
            $info = 1;
        }
     */
    $whiteboardpath = $CFG->wwwroot . "/mod/virtualclass/bundle/virtualclass/";
    $sid = $USER->sesskey;

    $r = 's'; // Default role.
    if (has_capability('mod/virtualclass:addinstance', $context)) {
        if ($USER->id == $virtualclass->moderatorid) {
            $r = 't';
        }
    }

    // Output starts here.
    echo $OUTPUT->header();
    ?>
    <script type="text/javascript">
    <?php 
    if ($USER->id) {
        $userpicture = moodle_url::make_pluginfile_url(context_user::instance($USER->id)->id, 'user', 'icon', null, '/', 'f2');
        $src = $userpicture->out(false);
    } else {
        $src = 'bundle/virtualclass/images/quality-support.png';
    }
        echo "wbUser.imageurl='".$src."';";
        echo "wbUser.name='".$USER->firstname."';";
        echo "wbUser.id='".$USER->id."';";
        echo "wbUser.socketOn='$info';";
        echo "wbUser.dataInfo='$info';";
        echo "wbUser.room='" . $course->id . "_" . $cm->id."';";
        echo "wbUser.sid='".$sid."';";
        echo "wbUser.role='".$r."';";
        echo "wbUser.fname='".$USER->firstname."';";
        echo "wbUser.lname='".$USER->lastname."';";
    ?>
        window.whiteboardPath =  '<?php echo $whiteboardpath; ?>';
        if (!!window.Worker) {
            var sworker = new Worker("<?php echo $whiteboardpath."src/screenworker.js" ?>");
        }
    </script>

    <?php
    // require_once('bundle/virtualclass/build/js.debug.php');.

    $PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/bundle/io/src/iolib.js');
    $PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/wb.min.js');

    // $PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/chat.min.js');.
    $PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/index.js');

    if ($r == 't') {
        $role  = 'teacher';
    } else if ($r == 's') {
        $role  = 'student';
    }
    echo html_writer::start_tag('div', array('id' => 'vAppCont', 'class' => "$role"));
        echo html_writer::start_tag('div', array('id' => 'vAppWhiteboard', 'class' => 'vmApp'));
            echo html_writer::start_tag('div', array('id' => 'vcanvas', 'class' => 'socketon '.$role.''));
                echo html_writer::tag('div', '', array('id' => 'containerWb'));
                    echo html_writer::start_tag('div', array('id' => 'mainContainer'));
                        echo html_writer::tag('div', '', array('id' => 'packetContainer'));
                        echo html_writer::tag('div', '', array('id' => 'informationCont'));
                    echo html_writer::end_tag('div');
                echo html_writer::tag('div', '', array('class' => 'clear'));
            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('id' => 'audioWidget'));

    if ($r == 's') {
        $dap = "false";
        $classes = "audioTool deactive";
        echo html_writer::start_tag('div', array('id' => 'speakerStudent'));

            echo html_writer::start_tag('div', array('id' => 'speakerPressing', 'class' => 'audioTool deactive' ));
                // echo html_writer::tag('canvas', '', array('id' => 'speakerPressingImg', 'width' => 40, 'height' => 40));.
                echo html_writer::start_tag('a', array('id' => 'speakerPressingAnch', 'class' => 'tooltip', 'data-title' => get_string('pressalways', 'virtualclass')));
        // $iconurl = new moodle_url('http://web.icq.com/whitepages/online', array('icq' => $user->icq, 'img' => '5'));.
        // $statusicon = html_writer::tag('img', '', array('src' => $iconurl, 'class' => 'icon icon-post', 'alt' => get_string('status')));.
                    $pressingimg = $whiteboardpath . "images/speakerpressing.png";
                    echo html_writer::tag('img', '', array('id' => 'speakerPressingImg', 'src' => $pressingimg));

                echo html_writer::end_tag('a');

            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    } else {
          $classes = "audioTool active";
          $dap = "true";
    }
        $pressonceimg = $whiteboardpath . "images/speakerpressonce.png";
        echo html_writer::start_tag('div', array('id' => 'speakerPressOnce', 'class' => $classes, 'data-audio-playing' => $dap));
            echo html_writer::start_tag('a', array('id' => 'speakerPressonceAnch', 'class' => 'tooltip', 'data-title' => get_string('pressonce', 'virtualclass') ));
                 echo html_writer::tag('img', '', array('id' => 'speakerPressonceImg', 'src' => $pressonceimg));
            echo html_writer::end_tag('a');
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div', array('id' => 'audioTest', 'class' => 'audioTool'));
            $audioimg = $whiteboardpath . "images/audiotest.png";
            echo html_writer::start_tag('a', array('id' => 'audiotestAnch', 'class' => 'tooltip', 'data-title' => get_string('audiotest', 'virtualclass')));
                 echo html_writer::tag('img', '', array('id' => 'audiotestImg', 'src' => $audioimg));
            echo html_writer::end_tag('a');
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div', array('id' => 'silenceDetect', 'class' => 'audioTool'));
            $silencedetect = $whiteboardpath . "images/silencedetectdisable.png";
            echo html_writer::start_tag('a', array('id' => 'silenceDetectAnch', 'class' => 'tooltip sdDisable', 'data-title' => get_string('silencedetect', 'virtualclass')));
                 echo html_writer::tag('img', '', array('id' => 'silencedetectImg', 'src' => $silencedetect));
            echo html_writer::end_tag('a');
        echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');

        echo html_writer::start_tag('div', array('id' => 'chatWidget'));
            echo html_writer::tag('div', '', array('id' => 'stickycontainer'));
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    // Finish the page.
    echo $OUTPUT->footer();
}

 