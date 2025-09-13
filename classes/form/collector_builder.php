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

namespace local_pg\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
/**
 * Class collector_builder
 *
 * @package    local_pg
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collector_builder extends \moodleform {
    protected function definition() {
        $elements = $this->_customdata['elements'];
        $mform = $this->_form;

        foreach ($elements as $element) {
            $args = [$element->element, $element->name, format_string($element->string)];
            if ($element->element == 'select') {
                $args[] = array_filter(array_map('trim', explode("\n", $element->options)));
            }
            $mform->addElement(...$args);
            if (!empty($element->type)) {
                $mform->setType($element->name, $element->type);
            }
            if (!empty($element->default)) {
                $mform->setDefault($element->name, $element->default);
            }
        }
        if (!empty($elements)) {
            $this->add_action_buttons(false, $this->_customdata['submitlabel'] ?? null);
        }
        if (!empty($this->_customdata['fullwidth'])) {
            $this->set_display_vertical();
        }
    }
}
