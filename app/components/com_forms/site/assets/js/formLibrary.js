/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

Survey.setLicenseKey(
    "MzA5MjA0MTQtMmFjNi00NmYxLWFlMGEtOTQ2ODlhNDVkOWMxOzE9MjAyNS0xMi0yMiwyPTIwMjUtMTItMjIsND0yMDI1LTEyLTIy"
);

const autoSaveDelay = 500;

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

const FormLibrary = new Survey.Model();
FormLibrary.onValueChanged.add(debounce(saveSurveyData, autoSaveDelay));
FormLibrary.onCurrentPageChanged.add(debounce(saveSurveyData, autoSaveDelay));
FormLibrary.onComplete.add(debounce(saveSurveyData, autoSaveDelay));

function loadSurvey() {
    const id = $("input[name=form_id]").val();
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
        // console.log(JSON.parse(formText));
        FormLibrary.fromJSON(JSON.parse(formText));
        restoreSurveyData();
        FormLibrary.render(document.getElementById("formLibrary"));
    })
    .catch(error => {
        console.log('Error fetching form JSON');
    });
}

function restoreSurveyData() {
    const id = $("input[name=response_id]").val();
    const url = '/forms/responses/getjson?response_id=' + id;
    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'text/plain;charset=UTF-8'
        }
    })
    .then(response => {
        return response.text();
    })
    .then(responseText => {
        if (responseText) {
            const data = JSON.parse(responseText);
            FormLibrary.data = data;
            if (data.pageNo) {
                FormLibrary.currentPageNo = data.pageNo;
            }
        }
    })
    .catch(error => {
        console.log('Error fetching response JSON');
    });
}

function saveSurveyData() {
    const id = $("input[name=response_id]").val();
    const url = '/forms/responses/updatejson?response_id=' + id;

    const data = FormLibrary.data;
    data.pageNo = FormLibrary.currentPageNo;

    const formData = new FormData();
    formData.append('json', JSON.stringify(data));
    fetch(url, {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(errorText => {
                throw new Error(`HTTP Error: ${response.status}\n${errorText}`);
            });
        }
        notifySaved();
        return response.json(); // Parse the JSON from the response
    })
    // .then(data => console.log('Received JSON data:', data))
    .catch(error => console.error(error));
}

document.addEventListener("DOMContentLoaded", function() {
    loadSurvey();
});

HUB.FORMS.FormLibrary = FormLibrary
