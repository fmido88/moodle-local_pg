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

use core\output\pix_icon;
use local_pg\helper;
use local_pg\hook_callbacks;

/**
 * Callback implementations for Pages.
 *
 * @package    local_pg
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Serve saved files.
 * @param  ?stdClass    $course
 * @param  ?stdClass    $cm
 * @param  core\context $context
 * @param  string       $filearea
 * @param  array        $args
 * @param  bool         $forcedownload
 * @param  array        $options
 * @return void
 */
function local_pg_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload = false, array $options = []) {
    global $DB;
    $fs = get_file_storage();

    $allowedareas = [
        helper::CONTENT_FILEAREA,
        helper::CUSTOMLANG_FILEAREA,
        helper::FAQ_A_FILEAREA,
        helper::FAQ_Q_FILEAREA,
    ];

    if (!in_array($filearea, $allowedareas)) {
        send_file_not_found();
    }

    $filename = array_pop($args);
    $itemid   = array_pop($args);

    if ($file = $fs->get_file($context->id, 'local_pg', $filearea, $itemid, '/', $filename)) {
        send_stored_file($file, 0, 0, $forcedownload, $options);
    }

    send_file_not_found();
}

/**
 * Extend page navigation.
 * @param  global_navigation $nav
 * @return void
 */
function local_pg_extend_navigation(global_navigation $nav) {
    $homenav = $nav->find(SITEID, global_navigation::TYPE_COURSE);

    hook_callbacks::add_to_navigation($homenav);
}

/**
 * Add adding page icon to the navbar.
 * @return string
 */
function local_pg_render_navbar_output() {
    global $OUTPUT, $PAGE;

    if (!hook_callbacks::is_callback_allowed()) {
        return '';
    }

    if (
        !$PAGE->user_is_editing()
        || !has_capability('local/pg:add', \context_system::instance())
    ) {
        return '';
    }

    $label = get_string('addpage', 'local_pg');
    $icon  = $OUTPUT->action_icon(
        new \moodle_url('/local/pg/edit.php'),
        new pix_icon('i/file_plus', $label),
        null,
        ['class' => 'nav-link icon-no-margin'],
    );

    return html_writer::div($icon, 'nav-action');
}
