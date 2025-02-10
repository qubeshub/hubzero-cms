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

// Step 3: Define a survey JSON schema for the modal dialog
const popupJson = {
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
            "defaultValue": "edit",
            "choices": [
                { "value": "edit", "text": "Edit" },
                { "value": "view", "text": "View" }
            ]
          }
        ],
        "rowCount": 0,
        "addRowText": "Add collaborator"
      }
    ],
    "showQuestionNumbers": false,
    "showNavigationButtons": false
};

const popupSurvey = new Survey.SurveyModel(popupJson);

// https://surveyjs.io/form-library/examples/dropdown-menu-load-data-from-restful-service/vanillajs#
// https://surveyjs.io/form-library/examples/lazy-loading-dropdown/vanillajs#content-code
popupSurvey.onChoicesLazyLoad.add((_, options) => {
    if (options.question.getType() === "dropdown" && options.question.name === "user") {
        const url = `https://example.com/index.php?option=com_members&no_html=1&task=autocomplete&admin=true&start=${options.skip}&limit=${options.take}&value=${options.filter}`;
        sendRequest(url, (data) => { 
            const choices = data.map((item) => {
                return { value: item.id, text: item.name + ' (' + item.id + ')' };
            });
            options.setItems(choices, choices.length); 
        });
    }
});

// https://surveyjs.io/form-library/examples/lazy-loading-dropdown/vanillajs#content-code
popupSurvey.onGetChoiceDisplayValue.add((_, options) => {
    if (options.question.getType() === "dropdown" && options.question.name === "user") {
        const idStr = "id=" + options.values[0];
        const url = `https://example.com/index.php?option=com_members&no_html=1&task=autocomplete&admin=true&${idStr}`;
        sendRequest(url, (data) => { 
            const choice = [data[0].name + ' (' + data[0].id + ')'];
            options.setItems(choice);
        });
    }
});
    
// Step 4: Add a button that opens the dialog to the editor title
FormCreator.onPropertyEditorUpdateTitleActions.add((_, options) => {
    if (options.property.name === "permissions") {
        options.titleActions.push({
            id: "setPermissions",
            title: "Edit",
            // Step 5: Open the dialog on a button click
            action: () => {
                popupSurvey.setValue("access", (options.obj.permissions ? JSON.parse(options.obj.permissions) : {}));
                Survey.settings.showDialog({
                    componentName: "survey",
                    data: { model: popupSurvey },
                    onApply: () => {
                        const validAccessRules = popupSurvey.validate();
                        if (validAccessRules) {
                            // Get values from the pop-up survey using its `data` property
                            // and update the current question (`options.obj`)
                            options.obj.setPropertyValue("permissions", JSON.stringify(popupSurvey.data["access"]));
                            return true;
                        }
                            return false;
                    },
                    onCancel: () => {
                        console.log("Cancel");
                    },
                    cssClass: "sv-property-editor fixme",
                    title: "Edit Collaborators",
                    displayMode: "popup"
                })
            }
        });
    }
});

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