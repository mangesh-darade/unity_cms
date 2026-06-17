(function () {
    'use strict';

    var POLL_MS = 30000;
    var endpoint = 'api/ga4_realtime.php';

    function esc(text) {
        var div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function fmt(n) {
        return Number(n || 0).toLocaleString();
    }

    function renderRows(container, rows, emptyText) {
        if (!container) {
            return;
        }
        if (!rows || !rows.length) {
            container.innerHTML = '<tr><td colspan="2" style="color:#94a3b8;">' + esc(emptyText) + '</td></tr>';
            return;
        }
        container.innerHTML = rows.map(function (row) {
            return '<tr><td>' + esc(row.label) + '</td><td>' + fmt(row.value) + '</td></tr>';
        }).join('');
    }

    function updateUI(data) {
        var activeEl = document.getElementById('ga4-rt-active-users');
        var eventsEl = document.getElementById('ga4-rt-event-count');
        var updatedEl = document.getElementById('ga4-rt-updated');
        var statusEl = document.getElementById('ga4-rt-status');

        if (activeEl) {
            activeEl.textContent = fmt(data.active_users);
        }
        if (eventsEl) {
            eventsEl.textContent = fmt(data.event_count);
        }
        if (updatedEl && data.fetched_at) {
            var d = new Date(data.fetched_at * 1000);
            updatedEl.textContent = 'Updated ' + d.toLocaleTimeString();
        }
        if (statusEl) {
            statusEl.classList.remove('ga4-rt-status--error');
            statusEl.textContent = 'LIVE';
        }

        renderRows(document.getElementById('ga4-rt-pages'), data.page_paths, 'No active pages right now');
        renderRows(document.getElementById('ga4-rt-sources'), data.sources, 'No traffic sources yet');
        renderRows(document.getElementById('ga4-rt-countries'), data.countries, 'No country data yet');
        renderRows(document.getElementById('ga4-rt-devices'), data.devices, 'No device data yet');
        renderRows(document.getElementById('ga4-rt-events'), data.events, 'No events in the last 30 minutes');

        var miniPages = document.getElementById('ga4-rt-mini-pages');
        if (miniPages) {
            if (!data.page_paths || !data.page_paths.length) {
                miniPages.innerHTML = '<li style="color:#94a3b8;">No visitors on site right now</li>';
            } else {
                miniPages.innerHTML = data.page_paths.slice(0, 5).map(function (row) {
                    return '<li><code>' + esc(row.label) + '</code> — ' + fmt(row.value) + ' active</li>';
                }).join('');
            }
        }
    }

    function showError(message) {
        var statusEl = document.getElementById('ga4-rt-status');
        if (statusEl) {
            statusEl.classList.add('ga4-rt-status--error');
            statusEl.textContent = 'OFFLINE';
        }
        var errEl = document.getElementById('ga4-rt-error');
        if (errEl) {
            errEl.textContent = message || 'Could not load realtime data.';
            errEl.style.display = message ? 'block' : 'none';
        }
    }

    function poll() {
        fetch(endpoint, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    showError((data && data.error) ? data.error : 'GA4 realtime unavailable.');
                    return;
                }
                var errEl = document.getElementById('ga4-rt-error');
                if (errEl) {
                    errEl.style.display = 'none';
                }
                updateUI(data);
            })
            .catch(function () {
                showError('Network error while fetching GA4 realtime data.');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.getElementById('ga4-realtime-panel')) {
            return;
        }
        poll();
        setInterval(poll, POLL_MS);
    });
})();
