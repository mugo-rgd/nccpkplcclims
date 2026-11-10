



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    const MOBILE_BREAKPOINT = 768;

    const config = {
        authenticated: <?php echo json_encode(auth()->check(), 15, 512) ?>,

        chatUnreadUrl: <?php echo json_encode(
            auth()->check() && Route::has('chat.unread-count')
                ? route('chat.unread-count')
                : null
        , 15, 512) ?>
    };

    /*
    |--------------------------------------------------------------------------
    | Axios Configuration
    |--------------------------------------------------------------------------
    */

    if (typeof window.axios !== 'undefined') {
        window.axios.defaults.headers.common[
            'X-Requested-With'
        ] = 'XMLHttpRequest';

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        );

        if (csrfToken) {
            window.axios.defaults.headers.common[
                'X-CSRF-TOKEN'
            ] = csrfToken.getAttribute('content');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Safe Local Storage
    |--------------------------------------------------------------------------
    */

    function getPreference(key) {
        try {
            return localStorage.getItem(key) === 'true';
        } catch (error) {
            return false;
        }
    }

    function savePreference(key, value) {
        try {
            localStorage.setItem(
                key,
                value ? 'true' : 'false'
            );
        } catch (error) {
            // Storage may be unavailable.
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Initialization
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const body = document.body;

        const sidebar = document.getElementById(
            'appSidebar'
        );

        const sidebarToggle = document.getElementById(
            'sidebarToggle'
        );

        const topMenuToggle = document.getElementById(
            'topMenuToggle'
        );

        const mobileSidebarBackdrop =
            document.getElementById(
                'mobileSidebarBackdrop'
            );

        const topMenu = document.querySelector(
            '.top-menu-scroll, .kplc-menu-scroll'
        );

        /*
        |--------------------------------------------------------------------------
        | Responsive Helpers
        |--------------------------------------------------------------------------
        */

        function isMobile() {
            return window.innerWidth <= MOBILE_BREAKPOINT;
        }

        function closeMobileSidebar() {
            body.classList.remove(
                'mobile-sidebar-open'
            );

            if (sidebarToggle) {
                sidebarToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );
            }
        }

        function restorePreferences() {

            if (isMobile()) {
                body.classList.remove(
                    'sidebar-collapsed',
                    'top-menu-icons-only'
                );

                closeMobileSidebar();

                return;
            }

            body.classList.toggle(
                'sidebar-collapsed',
                getPreference('sidebarCollapsed')
            );

            body.classList.toggle(
                'top-menu-icons-only',
                getPreference('topMenuIconsOnly')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Restore Saved Menu Preferences
        |--------------------------------------------------------------------------
        */

        restorePreferences();

        /*
        |--------------------------------------------------------------------------
        | Sidebar Toggle
        |--------------------------------------------------------------------------
        */

        if (sidebarToggle && sidebar) {

            sidebarToggle.addEventListener(
                'click',
                function () {

                    if (isMobile()) {

                        const isOpen =
                            body.classList.toggle(
                                'mobile-sidebar-open'
                            );

                        sidebarToggle.setAttribute(
                            'aria-expanded',
                            isOpen ? 'true' : 'false'
                        );

                        return;
                    }

                    const collapsed =
                        body.classList.toggle(
                            'sidebar-collapsed'
                        );

                    savePreference(
                        'sidebarCollapsed',
                        collapsed
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Top Menu Compact / Expand
        |--------------------------------------------------------------------------
        */

        if (topMenuToggle) {

            topMenuToggle.addEventListener(
                'click',
                function () {

                    if (isMobile()) {
                        return;
                    }

                    const compact =
                        body.classList.toggle(
                            'top-menu-icons-only'
                        );

                    savePreference(
                        'topMenuIconsOnly',
                        compact
                    );

                    topMenuToggle.setAttribute(
                        'aria-pressed',
                        compact ? 'true' : 'false'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Horizontal Top Menu Scrolling
        |--------------------------------------------------------------------------
        */

        if (topMenu) {

            topMenu.addEventListener(
                'wheel',
                function (event) {

                    if (
                        Math.abs(event.deltaY) >
                        Math.abs(event.deltaX) &&
                        topMenu.scrollWidth >
                        topMenu.clientWidth
                    ) {

                        event.preventDefault();

                        topMenu.scrollLeft +=
                            event.deltaY;
                    }
                },
                { passive: false }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile Sidebar Backdrop
        |--------------------------------------------------------------------------
        */

        if (mobileSidebarBackdrop) {

            mobileSidebarBackdrop.addEventListener(
                'click',
                closeMobileSidebar
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Close Sidebar After Navigation
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            '#appSidebar a.nav-link'
        ).forEach(function (link) {

            link.addEventListener(
                'click',
                function () {

                    if (isMobile()) {
                        closeMobileSidebar();
                    }
                }
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Escape Key Closes Mobile Sidebar
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    body.classList.contains(
                        'mobile-sidebar-open'
                    )
                ) {
                    closeMobileSidebar();
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Window Resize
        |--------------------------------------------------------------------------
        */

        let wasMobile = isMobile();

        window.addEventListener(
            'resize',
            function () {

                const nowMobile = isMobile();

                if (nowMobile !== wasMobile) {
                    wasMobile = nowMobile;
                    restorePreferences();
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Bootstrap Tooltips
        |--------------------------------------------------------------------------
        */

        if (
            typeof window.bootstrap !== 'undefined' &&
            typeof bootstrap.Tooltip !== 'undefined'
        ) {

            document.querySelectorAll(
                '[data-bs-toggle="tooltip"]'
            ).forEach(function (element) {

                bootstrap.Tooltip.getOrCreateInstance(
                    element
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Chat Unread Badge
        |--------------------------------------------------------------------------
        */

        function updateUnreadBadges(count) {

            const unreadCount = Math.max(
                0,
                Number(count) || 0
            );

            const badges = [
                document.getElementById(
                    'unreadBadge'
                ),
                document.getElementById(
                    'sidebarUnreadBadge'
                )
            ];

            badges.forEach(function (badge) {

                if (!badge) {
                    return;
                }

                badge.textContent =
                    unreadCount > 99
                        ? '99+'
                        : (
                            unreadCount > 0
                                ? String(unreadCount)
                                : ''
                        );

                badge.style.display =
                    unreadCount > 0
                        ? 'inline-block'
                        : 'none';
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Fetch Unread Chat Count
        |--------------------------------------------------------------------------
        */

        function fetchUnreadChatCount() {

            if (
                !config.authenticated ||
                !config.chatUnreadUrl ||
                typeof window.axios === 'undefined'
            ) {
                return;
            }

            window.axios.get(
                config.chatUnreadUrl,
                {
                    timeout: 10000,
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            )
            .then(function (response) {

                const count =
                    response.data?.unread_count ?? 0;

                updateUnreadBadges(count);
            })
            .catch(function (error) {

                console.warn(
                    'Unable to load unread chat count:',
                    error.message
                );
            });
        }

        fetchUnreadChatCount();

    });

})();
</script>
<?php /**PATH G:\project\ncckplclims\resources\views/layouts/partials/scripts.blade.php ENDPATH**/ ?>