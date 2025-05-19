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
 * TODO describe module autotag
 *
 * @module     local_autotag/autotag
 * @copyright  2025 Christian Grévisse <christian.grevisse@uni.lu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Selectors from 'local_autotag/selectors';

class AutoTag {
    constructor(resourceID) {
        this.resourceID = resourceID;
        this.registerEventListeners();
        this.hideErrorMessage();
    }

    registerEventListeners() {
        const tagger = document.querySelector(Selectors.ELEMENTS.AUTOTAGBUTTON);
        if (tagger) {
            tagger.addEventListener('click', async() => {

                this.hideErrorMessage();

                tagger.setAttribute('disabled', 'disabled');
                const oldText = tagger.innerHTML;
                tagger.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

                const request = {
                    methodname: 'local_autotag_tag_resource',
                    args: {
                        resourceid: this.resourceID,
                    }
                };

                try {
                    const responseObj = await Ajax.call([request])[0];
                    if (responseObj.error) {
                        this.showErrorMessage(responseObj.error.exception.message);
                    } else {
                        // Add received tags.
                        const tagField = document.querySelector(Selectors.ELEMENTS.TAGFIELD);

                        if (!tagField) {
                            // Tag field not found, should never happen.
                            return;
                        }

                        const enterEvent = new KeyboardEvent('keydown', {
                            code: 'Enter',
                            key: 'Enter',
                            charKode: 13,
                            keyCode: 13,
                            view: window
                        });

                        for (const tag of responseObj) {
                            tagField.value = tag;
                            tagField.dispatchEvent(enterEvent);
                        }

                        // Open "Tags" section.
                        const tagSection = document.querySelector(Selectors.ELEMENTS.TAGSECTION);
                        const tagSectionLink = document.querySelector(Selectors.ELEMENTS.TAGSECTIONLINK);

                        if (tagSection && !tagSection.classList.contains('show') && tagSectionLink) {
                            tagSectionLink.click();
                        }
                    }
                } catch (error) {
                    this.showErrorMessage(error.message);
                } finally {
                    tagger.removeAttribute('disabled');
                    tagger.innerHTML = oldText;
                }
            });
        }
    }

    showErrorMessage(message) {
        const errorField = document.querySelector(Selectors.ELEMENTS.ERRORFIELD);
        if (errorField) {
            errorField.innerHTML = message;
            errorField.style.display = 'block';
        }
    }

    hideErrorMessage() {
        const errorField = document.querySelector(Selectors.ELEMENTS.ERRORFIELD);
        if (errorField) {
            errorField.innerHTML = '';
            errorField.style.display = 'none';
        }
    }
}

export const init = (resourceID) => {
    new AutoTag(resourceID);
};
