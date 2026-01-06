@extends('base.layout.app')

@section('title', __('points.manage_wallet'))

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.points.index') }}" class="btn btn-light">
        {{ __('points.title') }}
    </a>
@endsection

<form id="points_manage_form" action="{{ route('dashboard.points.store') }}" method="POST">
    @csrf

    <div class="row g-6">

        <div class="col-xl-8">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">{{ __('points.manage_wallet') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('points.manage_hint') }}</span>
                    </h3>
                </div>

                <div class="card-body pt-0">

                    <div id="form_result" class="alert d-none mb-6"></div>

                    <div class="row g-6">

                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('points.fields.user') }}</label>

                            <select id="user_id" name="user_id" class="form-select" data-control="select2"
                                data-placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث بالاسم أو الجوال...' : 'Search by name or mobile...' }}"
                                data-users-ajax="{{ route('dashboard.users.select2') }}"
                                data-selected-id="{{ old('user_id') }}"
                                data-selected-text="{{ old('user_id') ? old('user_name') ?? '' : '' }}">
                            </select>

                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('points.fields.action') }}</label>
                            <select name="action" class="form-select">
                                <option value="add">{{ __('points.actions.add') }}</option>
                                <option value="subtract">{{ __('points.actions.subtract') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label
                                class="required fw-semibold fs-6 mb-2">{{ __('points.fields.points_amount') }}</label>
                            <input type="number" min="1" name="points_amount" class="form-control"
                                value="{{ old('points_amount', 1) }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('points.fields.note') }}</label>
                            <textarea name="note" rows="3" class="form-control" placeholder="{{ __('points.placeholders.note') }}">{{ old('note') }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 mb-1">{{ __('points.wallet_snapshot') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('points.wallet_snapshot_hint') }}</span>
                    </h3>
                </div>

                <div class="card-body pt-0">
                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('points.wallet.balance') }}</div>
                        <div class="fw-bold fs-3" id="wallet_balance">—</div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('points.wallet.total_earned') }}</div>
                        <div class="fw-semibold" id="wallet_earned">—</div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('points.wallet.total_spent') }}</div>
                        <div class="fw-semibold" id="wallet_spent">—</div>
                    </div>

                    <div class="text-muted fs-7">
                        {{ __('points.wallet_note') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-end mt-6">
        <button type="submit" class="btn btn-primary">
            <span class="indicator-label">{{ __('points.submit') }}</span>
        </button>
    </div>
</form>

@endsection

@push('custom-script')
<script>
    (function() {
        const $form = $('#points_manage_form');

        function initUsersSelect2() {
            const isAr = document.documentElement.lang === 'ar';

            $('[data-users-ajax]').each(function() {
                const $el = $(this);
                const url = $el.data('users-ajax');

                // إذا داخل modal/offcanvas لازم dropdownParent
                const $parent = $el.closest('.modal, .offcanvas');
                const opts = {
                    width: '100%',
                    dir: isAr ? 'rtl' : 'ltr',
                    placeholder: $el.data('placeholder') || '',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term || '', page: params.page || 1, per_page: 10 };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return { results: data.results || [], pagination: { more: !!data.more } };
                        },
                        cache: true
                    }
                };

                if ($parent.length) {
                    opts.dropdownParent = $parent;
                }

                $el.select2(opts);

                // ✅ دعم القيمة القديمة (لو في old user_id)
                const selectedId = $el.data('selected-id');
                const selectedText = $el.data('selected-text');

                if (selectedId && selectedText) {
                    const option = new Option(selectedText, selectedId, true, true);
                    $el.append(option).trigger('change');
                }
            });
        }

        function setWalletUI(w) {
            $('#wallet_balance').text(w.balance_points ?? 0);
            $('#wallet_earned').text(w.total_earned_points ?? 0);
            $('#wallet_spent').text(w.total_spent_points ?? 0);
        }

        $('#user_id').on('change', function() {
            const userId = $(this).val();
            if (!userId) return setWalletUI({
                balance_points: '—',
                total_earned_points: '—',
                total_spent_points: '—'
            });

            $.get('{{ url('/dashboard/points/wallet-info') }}/' + userId)
                .done(function(res) {
                    setWalletUI(res);
                })
                .fail(function() {
                    setWalletUI({
                        balance_points: '—',
                        total_earned_points: '—',
                        total_spent_points: '—'
                    });
                });
        });

        // AJAX submit
        $form.on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData($form[0]);

            if (window.KH && typeof window.KH.setFormLoading === 'function') {
                window.KH.setFormLoading($form, true, {
                    text: '{{ app()->getLocale() === 'ar' ? 'جاري الحفظ...' : 'Saving...' }}'
                });
            }

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ app()->getLocale() === 'ar' ? 'تم' : 'Done' }}',
                        text: res.message || '{{ __('points.created_successfully') }}',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (res.redirect) window.location.href = res.redirect;
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                            window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                                globalAlertSelector: '#form_result'
                            });
                        }
                    } else {
                        let msg =
                            '{{ app()->getLocale() === 'ar' ? 'حدث خطأ غير متوقع.' : 'Unexpected error occurred.' }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                            .message;

                        Swal.fire(
                            '{{ app()->getLocale() === 'ar' ? 'خطأ' : 'Error' }}',
                            msg,
                            'error'
                        );
                    }
                },
                complete: function() {
                    if (window.KH && typeof window.KH.setFormLoading === 'function') {
                        window.KH.setFormLoading($form, false);
                    }
                }
            });
        });

        $(document).ready(initUsersSelect2);

    })();
</script>
@endpush
