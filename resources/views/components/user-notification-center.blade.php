@php
    $notificationItems = collect($userBellNotifications ?? []);
    $unreadCount = (int) ($userBellUnreadCount ?? 0);
    $clearUrl = route('user.notifications.clear');
@endphp

<div
    class="flex items-center gap-4"
    data-user-notif-root
    data-clear-url="{{ $clearUrl }}"
>
    <div class="relative">
        <button
            type="button"
            class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-black/5 dark:hover:bg-white/10"
            data-role="notif-trigger"
            aria-label="User notifications"
        >
            <span class="material-symbols-outlined text-black dark:text-white text-2xl">
                notifications
            </span>

            @if($unreadCount > 0)
                <span
                    class="absolute top-2 right-2 inline-flex h-2 w-2 rounded-full bg-red-500"
                    data-role="notif-dot"
                ></span>
            @endif
        </button>

        <div
            class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50"
            data-role="notif-dropdown"
        >
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" data-role="notif-unread-text">
                        {{ $unreadCount }} unread
                    </p>
                </div>
                <button
                    type="button"
                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 hover:underline disabled:opacity-40"
                    data-role="notif-clear"
                    {{ $unreadCount === 0 ? 'disabled' : '' }}
                >
                    Clear
                </button>
            </div>

            @if($notificationItems->isNotEmpty())
                <ul class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800" data-role="notif-list">
                    @foreach($notificationItems as $notification)
                        <li class="px-4 py-3 {{ !$notification->is_read ? 'bg-black/5 dark:bg-white/5' : '' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $notification->title }}
                                    </p>
                                    @if($notification->message)
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                            {{ $notification->message }}
                                        </p>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="hidden p-4 text-sm text-gray-500 dark:text-gray-400" data-role="notif-empty">
                    No notifications yet.
                </div>
            @else
                <ul class="hidden" data-role="notif-list"></ul>
                <div class="p-4 text-sm text-gray-500 dark:text-gray-400" data-role="notif-empty">
                    No notifications yet.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const initUserNotificationDropdowns = () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        document.querySelectorAll('[data-user-notif-root]').forEach((root) => {
            if (root.dataset.initialized === '1') {
                return;
            }
            root.dataset.initialized = '1';

            const trigger = root.querySelector('[data-role="notif-trigger"]');
            const dropdown = root.querySelector('[data-role="notif-dropdown"]');
            const clearBtn = root.querySelector('[data-role="notif-clear"]');
            const unreadText = root.querySelector('[data-role="notif-unread-text"]');
            const dot = root.querySelector('[data-role="notif-dot"]');
            const list = root.querySelector('[data-role="notif-list"]');
            const emptyState = root.querySelector('[data-role="notif-empty"]');
            const clearUrl = root.dataset.clearUrl;

            if (trigger && dropdown) {
                trigger.addEventListener('click', (event) => {
                    event.stopPropagation();
                    dropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', (event) => {
                    if (!root.contains(event.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            }

            if (clearBtn && clearUrl && csrf) {
                clearBtn.addEventListener('click', () => {
                    if (clearBtn.disabled) {
                        return;
                    }

                    clearBtn.disabled = true;

                    fetch(clearUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    })
                        .then(() => {
                            if (unreadText) {
                                unreadText.textContent = '0 unread';
                            }
                            if (dot) {
                                dot.remove();
                            }
                            if (list) {
                                list.innerHTML = '';
                                list.classList.add('hidden');
                            }
                            if (emptyState) {
                                emptyState.classList.remove('hidden');
                            }
                        })
                        .catch(() => {
                            clearBtn.disabled = false;
                        });
                });
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserNotificationDropdowns);
    } else {
        initUserNotificationDropdowns();
    }
})();
</script>




