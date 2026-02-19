</div><!-- /.content-wrapper -->
</main><!-- /.main-content -->

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Sidebar Toggle
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    // Toast notification helper
    function showToast(message, type = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({ icon: type, title: message });
    }

    // Confirm dialog helper
    function confirmAction(title, text, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            customClass: { popup: 'font-prompt' }
        }).then((result) => {
            if (result.isConfirmed) callback();
        });
    }
</script>

<?php if (isset($extraJs))
    echo $extraJs; ?>

<?php if (function_exists('isAdmin') && isAdmin()): ?>
    <script>
        // ====================================================
        // Notification Bell - AJAX Polling
        // ====================================================
        var NOTIF_BASE = '<?= BASE_URL ?>';
        var notifPollTimer = null;

        function fetchNotifications() {
            fetch(NOTIF_BASE + '/actions/notifications.php?limit=15')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) return;
                    var badge = document.getElementById('notifBadge');
                    var list = document.getElementById('notifList');
                    if (!badge || !list) return;

                    // Update badge
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }

                    // Render list
                    if (data.notifications.length === 0) {
                        list.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-bell-slash d-block mb-2" style="font-size:1.5rem;"></i>ไม่มีแจ้งเตือน</div>';
                        return;
                    }

                    var html = '';
                    for (var i = 0; i < data.notifications.length; i++) {
                        var n = data.notifications[i];
                        var unreadClass = n.is_read == 0 ? 'background: rgba(102,126,234,0.08);' : '';
                        var icon = n.type === 'new_request' ? 'fa-file-alt text-primary' : 'fa-bell text-warning';
                        html += '<div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom notif-item" style="cursor:pointer;' + unreadClass + '" ';
                        html += 'onclick="clickNotif(' + n.id + ', \'' + (n.link || '') + '\')">';
                        html += '<i class="fas ' + icon + ' mt-1" style="font-size:0.9rem;"></i>';
                        html += '<div class="flex-grow-1"><div class="fw-semibold" style="font-size:0.85rem;">' + escapeHtml(n.title) + '</div>';
                        html += '<div class="text-muted" style="font-size:0.78rem;">' + escapeHtml(n.message) + '</div>';
                        html += '<div class="text-muted" style="font-size:0.7rem;"><i class="fas fa-clock me-1"></i>' + escapeHtml(n.time_ago) + '</div>';
                        html += '</div>';
                        if (n.is_read == 0) {
                            html += '<span class="badge rounded-pill bg-primary" style="font-size:0.5rem; width:8px; height:8px; padding:0; min-width:8px;"></span>';
                        }
                        html += '</div>';
                    }
                    list.innerHTML = html;
                })
                .catch(function () { });
        }

        function clickNotif(id, link) {
            // Mark as read
            var fd = new FormData();
            fd.append('action', 'mark_read');
            fd.append('id', id);
            fetch(NOTIF_BASE + '/actions/notifications.php', { method: 'POST', body: fd })
                .then(function () { fetchNotifications(); });

            // Navigate if link
            if (link) {
                window.location.href = link;
            }
        }

        function markAllRead() {
            var fd = new FormData();
            fd.append('action', 'mark_all_read');
            fetch(NOTIF_BASE + '/actions/notifications.php', { method: 'POST', body: fd })
                .then(function () { fetchNotifications(); });
        }

        function escapeHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Initial fetch + poll every 30 seconds
        fetchNotifications();
        notifPollTimer = setInterval(fetchNotifications, 30000);

        // Also fetch when bell dropdown opens
        var bellBtn = document.querySelector('#notificationBell button');
        if (bellBtn) {
            bellBtn.addEventListener('click', function () {
                fetchNotifications();
            });
        }
    </script>
    <style>
        .notif-item:hover {
            background: rgba(102, 126, 234, 0.06) !important;
        }

        #notifList::-webkit-scrollbar {
            width: 4px;
        }

        #notifList::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }
    </style>
<?php endif; ?>
</body>

</html>