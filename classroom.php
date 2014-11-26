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
//$event->add_record_snapshot($PAGE->cm->modname, $activityrecord);
$event->trigger();

// Print the page header.
$PAGE->set_url('/mod/virtualclass/view.php', array('id' => $cm->id));
$PAGE->set_popup_notification_allowed(false); // No popup notifications in virtual classroom
$PAGE->set_title(format_string($virtualclass->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->set_pagelayout('embedded');
$PAGE->requires->jquery(true);
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/css/styles.css'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/bundle/jquery/css/base/jquery-ui.css'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/virtualclass/bundle/virtualclass/css/jquery.ui.chatbox.css'));

// Mark viewed by user (if required)
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Checking moodle deugger is on or not.
$info = 0;
if($CFG->debug == 32767 && $CFG->debugdisplay == 1){
    $info = 1;
}
$whiteboard_path = $CFG->wwwroot . "/mod/virtualclass/bundle/virtualclass/";
$sid = $USER->sesskey;

$r = 's'; //default role
if(has_capability('mod/virtualclass:addinstance', $context)){
    if($USER->id == $virtualclass->moderatorid){
        $r = 't';
    }
}

// Output starts here.
echo $OUTPUT->header();
?>
<?php 

//$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/bundle/io/src/iolib.js');
//$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/wb.min.js');
////$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/chat.min.js');
//$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/index.js');


?>
<script type="text/javascript">
    <?php echo "name='".$USER->username."';"; ?>
    <?php echo "id='".$USER->id."';"; ?>
    <?php echo "sid='".$sid."';";?>
    <?php echo "fname='".$USER->firstname."';"; ?>
    <?php echo "lname='".$USER->lastname."';"; ?>

    <?php echo "wbUser.name='".$USER->firstname."';"; ?>
    <?php echo "wbUser.id='".$USER->id."';"; ?>
    <?php echo "wbUser.socketOn='$info';"; ?>
    <?php echo "wbUser.dataInfo='$info';"; ?>
    <?php echo "wbUser.room='" . $course->id . "_" . $cm->id."';"; ?>
    <?php echo "wbUser.sid='".$sid."';"; ?>
    <?php echo "wbUser.role='".$r."';"; ?>

    window.whiteboardPath =  '<?php echo $whiteboard_path; ?>';
</script>

<?php
require_once('bundle/virtualclass/build/js.debug.php');
//$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/bundle/io/src/iolib.js');
//$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/wb.min.js');
////$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/build/chat.min.js');
//$PAGE->requires->js('/mod/virtualclass/bundle/virtualclass/index.js');

if($r == 't'){
   $role  = 'teacher';
}else if($r == 's'){
   $role  = 'student'; 
}
echo html_writer::start_tag('div', array('id' => 'vAppCont','class' => "$role"));
    echo html_writer::start_tag('div', array('id' => 'vAppWhiteboard','class' => 'vmApp'));
        echo html_writer::start_tag('div', array('id' => 'vcanvas','class' => 'socketon '.$role.''));
            echo html_writer::tag('div', '', array('id' => 'containerWb'));
                echo html_writer::start_tag('div', array('id' => 'mainContainer'));
                    echo html_writer::tag('div', '', array('id' => 'packetContainer'));
                    echo html_writer::tag('div', '', array('id' => 'informationCont'));
                echo html_writer::end_tag('div');
            echo html_writer::tag('div', '', array('class' => 'clear'));
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    
if($r == 's'){
    echo html_writer::start_tag('div', array('id' => 'speakerStudent'));
        echo html_writer::tag('canvas', '', array('id' => 'speeakerStudentImage', 'width' => 40, 'height' => 40));
    echo html_writer::end_tag('div');
}
    echo html_writer::start_tag('div', array('id' => 'chatWidget'));
        echo html_writer::tag('div', '', array('id' => 'stickycontainer'));
    echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
// Finish the page.
echo $OUTPUT->footer();