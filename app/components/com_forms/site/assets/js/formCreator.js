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