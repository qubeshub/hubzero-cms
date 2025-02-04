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

Survey.Serializer.addProperty("survey", {
    name: "discoverable",
    displayName: "Discoverable",
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
    name: "openingTime",
    displayName: "Opening date",
    type: "opening-time",
    category: "accessRules",
    default: "",
    visibleIndex: 1
});

Survey.Serializer.addProperty("survey", {
    name: "closingTime",
    displayName: "Closing date",
    type: "closing-time",
    category: "accessRules",
    default: "",
    visibleIndex: 2
});

Survey.Serializer.addProperty("survey", {
    name: "editable",
    displayName: "Responses editable after submission",
    type: "boolean",
    category: "accessRules",
    default: "true",
    visibleIndex: 3
});

// Help text and custom property labels
const translations = SurveyCreator.editorLocalization.getLocale("");

// Custom property labels
translations.pe.tabs.accessRules = "Access Rules";
translations.pe.tabs.collaboration = "Collaboration";

// Help/info text
translations.pehelp.survey = {
    discoverable: 'Set to "Visible" for form to be available in search & browse results. "Hidden" forms will still be accessible via a direct link to the form.',
    openingTime: 'Specify a date and time for the form to become available for responses.',
    closingTime: 'Specify a date and time for the form to become unavailable for responses.'
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