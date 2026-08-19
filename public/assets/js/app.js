/**
 * NiveraCloud - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 300);
        }, 5000);
    });

    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // Live server resource refresh
    var resourceEl = document.getElementById('server-resources');
    if (resourceEl) {
        var serverId = resourceEl.dataset.serverId;
        setInterval(function() {
            fetch('/api/servers/' + serverId + '/resources', {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(function(r) { return r.json(); }).then(function(d) {
                if (d.ok && d.resources) {
                    resourceEl.dispatchEvent(new CustomEvent('resources-updated', {detail: d}));
                }
            }).catch(function() {});
        }, 5000);
    }

    // Mobile sidebar toggle
    var sidebar = document.querySelector('.sb');
    if (sidebar) {
        var toggle = document.getElementById('sb-toggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
            });
        }
    }
});
