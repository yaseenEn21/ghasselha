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
KH.showValidationErrors = function (form, errors, options) {
    options = options || {};
    const $form = $(form);

    // 1) نظف الأخطاء القديمة
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('').hide();

    if (options.globalAlertSelector) {
        const $alert = $(options.globalAlertSelector);
        $alert.removeClass('alert-danger alert-success').addClass('d-none').empty();
    }

    let firstMessage = null;

    Object.keys(errors).forEach(function (field) {
        const messages = errors[field];
        if (!firstMessage) {
            firstMessage = messages[0];
        }

        let inputName = field;

        // 🔁 1) حوّل name.ar → name[ar]
        if (field.indexOf('.') !== -1) {
            const parts = field.split('.');
            const root = parts.shift(); // name
            inputName = root + '[' + parts.join('][') + ']'; // name[ar]
        }

        let $field = $form.find('[name="' + inputName + '"]');

        // 🔁 2) لو ما لقينا، جرّب شكل المصفوفة name[]
        if (!$field.length) {
            $field = $form.find('[name="' + inputName + '[]"]');
        }

        // 🔁 3) لو ما لقينا ولسه المفتاح فيه نقطة (زي name.ar)، جرّب نلقط أي حقل يبدأ بـ root[
        if (!$field.length && field.indexOf('.') !== -1) {
            const root = field.split('.')[0]; // "name"
            const $candidates = $form.find('[name^="' + root + '[');
            if ($candidates.length) {
                // في حالتك غالبًا أول واحد هو name[ar]
                $field = $candidates.first();
            }
        }

        if ($field.length) {
            $field.addClass('is-invalid');

            const $feedback = $field.closest('.fv-row, .mb-3, .col, .form-group')
                .find('.invalid-feedback')
                .first();

            if ($feedback.length) {
                $feedback.text(messages[0]).show();
            }
        } else if (options.globalAlertSelector) {
            // لو فعليًا مش لقينا ولا حقل، خلّي الرسالة على الأقل في الأليرت العام
            const $alert = $(options.globalAlertSelector);
            $alert.removeClass('d-none')
                .addClass('alert alert-danger');
            $alert.append('<div>' + messages[0] + '</div>');
        }
    });

    // 4) أول رسالة في الأليرت العام (اختياري)
    if (options.globalAlertSelector && firstMessage) {
        const $alert = $(options.globalAlertSelector);
        if (!$alert.hasClass('alert-danger')) {
            $alert.removeClass('d-none')
                .addClass('alert alert-danger')
                .html('<div>' + firstMessage + '</div>');
        }
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

            const del = config.delete || {};

            const i18n = del.i18n || {};

            Swal.fire({
                title: i18n.title || 'Are you sure?',
                text: i18n.text || 'This record will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: i18n.confirmButtonText || 'Yes, delete',
                cancelButtonText: i18n.cancelButtonText || 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: del.token,
                        },
                        success: function () {
                            Swal.fire(
                                i18n.successTitle || 'Deleted',
                                i18n.successText || 'The record has been deleted successfully.',
                                'success'
                            );
                            // إعادة تحميل الجدول
                            if (options.tableId && $.fn.DataTable.isDataTable('#' + options.tableId)) {
                                $('#' + options.tableId).DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function () {
                            Swal.fire(
                                i18n.errorTitle || 'Error',
                                i18n.errorText || 'An error occurred while deleting.',
                                'error'
                            );
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

