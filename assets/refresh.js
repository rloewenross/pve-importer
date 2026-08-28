// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

function callRefresh() {
    fetch('/refresh', { method: 'POST', redirect: 'manual' })
    .then(function (response) {
        if (response.status === 302) {
            const location = response.headers.get('Location');

            if (location) {
                window.location.assign(location);
            }
        }
    })
    .catch(function (e) {
        console.log("Call to refresh failed:", e);
    })
    .finally(function () {
        setTimeout(callRefresh, 60 * 60 * 1000);
    });
}
callRefresh();
