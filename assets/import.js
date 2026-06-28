import * as tus from 'tus-js-client';

var fileInput = document.getElementById("file-input");
var message = document.getElementById("message");
var fileProgress = document.getElementById("file-progress");
var uploadStatus = document.getElementById("upload-status");

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

document.getElementById("file-submit").addEventListener("click", function() {
    if (fileInput.files.length != 1) {
        message.textContent = "Must submit 1 file";
        return;
    }
    message.textContent = "";
    
    var file = fileInput.files[0];
    
    const endpoint = fileInput.getAttribute("url");
    var upload = new tus.Upload(file, {
        endpoint: endpoint,
        retryDelays: [0, 3000, 5000, 10000, 20000],
        metadata: {
          filename: file.name,
          filetype: file.type,
        },

        onError: function (error) {
          message.textContent = error;
        },
        
        onProgress: function (bytesUploaded, bytesTotal) {
            fileProgress.style.display = "block";
            fileProgress.max = bytesTotal;
            fileProgress.value = bytesUploaded;
            
            uploadStatus.textContent = formatBytes(bytesUploaded) + '/' + formatBytes(bytesTotal);
        },
    });
    
    upload.start();
});