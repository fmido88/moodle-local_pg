<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_pg
 * @category    upgrade
 * @copyright   2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_pg_install() {
    global $DB;
    $dbman = $DB->get_manager();
    $tables = [
        'local_page_pages' => 'local_pg_pages',
        'local_page_faq'   => 'local_pg_faq',
        'local_page_langs' => 'local_pg_langs',
    ];
    // Migrate from the old plugin name to the new one.
    foreach ($tables as $old => $new) {
        if ($dbman->table_exists($old) && $dbman->table_exists($new)) {
            // Check if the two tables having the same structures.
            $oldfields = array_keys($DB->get_columns($old));
            $newfields = array_keys($DB->get_columns($new));

            if (count($oldfields) !== count($newfields)) {
                continue;
            }

            foreach ($oldfields as $oldfield) {
                if (!in_array($oldfield, $newfields)) {
                    continue 2;
                }
            }

            $records = $DB->get_records($old);
            $DB->insert_records($new, $records);
            $DB->delete_records($old);
        }
    }
    return true;
}
