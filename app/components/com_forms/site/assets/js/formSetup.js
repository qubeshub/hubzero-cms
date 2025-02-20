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

// Add editors property category
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
    name: "visibility",
    displayName: "General visibility",
    type: "dropdown",
    category: "accessRules",
    default: "hidden",
    choices: [
        { value: "anyone", text: "Anyone" },
        { value: "member", text: "QUBES Members" },
        { value: "hidden", text: "Hidden" }
    ],
    visibleIndex: 0
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "userOrGroupVisibility";
    },
    getJSON: () => {
        return { 
            type: "comment", 
            visibleIf: "{visibility} == 'hidden'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "userVisibility",
    displayName: "User visibility",
    type: "userOrGroupVisibility",
    category: "accessRules",
    visibleIndex: 1
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "userVisibility-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "lazyLoadUsersForSummaryFunc({userVisibility})",
            defaultValue: "No users",
            visibleIf: "{visibility} == 'hidden'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "userVisibilitySummary",
    displayName: "",
    type: "userVisibility-summary",
    category: "accessRules",
    visibleIndex: 2
});

Survey.Serializer.addProperty("survey", {
    name: "groupVisibility",
    displayName: "Group visibility",
    type: "userOrGroupVisibility",
    category: "accessRules",
    visibleIndex: 3
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "groupVisibility-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "lazyLoadGroupsForSummaryFunc({groupVisibility})",
            defaultValue: "No groups",
            visibleIf: "{visibility} == 'hidden'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "groupVisibilitySummary",
    displayName: "",
    type: "groupVisibility-summary",
    category: "accessRules",
    visibleIndex: 4
});

Survey.Serializer.addProperty("survey", {
    name: "accessibility",
    displayName: "General accessibility",
    type: "dropdown",
    category: "accessRules",
    default: "anyone",
    choices: [
        { value: "anyone", text: "Anyone" },
        { value: "member", text: "QUBES Members" },
        { value: "readonly", text: "Read-only" },
        { value: "restricted", text: "Restricted" }
    ],
    default: "anyone",
    visibleIndex: 5
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "userOrGroupAccessibility";
    },
    getJSON: () => {
        return { 
            type: "comment", 
            visibleIf: "{accessibility} == 'readonly' || {accessibility} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "userAccessibility",
    displayName: "User accessibility",
    type: "userOrGroupAccessibility",
    category: "accessRules",
    visibleIndex: 6
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "userAccessibility-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "lazyLoadUsersForSummaryFunc({userAccessibility})",
            defaultValue: "No users",
            visibleIf: "{accessibility} == 'readonly' || {accessibility} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "userAccessibilitySummary",
    displayName: "",
    type: "userAccessibility-summary",
    category: "accessRules",
    visibleIndex: 7
});

Survey.Serializer.addProperty("survey", {
    name: "groupAccessibility",
    displayName: "Group accessibility",
    type: "userOrGroupAccessibility",
    category: "accessRules",
    visibleIndex: 8
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "groupAccessibility-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "lazyLoadGroupsForSummaryFunc({groupAccessibility})",
            defaultValue: "No groups",
            visibleIf: "{accessibility} == 'readonly' || {accessibility} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "groupAccessibilitySummary",
    displayName: "",
    type: "groupAccessibility-summary",
    category: "accessRules",
    visibleIndex: 9
});

Survey.Serializer.addProperty("survey", {
    name: "openingTime",
    displayName: "Opening date",
    type: "opening-time",
    category: "accessRules",
    default: "",
    visibleIndex: 10
});

Survey.Serializer.addProperty("survey", {
    name: "closingTime",
    displayName: "Closing date",
    type: "closing-time",
    category: "accessRules",
    default: "",
    visibleIndex: 11
});

Survey.Serializer.addProperty("survey", {
    name: "editable",
    displayName: "Responses editable after submission",
    type: "boolean",
    category: "accessRules",
    default: "true",
    visibleIndex: 12
});

Survey.Serializer.addProperty("survey", {
    name: "limitResponses",
    displayName: "Limit number of responses",
    type: "boolean",
    category: "accessRules",
    default: "false",
    visibleIndex: 13
});

Survey.Serializer.addProperty("survey", {
    name: "limitResponseNumber",
    displayName: "Allowable responses per respondent",
    type: "response-number",
    category: "accessRules",
    default: "1",
    visibleIndex: 14
});

/*
 * COLLABORATION PROPERTIES
 */
Survey.Serializer.addProperty("survey", {
    name: "generalEditPermissions",
    displayName: "General edit permissions",
    type: "dropdown",
    category: "collaboration",
    "choices": [
        { value: "anyone", text: "Anyone" },
        { value: "member", text: "QUBES Members" },
        { value: "restricted", text: "Restricted" }
    ],
    default: "restricted",
    visibleIndex: 0
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "editors";
    },
    getJSON: () => {
        return { 
            type: "comment", 
            visibleIf: "{generalEditPermissions} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "editors",
    displayName: "User editors",
    type: "editors",
    category: "collaboration",
    visibleIndex: 1
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "editor-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "lazyLoadUsersForSummaryFunc({editors})",
            defaultValue: "No users",
            visibleIf: "{generalEditPermissions} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "editorSummary",
    displayName: "",
    type: "editor-summary",
    category: "collaboration",
    visibleIndex: 2
});

Survey.Serializer.addProperty("survey", {
    name: "groupEditors",
    displayName: "Group editors",
    type: "editors",
    category: "collaboration",
    visibleIndex: 3
});

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "groupEditors-summary";
    },
    getJSON: () => {
        return { 
            type: "expression", 
            expression: "lazyLoadGroupsForSummaryFunc({groupEditors})",
            defaultValue: "No groups",
            visibleIf: "{generalEditPermissions} == 'restricted'"
        };
    }
});
Survey.Serializer.addProperty("survey", {
    name: "groupEditorsSummary",
    displayName: "",
    type: "groupEditors-summary",
    category: "collaboration",
    visibleIndex: 4
});

// https://surveyjs.io/form-library/examples/use-custom-functions-in-expressions/documentation
// https://surveyjs.io/form-library/examples/asynchronous-functions-in-expression-questions/vanillajs#content-code
function lazyLoadUsersForSummary(params, returnResultCallback) {
    if (!params[0]) {
        returnResultCallback();
        return;
    }

    const idsStr = JSON.parse(params[0]).map((item) => "id[]=" + item.user).join("&");
    fetch(`https://example.com/index.php?option=com_members&no_html=1&task=autocomplete&admin=true&${idsStr}`)
        .then(response => response.json())
        .then(data => {
            const users = data.slice(0, 2).map((item) => item.name).join(', ') + ' and ' + Math.max(data.length-2, 0) + ' other(s)';
            returnResultCallback(users);
        })
        .catch(error => {
            console.error("Error:", error);
        });
}
function lazyLoadUsersForSummaryFunc(params) {
    return lazyLoadUsersForSummary(params, this.returnResult);
}
Survey.FunctionFactory.Instance.register("lazyLoadUsersForSummaryFunc", lazyLoadUsersForSummaryFunc, true);

function lazyLoadGroupsForSummary(params, returnResultCallback) {
    if (!params[0]) {
        returnResultCallback();
        return;
    }

    const ids = JSON.parse(params[0]);
    console.log(ids);
    const idsStr = ids.map((item) => "id[]=" + item.group).join("&");
    fetch(`https://example.com/index.php?option=com_groups&no_html=1&task=autocomplete&admin=true&${idsStr}&order=`)
        .then(response => response.json())
        .then(data => {
            // Do an additional fetch to get role names
            const groups = data.slice(0, 2).map((item, idx) => capitalize(ids[idx].role) + ' of ' + item.name + (ids[idx].hasOwnProperty('permission') ? ' (' + ids[idx].permission + ')' : '')).join(', ') + ' and ' + Math.max(data.length-2, 0) + ' other(s)';
            returnResultCallback(groups);
        })
        .catch(error => {
            console.error("Error:", error);
        });
}
function lazyLoadGroupsForSummaryFunc(params) {
    return lazyLoadGroupsForSummary(params, this.returnResult);
}
Survey.FunctionFactory.Instance.register("lazyLoadGroupsForSummaryFunc", lazyLoadGroupsForSummaryFunc, true);

// Help text and custom property labels
const translations = SurveyCreator.editorLocalization.getLocale("en");

// Custom property labels
translations.pe.tabs.accessRules = "Access Rules";
translations.pe.tabs.collaboration = "Collaboration";

// Help/info text
translations.pehelp.survey = {
    visibility: 'Set to "Visible" for the form to show in search & browse results. "Hidden" forms will still be accessible via a direct link to the form, conditioned on the Accessibility setting below.',
    accessibility: 'Set to "Anyone" to make the form accessible to anyone on the internet. Set to "QUBES Members" to restrict access to logged in QUBES members only.',
    openingTime: 'Specify a date and time for the form to become available for responses.',
    closingTime: 'Specify a date and time for the form to become unavailable for responses.',
    editors: 'Add QUBES members as form editors.'
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

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1)
}