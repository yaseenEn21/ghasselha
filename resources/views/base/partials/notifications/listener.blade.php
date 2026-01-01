@if (auth()->check())
@if (config('services.pusher.key'))
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
@endif
<script>
    (function () {
        if (window.__dashboardNotificationsInit) {
            return;
        }
        window.__dashboardNotificationsInit = true;

        const listBox = document.getElementById('notification-list-box');
        const trigger = document.getElementById('notification-icon');
        const i18n = {
            fallbackTitle: @json(__('notifications.title')),
            viewAll: @json(__('notifications.view_all')),
            placeholder: @json(__('notifications.dropdown_placeholder')),
            loading: @json(__('notifications.loading')),
            empty: @json(__('notifications.empty_list')),
            error: @json(__('notifications.load_error')),
            statusUnread: @json(__('notifications.status_unread')),
            statusRead: @json(__('notifications.status_read')),
        };

        let isLoading = false;
        let audioContext = null;

        function setPlaceholder(text, state = 'info') {
            if (!listBox) {
                return;
            }
            listBox.innerHTML = `
                <div class="px-6 py-8 text-center text-muted" data-state="${state}">
                    ${text}
                </div>
            `;
        }

        function playNotificationSound() {
            try {
                const AudioCtor = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtor) {
                    return;
                }

                if (!audioContext) {
                    audioContext = new AudioCtor();
                }

                if (audioContext.state === 'suspended') {
                    audioContext.resume().catch(() => {});
                }

                const now = audioContext.currentTime;
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.type = 'triangle';
                oscillator.frequency.setValueAtTime(880, now);

                gainNode.gain.setValueAtTime(0.0001, now);
                gainNode.gain.exponentialRampToValueAtTime(0.05, now + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.start(now);
                oscillator.stop(now + 0.4);
            } catch (error) {
                console.warn('Notification sound error:', error);
            }
        }

        function renderNotifications(items) {
            if (!listBox) {
                return;
            }

            if (!items.length) {
                setPlaceholder(i18n.empty, 'empty');
                return;
            }

            const fragment = document.createDocumentFragment();

            items.forEach(function (item) {
                const wrapper = document.createElement('div');
                wrapper.className = 'px-6 py-4 border-bottom';

                const title = document.createElement('div');
                title.className = 'fw-semibold text-gray-900';
                title.textContent = item.title || i18n.fallbackTitle;

                const body = document.createElement('div');
                body.className = 'text-gray-600 fs-7 mt-1';
                body.textContent = item.body || '';

                const metaRow = document.createElement('div');
                metaRow.className = 'd-flex align-items-center gap-2 mt-3';

                const time = document.createElement('span');
                time.className = 'badge badge-light fs-8';
                time.textContent = item.created_at_human || item.created_at || '';
                metaRow.appendChild(time);

                const status = document.createElement('span');
                status.className = 'badge fs-8 ' + (item.is_read ? 'badge-light-success' : 'badge-light-warning');
                status.textContent = item.is_read ? i18n.statusRead : i18n.statusUnread;
                metaRow.appendChild(status);

                wrapper.appendChild(title);
                if (body.textContent.trim() !== '') {
                    wrapper.appendChild(body);
                }
                wrapper.appendChild(metaRow);

                fragment.appendChild(wrapper);
            });

            listBox.innerHTML = '';
            listBox.appendChild(fragment);
        }

        async function loadLatestNotifications(options = {}) {
            const { silent = false } = options;
            if (!listBox || !latestEndpoint) {
                return;
            }

            if (isLoading) {
                return;
            }

            isLoading = true;
            if (!silent) {
                setPlaceholder(i18n.loading, 'loading');
            }

            try {
                const response = await fetch(latestEndpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    cache: 'no-cache',
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const payload = await response.json();
                renderNotifications(Array.isArray(payload.data) ? payload.data : []);
            } catch (error) {
                console.error('Notifications fetch error:', error);
                if (!silent) {
                    setPlaceholder(i18n.error, 'error');
                }
            } finally {
                isLoading = false;
            }
        }

        if (trigger) {
            trigger.addEventListener('click', function () {
                // loadLatestNotifications();
            });
        }

        setPlaceholder(i18n.placeholder, 'placeholder');

        const pusherKey = @json(config('services.pusher.key'));
        if (typeof Pusher !== 'undefined' && pusherKey) {
            Pusher.logToConsole = @json((bool) config('app.debug'));

            const pusher = new Pusher(pusherKey, {
                cluster: @json(config('services.pusher.cluster', 'ap1')),
                forceTLS: @json((bool) config('services.pusher.use_tls', true))
            });

            const channelName = @json(config('services.pusher.channel', 'dashboard.notifications'));
            const eventName = @json(config('services.pusher.event', 'product.created'));

            const channel = pusher.subscribe(channelName);

            channel.bind(eventName, function (payload) {
                renderToast(payload || {});
                playNotificationSound();
                // loadLatestNotifications({ silent: true });
            });

            function ensureContainer() {
                let container = document.getElementById('dashboard-live-notifications');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'dashboard-live-notifications';
                    container.className = 'toast-container position-fixed bottom-0 start-0 p-4';
                    container.style.zIndex = '1095';
                    container.setAttribute('dir', document.documentElement.dir || 'ltr');
                    document.body.appendChild(container);
                }
                return container;
            }

            function renderToast(data) {
                const container = ensureContainer();
                const toastElement = document.createElement('div');
                toastElement.className = 'toast align-items-start shadow-lg border-0 text-bg-dark';
                toastElement.setAttribute('role', 'alert');
                toastElement.setAttribute('aria-live', 'assertive');
                toastElement.setAttribute('aria-atomic', 'true');

                const header = document.createElement('div');
                header.className = 'toast-header text-bg-dark border-0';

                const title = document.createElement('strong');
                title.className = 'me-auto';
                title.textContent = data.title || i18n.fallbackTitle;
                header.appendChild(title);

                const time = document.createElement('small');
                time.className = 'text-muted ms-2';
                time.textContent = data.created_at_human || '';
                header.appendChild(time);

                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn-close btn-close-white ms-2';
                closeButton.setAttribute('data-bs-dismiss', 'toast');
                closeButton.setAttribute('aria-label', 'Close');
                header.appendChild(closeButton);

                const body = document.createElement('div');
                body.className = 'toast-body';

                const message = document.createElement('p');
                message.className = 'mb-3';
                message.textContent = data.body || '';
                body.appendChild(message);

                const footer = document.createElement('div');
                footer.className = 'd-flex gap-2';

                const viewAllLink = document.createElement('a');
                // viewAllLink.href = notificationsUrl;
                viewAllLink.className = 'btn btn-sm btn-light';
                viewAllLink.textContent = i18n.viewAll;
                footer.appendChild(viewAllLink);

                body.appendChild(footer);

                toastElement.appendChild(header);
                toastElement.appendChild(body);

                container.appendChild(toastElement);

                if (window.bootstrap?.Toast) {
                    const toast = new bootstrap.Toast(toastElement, { delay: 8000 });
                    toastElement.addEventListener('hidden.bs.toast', function () {
                        toastElement.remove();
                    });
                    toast.show();
                } else {
                    toastElement.classList.add('show');
                }
            }
        }
    })();
</script>



@endif


