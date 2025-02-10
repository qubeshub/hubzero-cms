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

// Hide read-only mode (custom handling)
Survey.Serializer.getProperty("survey", "mode").visible = false;

// Remove built-in ability to limit one response per user (custom handling)
Survey.Serializer.getProperty("survey", "cookieName").visible = false;

// Remove built-in ability to send results on page next (custom handling)
Survey.Serializer.getProperty("survey", "sendResultOnPageNext").visible = false;

// Leave default for form save on lost focus (not while typing) [Data category]
Survey.Serializer.getProperty("survey", "textUpdateMode").visible = false;

// Title is required
Survey.Serializer.getProperty("survey", "title").isRequired = true;

// Add findability and accessibility information property category
Survey.Serializer.addProperty("survey", {
    name: "accessRules",
    type: "accessrules",
    category: "accessRules",
    showValueInLink: false
});

// Add collaboration property category
Survey.Serializer.addProperty("survey", {
    name: "collaboration",
    type: "collaboration",
    category: "collaboration",
    showValueInLink: false
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => prop.type === "accessrules",
    getJSON: () => {
      return {} // Adding properties below using API
    }
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => prop.type === "collaboration",
    getJSON: () => {
      return {} // Adding properties below using API
    }
});

// https://surveyjs.io/survey-creator/examples/customize-property-editors/vanillajs#
SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "opening-time";
    },
    getJSON: () => {
        return { type: "text", inputType: "datetime-local", maxValueExpression: "{closingTime}" };
    }
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "closing-time";
    },
    getJSON: () => {
        return { type: "text", inputType: "datetime-local", minValueExpression: "{openingTime}"};
    }
});

// https://surveyjs.io/survey-creator/examples/customize-property-editors/vanillajs#
SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "response-number";
    },
    getJSON: () => {
        return { type: "spinedit", min: "1", visibleIf: "{limitResponses}=true", 
                 validators: [{
                    "type": "regex",
                    "text": "Please enter an integer greater than 0",
                    "regex": "^[0-9]*$"
                }]};
    }
});

Survey.Serializer.addProperty("survey", {
    name: "discoverability",
    displayName: "Discoverability",
    type: "buttongroup",
    category: "accessRules",
    default: "hidden",
    choices: [
        { value: "hidden", text: "Hidden" },
        { value: "visible", text: "Visible" }
    ],
    visibleIndex: 0
});

Survey.Serializer.addProperty("survey", {
    name: "accessibility",
    displayName: "Accessibility",
    type: "buttongroup",
    category: "accessRules",
    default: "anyone",
    choices: [
        { value: "anyone", text: "Anyone" },
        { value: "member", text: "QUBES Members" }
    ],
    visibleIndex: 1
});

Survey.Serializer.addProperty("survey", {
    name: "openingTime",
    displayName: "Opening date",
    type: "opening-time",
    category: "accessRules",
    default: "",
    visibleIndex: 2
});

Survey.Serializer.addProperty("survey", {
    name: "closingTime",
    displayName: "Closing date",
    type: "closing-time",
    category: "accessRules",
    default: "",
    visibleIndex: 3
});

Survey.Serializer.addProperty("survey", {
    name: "editable",
    displayName: "Responses editable after submission",
    type: "boolean",
    category: "accessRules",
    default: "true",
    visibleIndex: 4
});

Survey.Serializer.addProperty("survey", {
    name: "limitResponses",
    displayName: "Limit number of responses",
    type: "boolean",
    category: "accessRules",
    default: "false",
    visibleIndex: 5
});

Survey.Serializer.addProperty("survey", {
    name: "limitResponseNumber",
    displayName: "Allowable responses per respondent",
    type: "response-number",
    category: "accessRules",
    default: "1",
    visibleIndex: 6
});

Survey.Serializer.addProperty("survey", {
    name: "generalPermissions",
    displayName: "General edit permissions",
    type: "buttongroup",
    category: "collaboration",
    "choices": [
        { "value": "restricted", "text": "Restricted" },
        { "value": "anyone", "text": "Anyone" },
        { "value": "member", "text": "QUBES Member" }
    ],
    default: "restricted"
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "permissions";
    },
    getJSON: () => {
        return { 
            type: "comment", 
            visibleIf: "{generalPermissions} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "permissions",
    displayName: "Collaborators",
    type: "permissions",
    category: "collaboration"
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "permission-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "myOfficialFunc({permissions})",
            defaultValue: "No collaborators",
            visibleIf: "{generalPermissions} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "permissionSummary",
    displayName: "",
    type: "permission-summary",
    category: "collaboration"
});

// https://surveyjs.io/form-library/examples/use-custom-functions-in-expressions/documentation
// https://surveyjs.io/form-library/examples/asynchronous-functions-in-expression-questions/vanillajs#content-code
function myFunc(params, returnResultCallback) {
    if (!params[0]) {
        returnResultCallback();
        return;
    }

    const idsStr = JSON.parse(params[0]).map((item) => "id[]=" + item.user).join("&");
    fetch(`https://example.com/index.php?option=com_members&no_html=1&task=autocomplete&admin=true&${idsStr}`)
        .then(response => response.json())
        .then(data => {
            const collabs = data.slice(0, 2).map((item) => item.name).join(', ') + ' and ' + Math.max(data.length-2, 0) + ' other(s)';
            returnResultCallback(collabs);
        })
        .catch(error => {
            console.error("Error:", error);
        });
}
function myOfficialFunc(params) {
    return myFunc(params, this.returnResult);
}
Survey.FunctionFactory.Instance.register("myOfficialFunc", myOfficialFunc, true);

// Help text and custom property labels
const translations = SurveyCreator.editorLocalization.getLocale("en");

// Custom property labels
translations.pe.tabs.accessRules = "Respondent Access Rules";
translations.pe.tabs.collaboration = "Collaboration";

// Help/info text
translations.pehelp.survey = {
    discoverability: 'Set to "Visible" for the form to show in search & browse results. "Hidden" forms will still be accessible via a direct link to the form, conditioned on the Accessibility setting below.',
    accessibility: 'Set to "Anyone" to make the form accessible to anyone on the internet. Set to "QUBES Members" to restrict access to logged in QUBES members only.',
    openingTime: 'Specify a date and time for the form to become available for responses.',
    closingTime: 'Specify a date and time for the form to become unavailable for responses.',
    permissions: 'Add QUBES members as form editors (edit permission) or reviewers (view permission).'
};

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