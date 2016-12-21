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
 * Created by PhpStorm.
 * User: Nayan
 * Date: 4/10/16
 * Time: 12:50 PM
 */

require_once("$CFG->libdir/filestorage/file_storage.php");
require_once($CFG->dirroot.'/course/modlib.php');
function tool_uploadcontent_my_mktempdir($dir, $prefix='') {
    if (substr($dir, -1) != '/') {
        $dir .= '/';
    }
    do {
        $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (file_exists($path));
    check_dir_exists($path);
    return $path;
}

function tool_uploadcontent_print_tabs($selected, $inactive, $activated) {
    global $CFG;
    $arsurl = $CFG->wwwroot.'/admin/tool/uploadcontent/index.php';
    $arcurl = $CFG->wwwroot.'/admin/tool/uploadcontent/content.php';

    $toprow[] = new tabobject('ars', $arsurl, get_string('zipcontenttab', 'tool_uploadcontent'));
    $toprow[] = new tabobject('arc', $arcurl, get_string('csvcontenttab', 'tool_uploadcontent'));

    $tabs[] = $toprow;
    $bottomrow[] = new tabobject('arcs', '', '');
    $tabs[] = $bottomrow;

    print_tabs($tabs, $selected, $inactive, $activated);
}
function tool_uploadcontent_check_course_capability($courseid) {
    global $CFG, $USER;
    $coursecontext = context_course::instance($courseid);
    if (has_capability('moodle/course:manageactivities', $coursecontext, $USER->id)) {
    return true;
    } 
	else {
	return false;
    }
}

function tool_uploadcontent_validate_category($categoryid) {
    global $DB;
    if ($DB->record_exists('course_categories', array('id' => $categoryid))) {
	return true;
    }
    return false;
}

function tool_uploadcontent_validate_course($courseid) {
    global $DB;
    if ($DB->record_exists('course', array('id' => $courseid))) {
        return true;
    }
    return false;
}

function tool_uploadcontent_validate_url($url, $filename) {
    global $CFG;
    $url = $CFG->dataroot."/filedir".$url.$filename;
    if (file_exists($url)) {
        return true;
    }
    return false;
}

function tool_uploadcontent_add_resourse_to_course($fileinfo) {
    global $DB, $CFG;
    try {
        $modinfo = new stdClass();
        $course = new stdClass();
        $course->id = $fileinfo[1];
        $modinfo->name = $fileinfo[3];
        $modinfo->help = "Help";
        $modinfo->language = 0;
        $modinfo->externalurl = '';
        $modinfo->action = "add";
        $modinfo->files = 0;
        $modinfo->display = 0;
        $modinfo->visible = 1;
        $modinfo->scorm_package_file = '';
        $modinfo->resource_module = 17;
        $modinfo->url_module = 20;
        $modinfo->course = $fileinfo[1];;
        $modinfo->coursemodule = '';
        $modinfo->section = $fileinfo[2];
        $modinfo->module = 17;
        $modinfo->modulename = get_string('resource' , 'tool_uploadcontent');
        $modinfo->instance = '';
        $modinfo->add = get_string('resource' , 'tool_uploadcontent');
        $modinfo->update = 0;
        $modinfo->return = 0;
        $modinfo->sr = 0;
        $modinfo->submitbutton2 = get_string('save' , 'tool_uploadcontent');
        $modinfo->revision = 1;
        $modinfo->groupingid = 0;
        $modinfo->completion = 0;
        $modinfo->completionview = 0;
        $modinfo->completionexpected = 0;
        $modinfo->completiongradeitemnumber = '';
        $modinfo->conditiongradegroup = array();
        $modinfo->conditionfieldgroup = array();
        $modinfo->groupmode = 0;
        $modinfo->intro = "";
        $modinfo->introformat = 1;
        $modinfo->timemodified = time();
        $modinfo->displayoptions = 'a:1:{s:10:"printintro";i:0;}';
        $moduleinfo = add_moduleinfo($modinfo, get_course($fileinfo[1]), null);
        $filerecord = new stdClass();
        $filerecord->filepath = $fileinfo[4];
        $filerecord->contextid = context_module::instance($moduleinfo->coursemodule)->id;
        $filestorage = get_file_storage();
        $fileinfos = array(
          'contextid' => $filerecord->contextid,
          'component' => 'mod_resource',
          'filearea' => get_string('content' , 'tool_uploadcontent'),
          'itemid' => 0,
          'filepath' => '/',
          'filename' => $fileinfo[3]
        );
         $filestorage->create_file_from_pathname($fileinfos, $CFG->dataroot.'/filedir'.$fileinfo[4].$fileinfo[3]);
    } catch (Exception $e) {
        print($e);
        die();
    }
}