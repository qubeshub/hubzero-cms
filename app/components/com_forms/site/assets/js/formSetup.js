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

function sendRequest(url, onloadSuccessCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = () => {
        if (xhr.status === 200) {
            onloadSuccessCallback(JSON.parse(xhr.response));
        }
    };
    xhr.send();
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1)
}

// https://surveyjs.io/form-library/examples/dropdown-menu-load-data-from-restful-service/vanillajs#
// https://surveyjs.io/form-library/examples/lazy-loading-dropdown/vanillajs#content-code
lazyLoadUsersOrGroupsFunc = (_, options) => {
    if (options.question.getType() === "dropdown" && 
        ((options.question.name === "user") || 
         (options.question.name === "group"))) {
        const option = (options.question.name === "user" ? "members" : "groups");
        const url = window.location.origin + `/index.php?option=com_` + option + `&no_html=1&task=autocomplete&admin=true&start=${options.skip}&limit=${options.take}&value=${options.filter}&total=1`;
        sendRequest(url, (data) => {
            const choices = data["items"].map((item) => {
                return { value: item.id, text: item.name + ' (' + item.id + ')' };
            });
            options.setItems(choices, data["total"]);
        });
    }
};

// https://surveyjs.io/form-library/examples/lazy-loading-dropdown/vanillajs#content-code
getUsersOrGroupsFunc = (_, options) => {
    if (options.question.getType() === "dropdown" && 
        ((options.question.name === "user") ||
         (options.question.name === "group"))) {
        const idStr = "id=" + options.values[0];
        const option = (options.question.name === "user" ? "members" : "groups");
        const url = window.location.origin + `/index.php?option=com_` + option + `&no_html=1&task=autocomplete&admin=true&${idStr}`;
        sendRequest(url, (data) => { 
            const choice = [data[0].name + ' (' + data[0].id + ')'];
            options.setItems(choice);
        });
    }
};