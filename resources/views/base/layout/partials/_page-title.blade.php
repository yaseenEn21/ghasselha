<!--begin::Page title-->
<div class="page-title d-flex justify-content-between flex-wrap me-3 w-100">
    <!--begin::Title-->
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
        {{$page_title ?? ''}}       
        <br>
        @yield('breadcrumb') 
    </h1>
    @yield('top-btns')
    <!--end::Title-->
</div>
<!--end::Page title-->
