function callRefresh() {
    fetch('/refresh', { method: 'POST' })
    .catch(function (e) {
        console.log("Call to refresh failed:", e);
    })
    .finally(function () {
        setTimeout(callRefresh, 60 * 60 * 1000);
    });
}
callRefresh()
