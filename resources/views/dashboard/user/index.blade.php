@extends('base.layout.app')

@push('custom-style')
@endpush

@section('content')

    @section('top-btns')
        @can('users.create')
            <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary">
                {{t('users.create_new')}}
            </a>
        @endcan
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="row mb-5">
                <div class="col-md-4">
                    <input type="text" id="search_name" class="form-control" placeholder="{{ t('messages.search') }}">
                </div>
            </div>

            <div class="">
                <table id="kt_datatable_zero_configuration" class="table table-row-bordered gy-5">
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
            const table = $("#kt_datatable_zero_configuration").DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('dashboard.users.index') }}',
                    data: d => {
                        d.search_name = $('#search_name').val();
                    }
                },
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
                        data: 'email',
                        name: 'email',
                        title: "{{ t('datatable.lbl_email') }}"
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: "{{ t('datatable.lbl_created_at') }}"
                    },
                    {
                        data: 'role_name',
                        name: 'role_name',
                        title: "{{ t('datatable.lbl_role') }}"
                    },
                    {
                        data: 'creator_name',
                        name: 'creator.name',
                        className: 'text-start',
                        title: 'بواسطة'
                    },
                    {
                        data: 'actions',
                        name: 'creator.name',
                        className: 'text-start',
                        title: '{{ t('datatable.lbl_actions') }}',
                        orderable: false
                    },
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
                }
            });


            let debounceTimer = null;
            $('#search_name').on('keyup change', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => table.draw(), 300);
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'تم',
                    text: @json(session('success')),
                    timer: 1500,
                    showConfirmButton: false
                });
            @endif
            
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: @json(session('error'))
                });
            @endif

        })();
    </script>
@endpush
