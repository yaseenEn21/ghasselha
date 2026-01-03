@extends('base.layout.app')

@push('custom-style')
@endpush

@section('content')

    @section('top-btns')
        @can('packages.create')
            <a href="{{ route('dashboard.packages.create') }}" class="btn btn-primary">
                {{ __('packages.create_new') }}
            </a>
        @endcan
    @endsection

    {{-- فلاتر --}}
    <div class="card mb-5">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">
                    {{ __('packages.filters_title') }}
                </span>
                <span class="text-muted mt-1 fw-semibold fs-7">
                    {{ __('packages.filters_subtitle') }}
                </span>
            </h3>
        </div>
        <div class="card-body pt-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        {{ __('packages.search_by_name') }}
                    </label>
                    <input type="text" id="search_name" class="form-control"
                           placeholder="{{ __('packages.search_by_name_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        {{ __('packages.status') }}
                    </label>
                    <select id="filter_status" class="form-select">
                        <option value="">{{ __('packages.all_statuses') }}</option>
                        <option value="active">{{ __('packages.active') }}</option>
                        <option value="inactive">{{ __('packages.inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- جدول --}}
    <div class="card">
        <div class="card-body">
            <table id="kt_packages_table" class="table table-row-bordered gy-5">
                <thead>
                <tr class="fw-semibold fs-6 text-muted"></tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('custom-script')
    <script>
        (function () {
            const locale = '{{ app()->getLocale() }}';
            const dtLangUrl = locale === 'ar'
                ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json';

            const table = window.KH.initAjaxDatatable({
                tableId: 'kt_packages_table',
                ajaxUrl: '{{ route('dashboard.packages.index') }}',
                languageUrl: dtLangUrl,
                searchInputId: 'search_name',
                columns: [
                    { data: 'id', name: 'id', title: "{{ t('datatable.lbl_id') }}" },
                    { data: 'name', name: 'name', title: "{{ t('datatable.lbl_name') }}" },
                    { data: 'validity_days', name: 'validity_days', title: "{{ __('packages.validity_days') }}" },
                    { data: 'washes_count', name: 'washes_count', title: "{{ __('packages.washes_count') }}" },
                    { data: 'price', name: 'price', title: "{{ __('packages.price') }}" },
                    { data: 'discounted_price', name: 'discounted_price', title: "{{ __('packages.discount_price') }}" },
                    { data: 'is_active_badge', name: 'is_active', title: "{{ __('packages.status') }}", searchable: false },
                    { data: 'created_at', name: 'created_at', title: "{{ t('datatable.lbl_created_at') }}" },
                    { data: 'actions', name: 'actions', title: "{{ t('datatable.lbl_actions') }}", orderable: false, searchable: false },
                ],
                extraData: function (d) {
                    d.status = $('#filter_status').val();
                },
                delete: {
                    buttonSelector: '.js-delete-package',
                    routeTemplate: '{{ route('dashboard.packages.destroy', ':id') }}',
                    token: '{{ csrf_token() }}',
                    i18n: {
                        title:             '{{ __('messages.confirm_delete_title') }}',
                        text:              '{{ __('messages.confirm_delete_text') }}',
                        confirmButtonText: '{{ __('messages.confirm_delete_confirm_button') }}',
                        cancelButtonText:  '{{ __('messages.confirm_delete_cancel_button') }}',
                        successTitle:      '{{ __('messages.delete_success_title') }}',
                        successText:       '{{ __('messages.delete_success_text') }}',
                        errorTitle:        '{{ __('messages.delete_error_title') }}',
                        errorText:         '{{ __('messages.delete_error_text') }}',
                    }
                }
            });

            $('#filter_status').on('change', function () {
                if (table) table.ajax.reload();
            });
        })();
    </script>
@endpush