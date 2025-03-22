/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

const autoSaveDelay = 5000; // Set to 5000 (5000 ms -> 5 seconds) for production

const creatorOptions = {
    showLogicTab: true,
    isAutoSave: true,
    autoSaveDelay: autoSaveDelay
};

const FormCreator = new SurveyCreator.SurveyCreator(creatorOptions);
FormCreator.onModified.add(notifySaving);

FormCreator.saveSurveyFunc = (saveNo, callback) => { 
    window.localStorage.setItem("survey-json", FormCreator.text);
    callback(saveNo, true);
    // console.log("Saving " + saveNo + "!");
    const id = $("input[name=id]").val();
    const url = '/forms/forms/' + id + '/update';
    saveSurveyJson(
        url,
        FormCreator.JSON,
        saveNo,
        callback
    );
    notifySaved();
};

FormCreator.onUploadFile.add((_, options) => {
    const formData = new FormData();
    options.files.forEach(file => {
        if (file.size>100000) {
            alert("Image too large. Please upload images less than 100kb.");
            options.callback('error', [ 'Image too large.' ]);
        }
        formData.append(file.name, file);
    });
    // console.log("Uploading files...");
    // for (const [key, value] of formData.entries()) {
    //     console.log(key, value);
    //   }
    const id = $("input[name=id]").val();
    const url = '/forms/forms/' + id + '/uploadfiles';
    fetch(url, {
        method: "POST",
        body: formData
    }).then(response => response.json())
        .then((data) => {
            options.callback(
                "success",
                data[options.files[0].name]
            );
        })
        .catch(error => {
            options.callback('error');
        });
});

function saveSurveyJson(url, json, saveNo, callback) {
    const formData = new FormData();
    formData.append('json', JSON.stringify(json));
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(errorText => {
                throw new Error(`HTTP Error: ${response.status}\n${errorText}`);
            });
        }
        return response.json(); // Parse the JSON from the response
    })
    .then(data => {
        // console.log('Received JSON data:', data); // Access your JSON data here
        // Update breadcrumbs if title changed
        const id = $("input[name=id]").val();
        if ($('.breadcrumbs.pathway > a[href="/forms/forms/' + id + '/display"]').text() !== data.title) {
            $('.breadcrumbs.pathway > a[href="/forms/forms/' + id + '/display"]').text(data.title);
        }
    })
    .catch(error => {
        console.error("Fetch error!", error);
        // callback(saveNo, false);
    });
}

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

const usersFormJson = {
    "elements": [
      {
        "type": "matrixdynamic",
        "name": "access",
        "titleLocation": "hidden",
        "showHeader": false,
        "columns": [
          {
            "name": "user",
            "title": "User",
            "cellType": "dropdown",
            "isUnique": true,
            "isRequired": true,
            "choicesLazyLoadEnabled": true,
            "itemComponent": "new-user-item", // Not used right now
            "placeholder": "Search for user"
          }
        ],
        "rowCount": 0,
        "addRowText": "Add user"
      }
    ],
    "showQuestionNumbers": false,
    "showNavigationButtons": false
};

const usersAndPermissionsFormJson = {
    "elements": [
      {
        "type": "matrixdynamic",
        "name": "access",
        "titleLocation": "hidden",
        "columns": [
          {
            "name": "user",
            "title": "User",
            "cellType": "dropdown",
            "isUnique": true,
            "isRequired": true,
            "choicesLazyLoadEnabled": true,
            "itemComponent": "new-user-item", // Not used right now
            "placeholder": "Search for user"
          },
          {
            "name": "permission",
            "title": "Permission",
            "cellType": "dropdown",
            "defaultValue": "fill",
            "searchEnabled": false,
            "choices": [
                { "value": "fill", "text": "Fill" },
                { "value": "readonly", "text": "Read-only" }
            ]
          }
        ],
        "rowCount": 0,
        "addRowText": "Add user"
      }
    ],
    "showQuestionNumbers": false,
    "showNavigationButtons": false
};

const groupsAndRolesFormJson = {
    "elements": [
      {
        "type": "matrixdynamic",
        "name": "access",
        "titleLocation": "hidden",
        "columns": [
          {
            "name": "group",
            "title": "Group",
            "isRequired": true,
            "cellType": "dropdown",
            "choicesLazyLoadEnabled": true,
            "itemComponent": "new-group-item", // Not used right now
            "placeholder": "Search for group"
          },
          {
            "name": "role",
            "title": "Role",
            "cellType": "dropdown",
            "defaultValue": "members",
            "searchEnabled": false,
            "isRequired": true,
            "choicesByUrl": {
                "url": "index.php?option=com_groups&no_html=1&task=autocomplete&id[]={row.group}&roles=1",
                "valueName": "id",
                "titleName": "name"
            }
          }
        ],
        "rowCount": 0,
        "addRowText": "Add group/role"
      }
    ],
    "showQuestionNumbers": false,
    "showNavigationButtons": false
};

const groupsAndRolesAndPermissionsFormJson = {
    "elements": [
      {
        "type": "matrixdynamic",
        "name": "access",
        "titleLocation": "hidden",
        "columns": [
          {
            "name": "group",
            "title": "Group",
            "cellType": "dropdown",
            "isRequired": true,
            "choicesLazyLoadEnabled": true,
            "itemComponent": "new-group-item", // Not used right now
            "placeholder": "Search for group"
          },
          {
            "name": "role",
            "title": "Role",
            "cellType": "dropdown",
            "defaultValue": "members",
            "isRequired": true,
            "searchEnabled": false,
            "choicesByUrl": {
                "url": "index.php?option=com_groups&no_html=1&task=autocomplete&id[]={row.group}&roles=1",
                "valueName": "id",
                "titleName": "name"
            }
          },
          {
            "name": "permission",
            "title": "Permission",
            "cellType": "dropdown",
            "defaultValue": "fill",
            "searchEnabled": false,
            "choices": [
                { "value": "fill", "text": "Fill" },
                { "value": "readonly", "text": "Read-only" }
            ]
          }
        ],
        "rowCount": 0,
        "addRowText": "Add group/role"
      }
    ],
    "showQuestionNumbers": false,
    "showNavigationButtons": false
};

const editorsForm = new Survey.SurveyModel(usersFormJson);
const groupEditorsForm = new Survey.SurveyModel(groupsAndRolesFormJson);
const userVisibilityForm = new Survey.SurveyModel(usersFormJson);
const groupVisibilityForm = new Survey.SurveyModel(groupsAndRolesFormJson);
const userAccessibilityForm = new Survey.SurveyModel(usersAndPermissionsFormJson);
const groupAccessibilityForm = new Survey.SurveyModel(groupsAndRolesAndPermissionsFormJson);

// Lazy loading for users and groups
editorsForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
groupEditorsForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
userVisibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
groupVisibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
userAccessibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
groupAccessibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);

// Display initial values for users and groups
editorsForm.onGetChoiceDisplayValue.add(getUsersOrGroupsFunc);
groupEditorsForm.onGetChoiceDisplayValue.add(getUsersOrGroupsFunc);
userVisibilityForm.onGetChoiceDisplayValue.add(getUsersOrGroupsFunc);
groupVisibilityForm.onGetChoiceDisplayValue.add(getUsersOrGroupsFunc);
userAccessibilityForm.onGetChoiceDisplayValue.add(getUsersOrGroupsFunc);
groupAccessibilityForm.onGetChoiceDisplayValue.add(getUsersOrGroupsFunc);

function createTitleButton(property_name, form, title) {
    return (_, options) => {
        if (options.property.name === property_name) {
            options.titleActions.push({
                id: "set" + options.property.name.charAt(0).toUpperCase() + options.property.name.slice(1), 
                title: "Edit",
                action: () => {
                    form.setValue("access", (options.obj[options.property.name] ? JSON.parse(options.obj[options.property.name]) : {}));
                    Survey.settings.showDialog({
                        componentName: "survey",
                        data: { model: form },
                        onApply: () => {
                            const validAccessRules = form.validate();
                            if (validAccessRules) {
                                // Get values from the pop-up survey using its `data` property
                                // and update the current question (`options.obj`)
                                options.obj.setPropertyValue(options.property.name, JSON.stringify(form.data["access"]));
                                return true;
                            }
                                return false;
                        },
                        onCancel: () => {
                            console.log("Cancel");
                        },
                        cssClass: "sv-property-editor fixme",
                        title: title,
                        displayMode: "popup"
                    })
                }
            });
        }
    };
}
FormCreator.onPropertyEditorUpdateTitleActions.add(createTitleButton("editors", editorsForm, "User Editors"));
FormCreator.onPropertyEditorUpdateTitleActions.add(createTitleButton("groupEditors", groupEditorsForm, "Group Editors"));
FormCreator.onPropertyEditorUpdateTitleActions.add(createTitleButton("userVisibility", userVisibilityForm, "User Visibility"));
FormCreator.onPropertyEditorUpdateTitleActions.add(createTitleButton("groupVisibility", groupVisibilityForm, "Group Visibility"));
FormCreator.onPropertyEditorUpdateTitleActions.add(createTitleButton("userAccessibility", userAccessibilityForm, "User Accessibility"));
FormCreator.onPropertyEditorUpdateTitleActions.add(createTitleButton("groupAccessibility", groupAccessibilityForm, "Group Accessibility"));

/* 
 * ACCESS RULE PROPERTIES
 */

// Create props array of properties. These properties get used in multiple places
//  (survey, page, and question properties)
let accessRules = {
    "accessRules": {
        type: "accessrules",
        category: "accessRules",
        showValueInLink: false,
        categoryIndex: 1
    },
    "visibility": {
        displayName: "Visibility",
        type: "dropdown",
        category: "accessRules",
        default: "hidden",
        choices: [
            { value: "anyone", text: "Anyone" },
            { value: "qubes", text: "QUBES Members" },
            { value: "hidden", text: "Hidden" }
        ],
        visibleIndex: 0
    },
    "userVisibility": {
        displayName: "User visibility",
        type: "userOrGroupVisibility",
        category: "accessRules",
        visibleIndex: 1
    },
    "userVisibilitySummary": {
        displayName: "",
        type: "userVisibility-summary",
        category: "accessRules",
        visibleIndex: 2
    },
    "groupVisibility": {
        displayName: "Group visibility",
        type: "userOrGroupVisibility",
        category: "accessRules",
        visibleIndex: 3
    },
    "groupVisibilitySummary": {
        displayName: "",
        type: "groupVisibility-summary",
        category: "accessRules",
        visibleIndex: 4
    },
    "accessibility": {
        displayName: "Accessibility",
        type: "dropdown",
        category: "accessRules",
        default: "anyone",
        choices: [
            { value: "anyone", text: "Anyone" },
            { value: "qubes", text: "QUBES Members" },
            { value: "readonly", text: "Read-only" },
            { value: "restricted", text: "Restricted" }
        ],
        default: "anyone",
        visibleIndex: 5
    },
    "userAccessibility": {
        displayName: "User accessibility",
        type: "userOrGroupAccessibility",
        category: "accessRules",
        visibleIndex: 6
    },
    "userAccessibilitySummary": {
        displayName: "",
        type: "userAccessibility-summary",
        category: "accessRules",
        visibleIndex: 7
    },
    "groupAccessibility": {
        displayName: "Group accessibility",
        type: "userOrGroupAccessibility",
        category: "accessRules",
        visibleIndex: 8
    },
    "groupAccessibilitySummary": {
        displayName: "",
        type: "groupAccessibility-summary",
        category: "accessRules",
        visibleIndex: 9
    },
    "openingTime": {
        displayName: "Opening date",
        type: "opening-time",
        category: "accessRules",
        default: "",
        visibleIndex: 10
    },
    "closingTime": {
        displayName: "Closing date",
        type: "closing-time",
        category: "accessRules",
        default: "",
        visibleIndex: 11
    },
    "editable": {
        displayName: "Responses editable after submission",
        type: "boolean",
        category: "accessRules",
        default: "true",
        visibleIndex: 12
    },
    "limitResponses": {
        displayName: "Limit number of responses",
        type: "boolean",
        category: "accessRules",
        default: "false",
        visibleIndex: 13
    },
    "limitResponseNumber": {
        displayName: "Allowable responses per respondent",
        type: "response-number",
        category: "accessRules",
        default: "1",
        visibleIndex: 14
    }
};

// Property Grid Editor Collections
SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => prop.type === "accessrules",
    getJSON: () => {
      return {}
    }
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
        return { type: "spinedit", min: "1", max: "127", visibleIf: "{limitResponses}=true", 
                 validators: [{
                    "type": "regex",
                    "text": "Please enter an integer greater than 0",
                    "regex": "^[0-9]*$"
                }]};
    }
});

Object.entries(accessRules).forEach(([name, prop]) => { prop.name = name; Survey.Serializer.addProperty("survey", prop); });

/*
 * COLLABORATION PROPERTIES
 */
let collaboration = {
    "collaboration": {
        type: "collaboration",
        category: "collaboration",
        showValueInLink: false
    },
    "editing": {
        displayName: "Edit permissions",
        type: "dropdown",
        category: "collaboration",
        "choices": [
            { value: "anyone", text: "Anyone" },
            { value: "qubes", text: "QUBES Members" },
            { value: "restricted", text: "Restricted" }
        ],
        default: "restricted",
        visibleIndex: 0
    },
    "editors": {
        displayName: "User editors",
        type: "editors",
        category: "collaboration",
        visibleIndex: 1
    },
    "editorSummary": {
        displayName: "",
        type: "editor-summary",
        category: "collaboration",
        visibleIndex: 2
    },
    "groupEditors": {
        displayName: "Group editors",
        type: "editors",
        category: "collaboration",
        visibleIndex: 3
    },
    "groupEditorsSummary": {
        displayName: "",
        type: "groupEditors-summary",
        category: "collaboration",
        visibleIndex: 4
    }
}

SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => prop.type === "collaboration",
    getJSON: () => {
      return {}
    }
});
SurveyCreatorCore.PropertyGridEditorCollection.register({
    fit: (prop) => {
        return prop.type === "editors";
    },
    getJSON: () => {
        return { 
            type: "comment", 
            visibleIf: "{editing} == 'restricted'"
        };
    }
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
            visibleIf: "{editing} == 'restricted'"
        };
    }
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
            visibleIf: "{editing} == 'restricted'"
        };
    }
});

Object.entries(collaboration).forEach(([name, prop]) => { prop.name = name; Survey.Serializer.addProperty("survey", prop); });

// Now add properties to pages, questions, and panels
const pageQuestionPanelProps = ["accessRules", "visibility", "userVisibility", "userVisibilitySummary", "groupVisibility", "groupVisibilitySummary", "accessibility", "userAccessibility", "userAccessibilitySummary", "groupAccessibility", "groupAccessibilitySummary"];
PQPAccessRules = JSON.parse(JSON.stringify(accessRules)); // Deep copy
PQPAccessRules["visibility"].choices.unshift({value: "inherit", text: "Inherit"});
PQPAccessRules["visibility"].default = "inherit";
PQPAccessRules["accessibility"].choices.unshift({value: "inherit", text: "Inherit"});
PQPAccessRules["accessibility"].default = "inherit";
pageQuestionPanelProps.forEach((prop) => { 
    Survey.Serializer.addProperty("page", PQPAccessRules[prop]); 
    Survey.Serializer.addProperty("question", PQPAccessRules[prop]);
    Survey.Serializer.addProperty("panel", PQPAccessRules[prop]);
});
Survey.Serializer.getProperty("page", "visible").visible = false;
Survey.Serializer.getProperty("question", "visible").visible = false;
Survey.Serializer.getProperty("panel", "visible").visible = false;
Survey.Serializer.getProperty("page", "readOnly").visible = false;
Survey.Serializer.getProperty("question", "readOnly").visible = false;
Survey.Serializer.getProperty("panel", "readOnly").visible = false;

// https://surveyjs.io/form-library/examples/use-custom-functions-in-expressions/documentation
// https://surveyjs.io/form-library/examples/asynchronous-functions-in-expression-questions/vanillajs#content-code
function lazyLoadUsersForSummary(params, returnResultCallback) {
    if (!params[0]) {
        returnResultCallback();
        return;
    }

    const ids = JSON.parse(params[0]);
    const num_users = ids.length;
    let users = ids.slice(0, 2); // Only showing first two
    const idsStr = users.map((item) => "id[]=" + item.user).join("&");
    fetch(`index.php?option=com_members&no_html=1&task=autocomplete&admin=true&${idsStr}`)
        .then(response => response.json())
        .then(data => {
            users = data.map((item, idx) => item.name + (users[idx].hasOwnProperty('permission') ? ' (' + users[idx].permission + ')' : '')).join(', ') + ' and ' + Math.max(num_users-2, 0) + ' other(s)';
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
    const num_roles = ids.length;
    let roles = ids.slice(0, 2); // Only showing first two
    const idsStr = roles.map((item) => "id[]=" + item.group).join("&");
    const ridsStr = roles.filter((item) => !isNaN(item.role) && item.role.trim() !== "").map((item) => "rid[]=" + item.role).join("&");
    fetch(`index.php?option=com_groups&no_html=1&task=autocomplete&admin=true&${idsStr}&${ridsStr}&roles=2`)
        .then(response => response.json())
        .then(data => {
            const groups = roles.map(item => capitalize(data.roles[item.role]) + ' of ' + data.groups[item.group] + (item.hasOwnProperty('permission') ? ' (' + item.permission + ')' : '')).join(', ') + ' and ' + Math.max(num_roles-2, 0) + ' other(s)';
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

// Custom item view for lazy loaded items - not working
// https://surveyjs.io/form-library/examples/dropdown-box-with-custom-items/vanillajs#content-code
// Send ticket in SurveyJS to ask if can store additional fields in choices (e.g. picture) so don't have to make additional api calls to server

// window.React = { createElement: SurveyUI.createElement, Component: SurveyUI.Component };

// class ItemTemplateComponent extends SurveyUI.Component {
//     render() {
//         const item = this.props.item;
//         return (
//             React.createElement("div", {
//                 className: "my-list-item",
//                 style: {
//                   display: "flex"
//                 }
//               }, React.createElement("span", {
//                 className: "list-item_text"
//               }, item.id))
//         );
//     }
// }

// SurveyUI.ReactElementFactory.Instance.registerElement(
//   "new-user-item",
//   (props) => {
//     return React.createElement(ItemTemplateComponent, props);
//   }
// );

document.addEventListener("DOMContentLoaded", function() {
    const id = $("input[name=id]").val();
    const url = '/forms/forms/' + id + '/getjson';
    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'text/plain;charset=UTF-8'
        }
    })
    .then(response => {
        return response.text();
    })
    .then(formText => { 
        // console.log(JSON.parse(text));
        FormCreator.text = formText;
        FormCreator.render(document.getElementById("formCreator"));
    })
    .catch(error => {
        console.log('Error fetching form JSON');
    });
});

HUB.FORMS.FormCreator = FormCreator