<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const topMenuToggle = document.getElementById('topMenuToggle');
        const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');
        const topMenu = document.querySelector('.top-menu-scroll');

        if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 768) {
            document.body.classList.add('sidebar-collapsed');
        }

        if (localStorage.getItem('topMenuIconsOnly') === 'true' && window.innerWidth > 768) {
            document.body.classList.add('top-menu-icons-only');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    document.body.classList.toggle('mobile-sidebar-open');
                    return;
                }

                document.body.classList.toggle('sidebar-collapsed');

                localStorage.setItem(
                    'sidebarCollapsed',
                    document.body.classList.contains('sidebar-collapsed') ? 'true' : 'false'
                );
            });
        }

        if (topMenuToggle) {
            topMenuToggle.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    return;
                }

                document.body.classList.toggle('top-menu-icons-only');

                localStorage.setItem(
                    'topMenuIconsOnly',
                    document.body.classList.contains('top-menu-icons-only') ? 'true' : 'false'
                );
            });
        }

        if (topMenu) {
            topMenu.addEventListener('wheel', function (e) {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                    e.preventDefault();
                    topMenu.scrollLeft += e.deltaY;
                }
            }, { passive: false });
        }

        if (mobileSidebarBackdrop) {
            mobileSidebarBackdrop.addEventListener('click', function () {
                document.body.classList.remove('mobile-sidebar-open');
            });
        }

        document.querySelectorAll('#appSidebar .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    document.body.classList.remove('mobile-sidebar-open');
                }
            });
        });

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            new bootstrap.Tooltip(element);
        });

        if (window.axios && <?php echo e(auth()->check() ? 'true' : 'false'); ?> && <?php echo e(Route::has('chat.unread-count') ? 'true' : 'false'); ?>) {
            axios.get('<?php echo e(Route::has('chat.unread-count') ? route('chat.unread-count') : '#'); ?>')
                .then(function (response) {
                    const count = response.data.unread_count || 0;

                    const badges = [
                        document.getElementById('unreadBadge'),
                        document.getElementById('sidebarUnreadBadge')
                    ];

                    badges.forEach(function (badge) {
                        if (!badge) return;

                        badge.textContent = count > 0 ? count : '';
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    });
                })
                .catch(function () {});
        }
    });
</script>
<?php /**PATH G:\project\ncckplclims\resources\views/layouts/partials/scripts.blade.php ENDPATH**/ ?>