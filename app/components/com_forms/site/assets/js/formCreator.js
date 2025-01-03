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
    // showSaveButton: true, // Use this to show save button if NOT auto-saving
    isAutoSave: true,
    autoSaveDelay: autoSaveDelay
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

const FormCreator = new SurveyCreator.SurveyCreator(creatorOptions);
// FormCreator.onSurveyPropertyValueChanged.add(notifySaving);
FormCreator.onModified.add(notifySaving);

FormCreator.saveSurveyFunc = (saveNo, callback) => { 
    window.localStorage.setItem("survey-json", FormCreator.text);
    callback(saveNo, true);
    console.log("Saving " + saveNo + "!");
    const id = $("input[name=id]").val();
    const url = '/forms/forms/' + id + '/updatejson';
    saveSurveyJson(
        url,
        FormCreator.JSON,
        saveNo,
        callback
    );
    notifySaved();
};

// creator.onUploadFile.add((_, options) => {
//     const formData = new FormData();
//     options.files.forEach(file => {
//         formData.append(file.name, file);
//     });
//     fetch("https://example.com/uploadFiles", {
//         method: "post",
//         body: formData
//     }).then(response => response.json())
//         .then(result => {
//             options.callback(
//                 "success",
//                 // A link to the uploaded file
//                 "https://example.com/files?name=" + result[options.files[0].name]
//             );
//         })
//         .catch(error => {
//             options.callback('error');
//         });
// });

document.addEventListener("DOMContentLoaded", function() {
    $('#hubForm').on('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);
        // console.log(...formData);

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                // Handle the successful response from the server
                console.log("Form submitted successfully:", response.status);
                notifySaved();
            },
            error: function(xhr, status, error) {
                // Handle any errors that occur during the AJAX request
                console.error("Error submitting form:", error);
            }
        });
    });

    $('input[name="form[responses_locked]"],'+
      'input[name="form[disabled]"],'+
      'input[name="form[archived]"],'+
      'input[name="form[opening_time]"],'+
      'input[name="form[closing_time]"]').on("change",
            debounce(function() { 
                console.log("Changed!"); 
                $('#hubForm').submit();
            }, autoSaveDelay)
    );
    
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
        console.log('Received JSON data:', data); // Access your JSON data here
    })
    .catch(error => {
        console.error("Fetch error!", error);
        // callback(saveNo, false);
    });
}

HUB.FORMS.FormCreator = FormCreator