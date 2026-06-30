import * as tus from 'tus-js-client';

var fileInput = document.getElementById("file-input");
const endpoint = fileInput.getAttribute("url");
var message = document.getElementById("message");
var fileProgress = document.getElementById("file-progress");
var uploadStatus = document.getElementById("upload-status");
const importStatusEndpoint = document.getElementById("import-status").getAttribute("url");

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
            fetch(importStatusEndpoint)
            .then(function (response) {
                return response.json();
            })
            .then(function (status) {
                if (status.status == "ok") {
                    showSuccess("File uploaded successfully! Import has started")
                } else if (status.status == "error") {
                    showError(status.message)
                }
            });
        },
    });
    
    upload.start();
});