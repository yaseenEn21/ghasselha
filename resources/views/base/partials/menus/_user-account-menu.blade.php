<!--begin::User account menu-->
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
    data-kt-menu="true">
    <!--begin::Menu item-->
    <div class="menu-item px-3">
        <div class="menu-content d-flex align-items-center px-3">
            <!--begin::Avatar-->
            <div class="symbol symbol-50px me-5">
                <img src="{{ auth()->user()->getFirstMediaUrl('profile_image') ?: asset('assets/media/avatars/blank.png') }}" alt="Avatar" />
            </div>
            <!--end::Avatar-->
            <!--begin::Username-->
            <div class="d-flex flex-column">
                <div class="fw-bold d-flex align-items-center fs-5">
                    {{auth()->user()->name}} 
                </div>
                <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
                    {{auth()->user()->email}} </a>
            </div>
            <!--end::Username-->
        </div>
    </div>
    <!--end::Menu item-->
    <!--begin::Menu separator-->
    <div class="separator my-2"></div>
    <!--end::Menu separator-->
    <!--begin::Menu item-->
    <div class="menu-item px-5">
        <a href="{{route('dashboard.profile.edit')}}" class="menu-link px-5">
            {{ t('my_profile') }}
        </a>
    </div>
    <!--end::Menu item-->
    
    <!--end::Menu item-->
    <!--begin::Menu separator-->
    <div class="separator my-2"></div>
    <!--end::Menu separator-->
    <!--begin::Menu item-->
    <div class="menu-item px-5">
        <!-- Authentication -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a class="menu-link px-5"
                    onclick="event.preventDefault();
                                this.closest('form').submit();">
                {{ t('logout') }}
        </a>
        </form>
    </div>
    <!--end::Menu item-->
</div>
<!--end::User account menu-->
