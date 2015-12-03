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
 * Displays information about all the assignment modules in the requested course
 *
 * @package   mod_virtualclass
 * @copyright 2015 Pinky Sharma
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
$filenum = required_param('prvfile' , PARAM_INT);
$id = required_param('fileBundelId' , PARAM_INT);

$file = $DB->get_record('virtualclass_files', array('id'=>$id));
//print_r($file);exit;
$filepath = $CFG->dataroot."/virtualclass/".$file->courseid."/".$file->vcid."/".$file->vcsessionkey."/vc.".$filenum;
//$filepath = $CFG->dataroot."/virtualclass/2/1/74FzDRhfpAy/user.".$filenum;

if (file_exists($filepath)) {
    $data = file_get_contents($filepath);
} else {
    $data = "VCE3";//"filenotfound";
}
//echo json_encode($arr);
echo $data;
?>
