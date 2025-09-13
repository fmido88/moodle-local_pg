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
 * Class create_collector
 *
 * @package    local_pg
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_collector extends \moodleform {
    protected function definition() {
        $preelements = $this->_customdata['elements'] ?? [];
        $elements = [];

        $mform = $this->_form;
        $maxsort = 1;
        foreach ($preelements as $e) {
            $e = (array)$e;
            if ($e['sortorder'] > $maxsort) {
                $maxsort = (int)$e['sortorder'];
            }
        }

        foreach($_POST as $key => $value) {
            if (strpos("element_", $key) === 0) {
                $parts = explode('_', $key);
                $elements[$parts[1]][$parts[2]] = $value;
            }
        }

        ksort($elements);

        foreach ($elements as $e) {
            if (!isset($e['sortorder'])) {
                $e['sortorder'] = $maxsort + 1;
            }
            if ($e['sortorder'] > $maxsort) {
                $maxsort = (int)$e['sortorder'];
            }
        }

        $elements = array_values(array_merge($preelements, $elements));
        usort($elements, fn(array $a, array $b) => $a['sortorder'] <=> $b['sortorder']);

        $i = 0;
        foreach ($elements as $element) {
            $i++;
            $group = [];
            
            $group[] = $mform->createElement(
                'text',
                "element_{$element->name}_name",
                $label = get_string('element_name', 'local_pg', $i),
                ['placeholder' => $label]
            );
            $mform->setType("element_{$element->name}_name", PARAM_ALPHANUMEXT);

            $group[] = $mform->createElement(
                'text',
                "element_{$element->name}_string",
                $label = get_string('element_string', 'local_pg', $i),
                ['placeholder' => $label]
            );
            $mform->setType("element_{$element->name}_string", PARAM_TEXT);

            if ($element->type === 'select') {
                $group[] = $mform->createElement(
                    'textarea',
                    "element_{$element->name}_options",
                    get_string('element_selectoptions', 'local_pg', $i),
                    ['placeholder' => $label]
                );
                $mform->addHelpButton("element_{$element->name}_options", 'element_selectoptions', 'local_pg', '', true, $i);
            }
    
            if ($element->type === 'text') {
                $options = [
                    PARAM_TEXT  => get_string('type_text', 'local_pg'),
                    PARAM_INT   => get_string('type_int', 'local_pg'),
                    PARAM_FLOAT => get_string('type_float', 'local_pg'),
                    PARAM_EMAIL => get_string('type_email', 'local_pg'),
                    PARAM_URL   => get_string('type_url', 'local_pg'),
                ];
                $group[] = $mform->createElement('select', "element_{$element->name}_type", get_string('element_type', 'local_pg', $i), $options);
            }

            // Text     => ['string', 'name', 'type'];
            // Select   => ['string', 'name', 'options'];
            // Checkbox => ['string', 'name'];

            $mform->addGroup($group, null, get_string('element_group', 'local_pg', $i));
        }

        $mform->addElement('text', 'form_label', get_string('form_label', 'local_pg'));
        $mform->setType('form_label', PARAM_TEXT);

        $mform->addElement('text', 'submit_label', get_string('submit_label', 'local_pg'));
        $mform->setType('submit_label', PARAM_TEXT);

        $mform->addElement('checkbox', 'fullwidth', get_string('fullwidth_labels', 'local_pg'));

        $mform->addElement('select', 'newelementtype', get_string('newelementtype', 'local_pg'));
        $mform->addElement('submit', 'addelement', get_string('addelement', 'local_pg'));
    }
}
