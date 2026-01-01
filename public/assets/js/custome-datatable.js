window.KH = window.KH || {};


/**
 * تشغيل/إيقاف حالة التحميل لفورم:
 * - يعطّل زر الإرسال
 * - يخفي .indicator-label (لو موجودة)
 * - يظهر .indicator-progress-v1 (أو ينشئها لو غير موجودة)
 */
window.KH.setFormLoading = function (form, isLoading, options) {
    var $form = form instanceof jQuery ? form : $(form);
    if (!$form.length) return;

    var $btn = $form.find('button[type="submit"], input[type="submit"]').first();
    if (!$btn.length) return;

    var text = (options && options.text) ? options.text : 'جاري الحفظ...';

    var $label = $btn.find('.indicator-label');
    var $progress = $btn.find('.indicator-progress-v1');

    // لو ما في indicator-progress-v1 ننشئها مرة واحدة
    if (!$progress.length) {
        $progress = $('<span class="indicator-progress-v1 d-none"></span>')
            .html(
                text +
                ' <span class="spinner-border spinner-border-sm align-middle ms-2"></span>'
            );

        if ($label.length) {
            $label.after($progress);
        } else {
            $btn.append($progress);
        }
    }

    if (isLoading) {
        $btn.prop('disabled', true);
        if ($label.length) $label.addClass('d-none');
        $progress.removeClass('d-none');
    } else {
        $btn.prop('disabled', false);
        if ($label.length) $label.removeClass('d-none');
        $progress.addClass('d-none');
    }
};

/**
 * عرض أخطاء الفاليديشن على الحقول + ألرت عام اختياري
 * options.globalAlertSelector: مثل '#invite_create_result'
 */
window.KH.showValidationErrors = function (form, errors, options) {
    var $form = form instanceof jQuery ? form : $(form);
    if (!$form.length) return;

    var globalSelector = options && options.globalAlertSelector ? options.globalAlertSelector : null;
    var $globalAlert = globalSelector ? $(globalSelector) : null;

    // تنظيف قديم
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');

    if ($globalAlert && $globalAlert.length) {
        $globalAlert
            .addClass('d-none')
            .removeClass('alert-danger alert-success')
            .html('');
    }

    if (!errors) return;

    var globalMessages = [];

    Object.keys(errors).forEach(function (field) {
        var messages = errors[field];
        var $input = $form.find('[name="' + field + '"]');

        // لو ما لقينا الحقل -> نخزن الرسالة للـ global
        if (!$input.length) {
            globalMessages = globalMessages.concat(messages);
            return;
        }

        $input.addClass('is-invalid');

        var $feedback = $input.siblings('.invalid-feedback');
        if (!$feedback.length) {
            $feedback = $input.closest('.mb-3, .fv-row, .form-group')
                .find('.invalid-feedback').first();
        }

        if ($feedback.length) {
            $feedback.text(messages[0]); // أول رسالة للحقل
        } else {
            globalMessages = globalMessages.concat(messages);
        }
    });

    // عرض الرسائل العامة في الألرت (لو موجودة)
    if ($globalAlert && $globalAlert.length && globalMessages.length) {
        var html = '';
        globalMessages.forEach(function (msg) {
            html += '<div> - ' + msg + '</div>';
        });

        $globalAlert
            .removeClass('d-none')
            .addClass('alert-danger')
            .html(html);
    }
};


/**
 * Helper عام لتهيئة داتاتيبل AJAX + بحث خارجي + فلتر حالة + حذف بـ SweetAlert
 */
window.KH.initAjaxDatatable = function (config) {
    if (!config || !config.tableId || !config.ajaxUrl || !config.columns) {
        console.error('KH.initAjaxDatatable: tableId, ajaxUrl, columns are required');
        return null;
    }

    let currentStatus = '';

    let table = $('#' + config.tableId).DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language: config.languageUrl
            ? { url: config.languageUrl }
            : {},
        ajax: {
            url: config.ajaxUrl,
            data: function (d) {
                // فلتر الحالة
                if (config.statusParamName) {
                    d[config.statusParamName] = currentStatus;
                }

                // البحث الخارجي
                if (config.searchInputId) {
                    d.search_custom = $('#' + config.searchInputId).val();
                }

                // بيانات إضافية
                if (typeof config.extraData === 'function') {
                    config.extraData(d);
                }
            }
        },
        dom:
            "<'table-responsive'tr>" +
            "<'row mt-3'" +
            "<'col-sm-6 d-flex align-items-center justify-content-start'i>" +
            "<'col-sm-6 d-flex align-items-center justify-content-end'p>" +
            ">",
        order: config.order || [[0, 'desc']],
        lengthMenu: config.lengthMenu || [10, 25, 50, 100],
        pageLength: config.pageLength || 10,
        columns: config.columns
    });

    // 🔍 البحث الخارجي
    if (config.searchInputId) {
        $('#' + config.searchInputId).on('keyup', function () {
            table.ajax.reload();
        });
    }

    // 🎛 فلتر الحالة بالدروب داون
    if (config.statusMenuId && config.statusLabelId) {
        let $menu = $('#' + config.statusMenuId);
        let $label = $('#' + config.statusLabelId);

        $menu.on('click', 'a.dropdown-item', function (e) {
            e.preventDefault();

            $menu.find('a.dropdown-item').removeClass('active');
            $menu.find('.status-check').addClass('d-none');

            $(this).addClass('active');
            $(this).find('.status-check').removeClass('d-none');

            let text = $(this).find('span:first').text();
            $label.text(text);

            currentStatus = $(this).data('status') ?? '';

            table.ajax.reload();
        });
    }

    // 🗑 الحذف بـ SweetAlert + AJAX
    if (config.delete && config.delete.buttonSelector && config.delete.routeTemplate && config.delete.token) {
        $(document).on('click', config.delete.buttonSelector, function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            if (!id) return;

            let url = config.delete.routeTemplate.replace(':id', id);

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'سيتم حذف هذا السجل نهائياً.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، حذف',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: config.delete.token
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الحذف',
                                text: res.message || 'تم حذف السجل بنجاح.',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            table.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            let msg = 'حدث خطأ غير متوقع.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: msg
                            });
                        }
                    });
                }
            });
        });
    }

    return table;
};

window.KH.initAjaxEditModal = function (config) {
    if (!config.buttonSelector || !config.modalId || !config.formId ||
        !config.fetchUrl || !config.updateUrl || !config.token) {
        console.error('KH.initAjaxEditModal: missing required config');
        return;
    }

    let $modal = $('#' + config.modalId);
    let $form = $('#' + config.formId);
    let currentId = null;

    // فتح المودال + تعبئة البيانات
    $(document).on('click', config.buttonSelector, function (e) {
        e.preventDefault();

        currentId = $(this).data('id');
        if (!currentId) return;

        let url = (typeof config.fetchUrl === 'function')
            ? config.fetchUrl(currentId)
            : config.fetchUrl.replace(':id', currentId);

        // تنظيف الأخطاء القديمة
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        $.get(url, function (res) {
            if (config.onFill && res.data) {
                config.onFill(res.data);
            }
            $modal.modal('show');
        }).fail(function () {
            Swal.fire('خطأ', 'تعذر جلب بيانات السجل.', 'error');
        });
    });

    // حفظ التعديل
    // حفظ التعديل
    $form.on('submit', function (e) {
        e.preventDefault();
        if (!currentId) return;

        let url = (typeof config.updateUrl === 'function')
            ? config.updateUrl(currentId)
            : config.updateUrl.replace(':id', currentId);

        // 🔄 فعّل حالة التحميل على زر الإرسال
        window.KH.setFormLoading($form, true, { text: 'جاري الحفظ...' });

        $.ajax({
            url: url,
            type: 'POST',
            data: $form.serialize() + '&_method=PUT&_token=' + config.token,
            success: function (res) {
                if (config.table) {
                    config.table.ajax.reload(null, false);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'تم الحفظ',
                    text: res.message || 'تم حفظ التعديل بنجاح.',
                    timer: 2000,
                    showConfirmButton: false
                });

                $modal.modal('hide');
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    KH.showValidationErrors($form, xhr.responseJSON.errors, {
                        globalAlertSelector: config.globalAlertSelector // لو حاب في بعض المودالات
                    });
                } else {
                    let msg = 'حدث خطأ غير متوقع.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('خطأ', msg, 'error');
                }
            },
            complete: function () {
                // ✅ أوقف حالة التحميل دائماً في النهاية
                window.KH.setFormLoading($form, false);
            }
        });
    });

};

