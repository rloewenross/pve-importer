// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

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
