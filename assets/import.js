// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

import * as tus from 'tus-js-client';

var fileInput = document.getElementById("file-input");
const endpoint = fileInput.getAttribute("url");
var message = document.getElementById("message");
var fileProgress = document.getElementById("file-progress");
var uploadStatus = document.getElementById("upload-status");
const importStatusEndpoint = document.getElementById("import-status").getAttribute("url");
var selectedPool = document.getElementBiId("pool-input");

// Source - https://stackoverflow.com/a/18650828
// Posted by anon, modified by community. See post 'Timeline' for change history
// Retrieved 2026-06-27, License - CC BY-SA 4.0
// Modified from XiB to XB
function formatBytes(bytes, decimals = 2) {
    if (!+bytes) return '0 Bytes'

    const k = 1000
    const dm = decimals < 0 ? 0 : decimals
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']

    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
}

function showError(s) {
    message.textContent = s;
    message.classList.add("error");
    message.style.display = "block";
}

function showSuccess(s) {
    message.textContent = s;
    message.classList.add("success");
    message.style.display = "block";
}

document.getElementById("file-submit").addEventListener("click", function() {
    if (fileInput.files.length != 1) {
        message.textContent = "Must submit 1 file";
        return;
    }
    message.textContent = "";
    
    var file = fileInput.files[0];
    
    var upload = new tus.Upload(file, {
        endpoint: endpoint,
        retryDelays: [0, 3000, 5000, 10000, 20000],
        metadata: {
          filename: file.name,
          filetype: file.type,
          pool: selectedPool.value,
        },

        onError: function (error) {
          showError(error);
        },
        
        onProgress: function (bytesUploaded, bytesTotal) {
            fileProgress.style.display = "block";
            fileProgress.max = bytesTotal;
            fileProgress.value = bytesUploaded;
            
            uploadStatus.textContent = formatBytes(bytesUploaded) + '/' + formatBytes(bytesTotal);
        },
        
        onSuccess: function () {
            showSuccess("File uploaded successfully! Import has started")
        },
    });
    
    upload.start();
});

var importStatusPlaceholder = document.getElementById("import-status-placeholder");
var importStatusList = document.getElementById("import-status-list");
const statusEndpoint = importStatusList.getAttribute("url");

function setImportStatusPlaceholderText(s) {
    var text = document.createElement("p");
    text.className = "import-status-placeholder-text";
    text.textContent = s;
    
    importStatusPlaceholder.replaceChildren(text);
}

function createImportCard(statusInfo) {
    var card = document.createElement("div");
    card.className = "import-card";
    
    var header = document.createElement("div");
    header.className = "import-card-header";
    
    var title = document.createElement("p");
    title.className = "import-card-title";
    title.textContent = statusInfo.vm_name;

    var status = document.createElement("p");
    status.className = "import-card-status";
    if (statusInfo.state == "complete") {
        status.textContent = "Done";
        status.classList.add("success");
    } else if (statusInfo.state == "error") {
        status.textContent = "Failed";
        status.classList.add("error");
    } else if (statusInfo.state == "importing") {
        status.textContent = "Importing";
    } else {
        status.textContent = "???";
    }
    
    header.append(title, status);
    
    const dateCreated = new Date(statusInfo.date_created * 1000);
    var date = document.createElement("p");
    date.className = "import-card-date";
    date.textContent = dateCreated.toDateString().concat(" ", dateCreated.toLocaleTimeString());

    var vmid = document.createElement("p");
    vmid.className = "import-card-vmid";
    vmid.textContent = "vmid: ".concat(statusInfo.vmid !== null ? statusInfo.vmid.toString() : "???");
    
    if (statusInfo.state == "error") {
        var message = document.createElement("p");
        message.className = "import-card-message";
        message.textContent = statusInfo.error_message;
    }
    
    if (statusInfo.state == "error") {
        card.append(header, date, vmid, message);
    } else {
        card.append(header, date, vmid);
    }
    
    return card;
}

var finishedStatusList = [];

function loadImportStatus() {
    fetch(statusEndpoint)
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        const completeData = data.concat(finishedStatusList);

        if (completeData.length === 0) {
            setImportStatusPlaceholderText("No imports in progress");

            importStatusList.hidden = true;
            importStatusList.replaceChildren();
            importStatusPlaceholder.hidden = false;
        } else {
            completeData.sort(function (a, b) {
                return a.date_created > b.date_created ? -1 : 1;
            });

            const fragment = document.createDocumentFragment();
            for (const status of completeData) {
                fragment.appendChild(createImportCard(status));
            }
            importStatusList.replaceChildren(fragment);

            importStatusPlaceholder.hidden = true;
            importStatusPlaceholder.replaceChildren();
            importStatusList.hidden = false;
            
            for (const status of data) {
                if (status.state == "error" || status.state == "complete") {
                    finishedStatusList.push(status);
                }
            }
        }
    })
    .catch(function (e) {
        setImportStatusPlaceholderText("Unable to load imports");

        importStatusList.hidden = true;
        importStatusList.replaceChildren();
        importStatusPlaceholder.hidden = false;
        
        console.error("Unable to load import statuses:", e);
    })
    .finally(function () {
        setTimeout(loadImportStatus, 5000);
    });
}

loadImportStatus();