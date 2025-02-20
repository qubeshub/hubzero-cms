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
            "choicesLazyLoadEnabled": true,
            "itemComponent": "new-user-item", // Not used right now
            "placeholder": "Search for user"
          },
          {
            "name": "permission",
            "title": "Permission",
            "cellType": "dropdown",
            "defaultValue": "fill",
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
            "choices": [
                { "value": "members", "text": "Members" },
                { "value": "managers", "text": "Managers" }
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
            "choicesLazyLoadEnabled": true,
            "itemComponent": "new-group-item", // Not used right now
            "placeholder": "Search for group"
          },
          {
            "name": "role",
            "title": "Role",
            "cellType": "dropdown",
            "defaultValue": "members",
            "choices": [
                { "value": "members", "text": "Members" },
                { "value": "managers", "text": "Managers" }
            ]
          },
          {
            "name": "permission",
            "title": "Permission",
            "cellType": "dropdown",
            "defaultValue": "fill",
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

// https://surveyjs.io/form-library/examples/dropdown-menu-load-data-from-restful-service/vanillajs#
// https://surveyjs.io/form-library/examples/lazy-loading-dropdown/vanillajs#content-code
lazyLoadUsersOrGroupsFunc = (_, options) => {
    if (options.question.getType() === "dropdown" && 
        ((options.question.name === "user") || 
         (options.question.name === "group"))) {
        const option = (options.question.name === "user" ? "members" : "groups");
        const url = `https://example.com/index.php?option=com_` + option + `&no_html=1&task=autocomplete&admin=true&start=${options.skip}&limit=${options.take}&value=${options.filter}`;
        sendRequest(url, (data) => { 
            const choices = data.map((item) => {
                return { value: item.id, text: item.name + ' (' + item.id + ')' };
            });
            options.setItems(choices, choices.length); 
        });
    }
};
editorsForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
groupEditorsForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
userVisibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
groupVisibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
userAccessibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);
groupAccessibilityForm.onChoicesLazyLoad.add(lazyLoadUsersOrGroupsFunc);

// https://surveyjs.io/form-library/examples/lazy-loading-dropdown/vanillajs#content-code
getUsersOrGroupsFunc = (_, options) => {
    if (options.question.getType() === "dropdown" && 
        ((options.question.name === "user") ||
         (options.question.name === "group"))) {
        const idStr = "id=" + options.values[0];
        const option = (options.question.name === "user" ? "members" : "groups");
        const url = `https://example.com/index.php?option=com_` + option + `&no_html=1&task=autocomplete&admin=true&${idStr}`;
        sendRequest(url, (data) => { 
            const choice = [data[0].name + ' (' + data[0].id + ')'];
            options.setItems(choice);
        });
    }
};
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