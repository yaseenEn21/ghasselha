<!--begin::Content-->
<div id="kt_app_content" class="app-content  flex-column-fluid ">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        @yield('content')
        {{-- <!--begin::Row-->
        <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10 h-100">
                @include('base.partials/widgets/cards/_widget-20')
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row--> --}}
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->
