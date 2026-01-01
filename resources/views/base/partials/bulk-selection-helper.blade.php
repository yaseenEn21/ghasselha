@once
    @push('custom-script')
        <script>
            window.createBulkSelection = function (options) {
                const state = new Set();
                const $table = $(options.tableSelector);
                const $selectAll = $(options.selectAllSelector);
                const $bulkBtn = $(options.bulkButtonSelector);

                function updateButton() {
                    const hasSelected = state.size > 0;
                    $bulkBtn.toggleClass('d-none', !hasSelected);
                    $bulkBtn.prop('disabled', !hasSelected);
                }

                function syncSelectAll() {
                    const $checkboxes = $table.find('.bulk-checkbox');
                    if (!$checkboxes.length) {
                        $selectAll.prop('checked', false);
                        return;
                    }
                    const checked = $checkboxes.filter(':checked').length === $checkboxes.length;
                    $selectAll.prop('checked', checked);
                }

                function purgeInvisible() {
                    const visibleIds = new Set();
                    $table.find('.bulk-checkbox').each(function () {
                        visibleIds.add(this.value);
                    });
                    for (const value of Array.from(state)) {
                        if (!visibleIds.has(value)) {
                            state.delete(value);
                        }
                    }
                }

                function clearSelection() {
                    state.clear();
                    $table.find('.bulk-checkbox').prop('checked', false);
                    $selectAll.prop('checked', false);
                    updateButton();
                }

                function performBulkDelete(table) {
                    const ids = Array.from(state);
                    const requests = ids.map((id) =>
                        fetch(`${options.deleteUrl}/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': options.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: new URLSearchParams({ _method: 'DELETE' }),
                        }).then(async (res) => {
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) {
                                throw new Error(data.message || 'تعذر حذف السجل.');
                            }
                            return data;
                        })
                    );

                    Promise.allSettled(requests).then((results) => {
                        const hasFailed = results.some((result) => result.status === 'rejected');

                        if (typeof Swal !== 'undefined') {
                            if (hasFailed) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'فشل جزئي',
                                    text: 'تعذر حذف بعض السجلات، يرجى التحقق وإعادة المحاولة.',
                                });
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف',
                                    text: 'تم حذف السجلات المحددة بنجاح.',
                                    timer: 1500,
                                    showConfirmButton: false,
                                });
                            }
                        } else if (hasFailed) {
                            alert('تعذر حذف بعض السجلات.');
                        }

                        clearSelection();
                        table.ajax.reload(null, false);
                    }).catch(() => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'خطأ', text: 'حدث خطأ غير متوقع أثناء الحذف.' });
                        } else {
                            alert('حدث خطأ غير متوقع أثناء الحذف.');
                        }
                    });
                }

                function bind(table) {
                    table.on('draw', function () {
                        purgeInvisible();
                        $table.find('.bulk-checkbox').each(function () {
                            this.checked = state.has(this.value);
                        });
                        syncSelectAll();
                        updateButton();
                    });

                    $table.on('change', '.bulk-checkbox', function () {
                        if (this.checked) {
                            state.add(this.value);
                        } else {
                            state.delete(this.value);
                        }
                        syncSelectAll();
                        updateButton();
                    });

                    $selectAll.on('change', function () {
                        const checked = this.checked;
                        $table.find('.bulk-checkbox').each(function () {
                            this.checked = checked;
                            if (checked) {
                                state.add(this.value);
                            } else {
                                state.delete(this.value);
                            }
                        });
                        updateButton();
                    });

                    $bulkBtn.on('click', function () {
                        if (!state.size) {
                            return;
                        }

                        const proceed = () => performBulkDelete(table);

                        if (typeof Swal === 'undefined') {
                            if (confirm(`سيتم حذف ${state.size} سجلات. هل أنت متأكد؟`)) {
                                proceed();
                            }
                            return;
                        }

                        Swal.fire({
                            title: 'تأكيد الحذف',
                            text: `سيتم حذف ${state.size} سجلات. هل أنت متأكد؟`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'نعم، حذف',
                            cancelButtonText: 'إلغاء',
                            reverseButtons: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                proceed();
                            }
                        });
                    });
                }

                return {
                    columns(originalColumns) {
                        return [
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                width: '40px',
                                render: function (data, type, row) {
                                    if (type === 'display') {
                                        return `<div class="form-check form-check-sm">
                                                    <input type="checkbox" class="form-check-input bulk-checkbox" value="${row.id}">
                                                </div>`;
                                    }
                                    return row.id;
                                },
                            },
                            ...originalColumns,
                        ];
                    },
                    bind,
                    clear: clearSelection,
                };
            };
        </script>
    @endpush
@endonce
