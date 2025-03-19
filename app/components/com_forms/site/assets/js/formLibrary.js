/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

var HUB = HUB || {}

HUB.FORMS = HUB.FORMS || {}

const autoSaveDelay = 500;

var surveyDataRestored = false;

const FormLibrary = new Survey.Model();
FormLibrary.onValueChanged.add(debounce(saveSurveyData, autoSaveDelay));
FormLibrary.onCurrentPageChanged.add(debounce(saveSurveyData, autoSaveDelay));
FormLibrary.onComplete.add(submitSurvey);
FormLibrary.completeText = "Submit";

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
        surveyDataRestored = true;
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

function submitSurvey() {
    const id = $("input[name=response_id]").val();
    const url = '/forms/responses/submit?response_id=' + id;

    const data = FormLibrary.data;
    const formData = new FormData();
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
    .then(data => console.log('Received JSON data:', data))
    .catch(error => console.error(error));
}

function saveSurveyData() {
    if (surveyDataRestored) {
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
    } else {
        notifySaved();
    }
}

FormLibrary.onUploadFiles.add((_, options) => {
    const formData = new FormData();
    options.files.forEach((file) => {
        if (file.size>100000000) {
            alert("File too large. Please upload images less than 100Mb.");
            options.callback('error', [ 'File too large.' ]);
        }
        formData.append(file.name, file);
    });
    // console.log("Uploading files...");
    // for (const [key, value] of formData.entries()) {
    //     console.log(key, value);
    //   }
    const form_id = $("input[name=form_id]").val();
    const response_id = $("input[name=response_id]").val();
    const url = '/forms/responses/uploadfiles?form_id=' + form_id + '&response_id=' + response_id;
    fetch(url, {
        method: "POST",
        body: formData
    })
        .then((response) => response.json())
        .then((data) => {
            console.log(data);
            options.callback(
                options.files.map((file) => {
                    return {
                        file: { name: data[file.name].name, type: file.type },
                        content: data[file.name].url
                    };
                })
            );
        })
        .catch((error) => {
            console.error("Error: ", error);
            options.callback([], [ 'An error occurred during file upload.' ]);
        });
});

async function deleteFile(fileURL, options) {
    try {
        const form_id = $("input[name=form_id]").val();
        const response_id = $("input[name=response_id]").val();
        const filename = fileURL.split("/").slice(-1)[0];
        const url = '/forms/responses/deletefile?form_id=' + form_id + '&response_id=' + response_id + '&filename=' + filename;
        const response = await fetch(url, {
            method: "DELETE"
        });
        if (response.status === 200) {
            console.log(`File ${filename} was deleted successfully`);
            return true;
        } else {
            console.error(`Failed to delete file: ${filename}`);
            return false;
        }
    } catch (error) {
        console.error("Error while deleting file: ", error);
        return false;
    }
}

FormLibrary.onClearFiles.add(async (_, options) => {
    if (!options.value || options.value.length === 0) {
        return options.callback("success");
    }
    if (!options.fileName && !!options.value) {
        for (const item of options.value) {
            if (!(await deleteFile(item.content, options))) {
                options.callback("error");
            }
        }
    } else {
        const fileToRemove = options.value.find(
            (item) => item.name === options.fileName
        );
        if (fileToRemove) {
            if (!(await deleteFile(fileToRemove.content, options))) {
                options.callback("error");
            }
        } else {
            console.error(`File with name ${options.fileName} is not found`);
        }
    }
    options.callback("success");
});

document.addEventListener("DOMContentLoaded", function() {
    if ($('#submitted').val()) {
        FormLibrary.showCompleteButton = false;
    }
    loadSurvey();
});

HUB.FORMS.FormLibrary = FormLibrary
