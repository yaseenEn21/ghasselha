@extends('base.layout.app')

@push('custom-style')
@endpush

@section('content')
    <div class="card">
        @section('top-btns')
            @can('roles.create')
                <a href="{{ route('dashboard.roles.create') }}" class="btn btn-primary">
                    {{t('roles.create_new')}}
                </a>
            @endcan
        @endsection

        <div class="card-body">
            <div class="row mb-5">
                <div class="col-md-4">
                    <input type="text" id="search_name" class="form-control" placeholder="{{ t('messages.search') }}">
                </div>
            </div>

            <div class="table-responsive">
                <table id="roles_table" class="table table-row-bordered gy-5 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>عدد الصلاحيات</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('custom-script')

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function() {
            const table = $('#roles_table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('dashboard.roles.index') }}',
                    data: function(d) {
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
                        title: 'الاسم'
                    },
                    {
                        data: 'permissions_count',
                        name: 'permissions_count',
                        title: 'عدد الصلاحيات'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: 'تاريخ الإنشاء'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        title: 'إجراءات',
                        orderable: false,
                        searchable: false
                    },
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
                },
                order: [
                    [0, 'desc']
                ]
            });

            // بحث بالاسم (debounce)
            let t = null;
            $('#search_name').on('keyup change', function() {
                clearTimeout(t);
                t = setTimeout(() => table.draw(), 300);
            });

            // ✅ تأكيد وحذف عبر AJAX
            $(document).on('click', '.js-delete', function() {
                const url = this.dataset.url;
                const name = this.dataset.name || '';

                Swal.fire({
                    title: 'تأكيد الحذف',
                    text: name ? `سيتم حذف الدور: ${name}` : 'سيتم حذف هذا الدور.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: new URLSearchParams({
                                _method: 'DELETE'
                            })
                        })
                        .then(async (res) => {
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) throw new Error(data.message || 'تعذر الحذف');
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الحذف',
                                text: data.message || 'تم حذف الدور بنجاح',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null,
                            false); // إعادة تحميل دون فقدان الصفحة الحالية
                        })
                        .catch((err) => {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: err.message || 'حدث خطأ غير متوقع'
                            });
                        });
                });
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
