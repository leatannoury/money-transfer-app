@php
    $adminNotifications = collect($adminBellNotifications ?? []);
    $adminUnreadCount = (int) ($adminBellUnreadCount ?? 0);
    $adminClearUrl = route('admin.notifications.clear');
@endphp

<div
    class="flex items-center gap-4"
    data-admin-notif-root
    data-clear-url="{{ $adminClearUrl }}"
>
    <div class="relative">
        <button
            type="button"
            class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
            data-role="admin-notif-trigger"
            aria-label="Admin notifications"
        >
            <span class="material-symbols-outlined text-text-light dark:text-text-dark text-2xl">
                notifications
            </span>
            @if($adminUnreadCount > 0)
                <span class="absolute top-2 right-2 inline-flex h-2 w-2 rounded-full bg-red-500" data-role="admin-notif-dot"></span>
            @endif
        </button>

        <div
            class="hidden absolute right-0 mt-2 w-80 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl z-50"
            data-role="admin-notif-dropdown"
        >
            <div class="px-4 py-3 border-b border-border-light dark:border-border-dark flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">Notifications</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" data-role="admin-notif-unread-text">
                        {{ $adminUnreadCount }} unread
                    </p>
                </div>
                <button
                    type="button"
                    class="text-xs font-semibold text-primary hover:underline disabled:opacity-40"
                    data-role="admin-notif-clear"
                    {{ $adminUnreadCount === 0 ? 'disabled' : '' }}
                >
                    Clear
                </button>
            </div>

            @if($adminNotifications->isNotEmpty())
                <ul class="max-h-80 overflow-y-auto divide-y divide-border-light dark:divide-border-dark" data-role="admin-notif-list">
                    @foreach($adminNotifications as $notification)
                        <li class="px-4 py-3 {{ !$notification->is_read ? 'bg-gray-50 dark:bg-gray-800/60' : '' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">{{ $notification->title }}</p>
                                    @if($notification->message)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->message }}</p>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="hidden p-4 text-sm text-gray-500 dark:text-gray-400" data-role="admin-notif-empty">
                    No notifications.
                </div>
            @else
                <ul class="hidden" data-role="admin-notif-list"></ul>
                <div class="p-4 text-sm text-gray-500 dark:text-gray-400" data-role="admin-notif-empty">
                    No notifications.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const initAdminNotifDropdowns = () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        document.querySelectorAll('[data-admin-notif-root]').forEach(root => {
            if (root.dataset.initialized === '1') {
                return;
            }
            root.dataset.initialized = '1';

            const trigger = root.querySelector('[data-role="admin-notif-trigger"]');
            const dropdown = root.querySelector('[data-role="admin-notif-dropdown"]');
            const unreadText = root.querySelector('[data-role="admin-notif-unread-text"]');
            const dot = root.querySelector('[data-role="admin-notif-dot"]');
            const clearBtn = root.querySelector('[data-role="admin-notif-clear"]');
            const list = root.querySelector('[data-role="admin-notif-list"]');
            const emptyState = root.querySelector('[data-role="admin-notif-empty"]');
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
        document.addEventListener('DOMContentLoaded', initAdminNotifDropdowns);
    } else {
        initAdminNotifDropdowns();
    }
})();
</script>



