@php
    $notificationsTitle = __('notifications.title');
@endphp
<div class="menu menu-sub menu-sub-dropdown menu-column w-350px" data-kt-menu="true" id="kt_menu_notifications">
    <div class="d-flex align-items-center justify-content-between px-6 py-4 border-bottom">
        <h3 class="mb-0 fs-5 fw-semibold">{{ $notificationsTitle }}</h3>
        {{-- <a href="{{ route('dashboard.notifications.index') }}" class="btn btn-sm btn-light-primary">
            {{ __('notifications.view_all') }}
        </a> --}}
    </div>
    <div class="scroll-y mh-325px" id="notification-list-box">
        <div class="px-6 py-8 text-center text-muted" data-state="placeholder">
            {{ __('notifications.dropdown_placeholder') }}
        </div>
    </div>
    <div class="border-top px-6 py-4 d-none" id="notification-empty-state">
        <div class="text-center text-muted fs-7">
            {{ __('notifications.empty_list') }}
        </div>
    </div>
</div>
