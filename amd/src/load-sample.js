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
 * TODO describe module load-sample
 *
 * @module     local_pg/load-sample
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Ajax from 'core/ajax';
/**
 * @type {JQuery<HTMLElement>}
 */
let select;

/**
 * Load a sample page from file.
 */
async function loadSample() {
    const request = Ajax.call([{
        methodname: 'local_pg_load_sample',
        args: {
            name: select.val(),
        },
    }]);
    const response = await request[0];
}
export const init = function() {
    select = $('select[name=sample]');
    $('button[data-for=load-sample]').on('click', loadSample);
};