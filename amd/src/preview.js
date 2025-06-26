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
 * This module is used to help preview the page and highlight syntax for css and js codes.
 *
 * @module     local_pg/preview
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Y from 'core/yui';
import Modal from 'core/modal';
import Prefetch from 'core/prefetch';
import Ajax from 'core/ajax';

// eslint-disable-next-line camelcase
import {get_string} from 'core/str';

Prefetch.prefetchStrings('local_pg', ['jssyntaxerror', 'csssyntaxerror']);

/**
 * @type {Object}
 */
let draftLangs = {};

/**
 * Open a modal to preview the page.
 */
async function preview() {
    // Create the URL object
    let url = new URL(M.cfg.wwwroot + '/local/pg/preview.php');

    // Add parameters to the URL
    url.searchParams.set("shortname", $('[name="shortname"]').val());
    url.searchParams.set("header", $('[name="header"]').val());
    url.searchParams.set("content", $('[name="content_editor[text]"]').val());
    url.searchParams.set("contentformat", $('[name="content_editor[format]"]').val());
    url.searchParams.set("layout", $('[name="layout"]').val());
    url.searchParams.set("css", $('[name="css"]').val());
    url.searchParams.set("js", $('[name="js"]').val());

    let lang = $('[name=lang]').val();
    if (lang) {
        url.searchParams.set("lang", lang);
    }

    // Create the modal with the updated URL
    let modal = await Modal.create({
        large: true,
        show: true,
        removeOnClose: true,
        body: `<iframe src="${url.href}" class="embed-responsive-item"></iframe>`,
        title: 'Preview'
    });

    modal.show();
    $('[data-region="body"]').addClass('embed-responsive').addClass('embed-responsive-16by9');
    $('[data-region="modal"]').attr('style', 'max-width: 100%; max-height: 100%; padding: 0; margin: 0;');
}

/**
 * Validate and save js code.
 * @param {CodeMirror} cm
 */
async function jsValidation(cm) {
    let errorPlaceholder = $('#js-text-error');

    var code = cm.getValue();
    try {
        // Basic syntax check
        // eslint-disable-next-line no-new-func
        Function(code);
        cm.save();
        errorPlaceholder.text('');
        errorPlaceholder.hide();
        $('input[type=submit]').removeAttr('disabled');
    } catch (e) {
        errorPlaceholder.text(await get_string('jssyntaxerror', 'local_pg', e.message));
        errorPlaceholder.show();
        $('input[type=submit]').attr('disabled', true);
    }
}

/**
 * Validate and save css code.
 * @param {CodeMirror} cm
 */
async function validateCSS(cm) {
    let errorPlaceholder = $('#css-text-error');

    let cssCode = cm.getValue().trim();
    let errors = [];

    if (typeof window.CSSLint !== 'undefined') {
        let result = window.CSSLint.verify(cssCode);
        errors = result.messages.map(msg => `Line ${msg.line}: ${msg.message}`);
    } else {
        try {
            let style = document.createElement("style");
            style.textContent = cssCode;
            document.head.appendChild(style);
            if (style.sheet.cssRules.length === 0 && cssCode !== "") {
                errors.push("Invalid CSS detected.");
            }
            document.head.removeChild(style);
        } catch (e) {
            errors.push("Invalid CSS syntax.");
        }
    }

    if (errors.length > 0) {
        errorPlaceholder.text(await get_string('csssyntaxerror', 'local_pg', errors.join("\n")));
        errorPlaceholder.show();
    } else {
        cm.save();
        errorPlaceholder.text('');
        errorPlaceholder.hide();
    }
}
/**
 * Save draft values of langs in memory.
 */
function savedraftlang() {
    let lang = $('[name=lang]').val();
    let title = $('[name=header]').val();
    let content = $('[name="content_editor[text]"]').val();

    draftLangs[lang] = {
        header: title,
        content: content
    };
    // eslint-disable-next-line no-console
    console.log(draftLangs);
}
/**
 * Fires when the language changed.
 */
async function changeLang() {
    // eslint-disable-next-line no-console
    console.log(draftLangs);
    let lang = $('[name=lang]').val();
    let disable = lang != "";

    $('[name=shortname]').attr('readonly', disable);

    if (draftLangs[lang]) {
        if (draftLangs[lang].header) {
            $('[name=header]').val(draftLangs[lang].header);
        }

        if (draftLangs[lang].content) {
            $('[name="content_editor[text]"]').val(draftLangs[lang].content);
            $('#id_content_editoreditable').html(draftLangs[lang].content);
        }

        return;
    }

    draftLangs[lang] = {
        header: undefined,
        content: undefined
    };

    let requests = Ajax.call([{
        methodname: 'local_pg_get_lang_content',
        args: {
            id: $('[name=id]').val(),
            lang: lang
        }
    }]);

    let response = await requests[0];
    if (response.header) {
        $('[name=header]').val(response.header);

        draftLangs[lang].header = response.header;
    }

    if (response.content) {
        $('[name="content_editor[text]"]').val(response.content);
        draftLangs[lang].content = response.content;
        $('#id_content_editoreditable').html(response.content);
    }
}

export const init = () => {
    // Must be sure that the dom is ready so codemirror is loaded.
    $(function() {
        setTimeout(function() {
            Y.use(['moodle-atto_html-codemirror', 'moodle-atto_html-beautify'], function(Y) {
                var CodeMirror = Y.M.atto_html.CodeMirror;
                var beautify = Y.M.atto_html.beautify;
                // Load JavaScript mode
                var jsTextarea = $('textarea[name="js"]');
                let jsCodeMirror;
                if (jsTextarea[0]) {
                    beautify.js_beautify(jsTextarea.val(), {
                        // eslint-disable-next-line camelcase
                        indent_size: 4
                    });
                    jsCodeMirror = CodeMirror.fromTextArea(jsTextarea[0], {
                        lineNumbers: true,
                        mode: 'javascript',
                        tabSize: 4,
                        lineWrapping: true,
                        indentWithTabs: false,
                        spellcheck: true,
                    });
                    jsCodeMirror.setSize('100%', '300px');
                    jsCodeMirror.on('change', jsValidation);
                }

                // Load CSS mode
                var cssTextarea = $('textarea[name="css"]');
                let cssCodeMirror;
                if (cssTextarea[0]) {
                    beautify.css_beautify(cssTextarea.val(), {
                        // eslint-disable-next-line camelcase
                        indent_size: 2
                    });
                    cssCodeMirror = CodeMirror.fromTextArea(cssTextarea[0], {
                        lineNumbers: true,
                        mode: 'css',
                        tabSize: 2,
                        lineWrapping: true,
                        indentWithTabs: false,
                        spellcheck: true,
                    });
                    cssCodeMirror.setSize('100%', '300px');
                    cssCodeMirror.on('change', validateCSS);
                }

                // I need to freeze the Codemirror textarea so that the user can't change the code if selected lang not ''.
                let langInput = $('[name=lang]');
                langInput.on('change', function() {
                    let readOnly = $(this).val() !== ''; // Read-only if lang is not empty
                    if (jsCodeMirror) {
                        jsCodeMirror.setOption("readOnly", readOnly);
                    }
                    if (cssCodeMirror) {
                        cssCodeMirror.setOption("readOnly", readOnly);
                    }
                    changeLang();
                });

            });
        }, 1000);

        $('button[name="preview"]').on("click", function() {
            preview();
        });

        $('[name="content_editor[text]"], [name="header"]').on('input, change', savedraftlang);

        savedraftlang();
    });
};
