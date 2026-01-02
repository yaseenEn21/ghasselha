@extends('base.layout.app')

@push('custom-style')
@endpush

@section('content')

@section('top-btns')
    @can('services.create')
        <a href="{{ route('dashboard.services.create') }}" class="btn btn-primary">
            {{ __('services.create_new') }}
        </a>
    @endcan
@endsection

{{-- 🧰 بوكس الفلاتر --}}
<div class="card mb-5">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">
                {{ __('services.filters_title') }}
            </span>
            <span class="text-muted mt-1 fw-semibold fs-7">
                {{ __('services.filters_subtitle') }}
            </span>
        </h3>
    </div>

    <div class="card-body pt-4">
        <div class="row g-3">
            {{-- بحث بالاسم --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    {{ __('services.search_by_name') }}
                </label>
                <input type="text" id="search_name" class="form-control"
                    placeholder="{{ __('services.search_by_name_placeholder') }}">
            </div>

            {{-- تصنيف الخدمة --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    {{ __('services.category') }}
                </label>
                <select id="filter_category_id" class="form-select">
                    <option value="">{{ __('services.all_categories') }}</option>
                    @php $locale = app()->getLocale(); @endphp
                    @foreach ($categories as $category)
                        @php
                            $catName = $category->name;
                            if (is_array($catName)) {
                                $catName = $catName[$locale] ?? (reset($catName) ?? '');
                            }
                        @endphp
                        <option value="{{ $category->id }}">{{ $catName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- حالة الخدمة --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    {{ __('services.status') }}
                </label>
                <select id="filter_status" class="form-select">
                    <option value="">{{ __('services.all_statuses') }}</option>
                    <option value="active">{{ __('services.active') }}</option>
                    <option value="inactive">{{ __('services.inactive') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- جدول الخدمات --}}
<div class="card">
    <div class="card-body">
        <div class="">
            <table id="kt_services_table" class="table table-row-bordered gy-5">
                <thead>
                    <tr class="fw-semibold fs-6 text-muted"></tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('custom-script')
<script>
    (function() {
        const locale = '{{ app()->getLocale() }}';
        const dtLangUrl = locale === 'ar' ?
            'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' :
            'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json';

        const table = window.KH.initAjaxDatatable({
            tableId: 'kt_services_table',
            ajaxUrl: '{{ route('dashboard.services.index') }}',
            languageUrl: dtLangUrl,
            searchInputId: 'search_name',
            columns: [{
                    data: 'id',
                    name: 'id',
                    title: "{{ t('datatable.lbl_id') }}"
                },
                {
                    data: 'name',
                    name: 'name',
                    title: "{{ t('datatable.lbl_name') }}"
                },
                {
                    data: 'category_name',
                    name: 'category.name',
                    title: "{{ __('services.category') }}"
                },
                {
                    data: 'duration_minutes',
                    name: 'duration_minutes',
                    title: "{{ __('services.duration') }}"
                },
                {
                    data: 'price',
                    name: 'price',
                    title: "{{ __('services.price') }}"
                },
                {
                    data: 'discounted_price',
                    name: 'discounted_price',
                    title: "{{ __('services.discount_price') }}"
                },
                {
                    data: 'is_active_badge',
                    name: 'is_active',
                    title: "{{ __('services.status') }}",
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    title: "{{ t('datatable.lbl_created_at') }}"
                },
                {
                    data: 'actions',
                    name: 'actions',
                    className: 'text-start',
                    title: '{{ t('datatable.lbl_actions') }}',
                    orderable: false,
                    searchable: false
                }
            ],
            extraData: function(d) {
                d.category_id = $('#filter_category_id').val();
                d.status = $('#filter_status').val();
            },
            delete: {
                buttonSelector: '.js-delete-service',
                routeTemplate: '{{ route('dashboard.services.destroy', ':id') }}',
                token: '{{ csrf_token() }}',
                i18n: {
                    title: '{{ __('messages.confirm_delete_title') }}',
                    text: '{{ __('messages.confirm_delete_text') }}',
                    confirmButtonText: '{{ __('messages.confirm_delete_confirm_button') }}',
                    cancelButtonText: '{{ __('messages.confirm_delete_cancel_button') }}',
                    successTitle: '{{ __('messages.delete_success_title') }}',
                    successText: '{{ __('messages.delete_success_text') }}',
                    errorTitle: '{{ __('messages.delete_error_title') }}',
                    errorText: '{{ __('messages.delete_error_text') }}',
                }
            }
        });

        // إعادة تحميل الجدول عند تغيير الفلاتر
        $('#filter_category_id, #filter_status').on('change', function() {
            if (table) {
                table.ajax.reload();
            }
        });

    })();
</script>
@endpush
