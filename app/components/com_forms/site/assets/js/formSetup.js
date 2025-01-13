/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

Survey.setLicenseKey(
    "MzA5MjA0MTQtMmFjNi00NmYxLWFlMGEtOTQ2ODlhNDVkOWMxOzE9MjAyNS0xMi0yMiwyPTIwMjUtMTItMjIsND0yMDI1LTEyLTIy"
);

// We want file uploads to server only and not base64 in json
// https://surveyjs.answerdesk.io/ticket/details/t4012/hide-property-in-file-question
// https://surveyjs.io/survey-creator/examples/remove-properties-from-property-grid/documentation
var storeDataAsText = Survey.Serializer.getProperty("file", "storeDataAsText");
storeDataAsText.defaultValue = false;
storeDataAsText.visible = false;

// Wait for upload to finish before completing survey
var waitForUpload = Survey.Serializer.getProperty("file", "waitForUpload");
waitForUpload.defaultValue = true;
waitForUpload.visible = false;

// Title is required
Survey.Serializer.getProperty("survey", "title").isRequired = true;

function debounce(func, delay) {
    let timeoutId;

    return function(...args) {
        notifySaving();
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
        func.apply(this, args);
        }, delay);
    };
}

function notifySaving() {
    $('.save-notify').addClass('saving');
}

function notifySaved() {
    $('.save-notify').removeClass('saving')
    .addClass('saved')
    .delay(2000)
    .fadeOut(2000, function() {
        $(this).removeClass('saved').show();
    });
}