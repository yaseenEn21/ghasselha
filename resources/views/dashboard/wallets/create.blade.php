@extends('base.layout.app')

@section('title', __('wallets.manage_wallet'))

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.wallets.index') }}" class="btn btn-light">
        {{ __('wallets.title') }}
    </a>
@endsection

<form id="wallet_manage_form" action="{{ route('dashboard.wallets.store') }}" method="POST">
    @csrf

    <div class="row g-6">

        <div class="col-xl-8">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">{{ __('wallets.manage_wallet') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('wallets.manage_hint') }}</span>
                    </h3>
                </div>

                <div class="card-body pt-0">
                    <div id="form_result" class="alert d-none mb-6"></div>

                    <div class="row g-6">

                        <div class="col-md-6 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('wallets.fields.user') }}</label>

                            <select id="user_id"
                                    name="user_id"
                                    class="form-select js-user-select"
                                    data-users-ajax="{{ route('dashboard.users.select2') }}"
                                    data-placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث بالاسم أو الجوال...' : 'Search by name or mobile...' }}"
                                    data-selected-id="{{ old('user_id') }}"
                                    data-selected-text="{{ old('user_id') ? (old('user_name') ?? '') : '' }}">
                            </select>

                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('wallets.fields.direction') }}</label>
                            <select name="direction" id="direction" class="form-select" data-control="select2">
                                <option value="credit" {{ old('direction','credit') === 'credit' ? 'selected' : '' }}>
                                    {{ __('wallets.directions.credit') }}
                                </option>
                                <option value="debit" {{ old('direction') === 'debit' ? 'selected' : '' }}>
                                    {{ __('wallets.directions.debit') }}
                                </option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-3 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('wallets.fields.type') }}</label>

                            <select name="type" id="type" class="form-select" data-control="select2">
                                {{-- سيتم تحديثها حسب direction --}}
                            </select>

                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('wallets.fields.amount') }}</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control"
                                   value="{{ old('amount', 1) }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('wallets.fields.description_ar') }}</label>
                            <input type="text" name="description_ar" class="form-control"
                                   value="{{ old('description_ar') }}"
                                   placeholder="{{ __('wallets.placeholders.description_ar') }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="fw-semibold fs-6 mb-2">{{ __('wallets.fields.description_en') }}</label>
                            <input type="text" name="description_en" class="form-control"
                                   value="{{ old('description_en') }}"
                                   placeholder="{{ __('wallets.placeholders.description_en') }}">
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
                        <span class="card-label fw-bold fs-4 mb-1">{{ __('wallets.wallet_snapshot') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('wallets.wallet_snapshot_hint') }}</span>
                    </h3>
                </div>

                <div class="card-body pt-0">
                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('wallets.wallet.balance') }}</div>
                        <div class="fw-bold fs-3" id="wallet_balance">—</div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('wallets.wallet.total_credit') }}</div>
                        <div class="fw-semibold" id="wallet_total_credit">—</div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('wallets.wallet.total_debit') }}</div>
                        <div class="fw-semibold" id="wallet_total_debit">—</div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted fs-8 mb-1">{{ __('wallets.wallet.currency') }}</div>
                        <div class="fw-semibold" id="wallet_currency">—</div>
                    </div>

                    <div class="text-muted fs-7">
                        {{ __('wallets.wallet_note') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-end mt-6">
        <button type="submit" class="btn btn-primary">
            <span class="indicator-label">{{ __('wallets.submit') }}</span>
        </button>
    </div>
</form>

@endsection

@push('custom-script')
    <script>
        (function () {
            const isAr = '{{ app()->getLocale() }}' === 'ar';

            const TYPE_OPTIONS = {
                credit: [
                    {id: 'topup', text: '{{ __('wallets.types.topup') }}'},
                    {id: 'refund', text: '{{ __('wallets.types.refund') }}'},
                    {id: 'adjustment', text: '{{ __('wallets.types.adjustment') }}'},
                ],
                debit: [
                    {id: 'booking_charge', text: '{{ __('wallets.types.booking_charge') }}'},
                    {id: 'package_purchase', text: '{{ __('wallets.types.package_purchase') }}'},
                    {id: 'adjustment', text: '{{ __('wallets.types.adjustment') }}'},
                ],
            };

            function fillTypes(direction) {
                const $type = $('#type');
                const current = $type.val();
                $type.empty();

                (TYPE_OPTIONS[direction] || []).forEach(opt => {
                    const option = new Option(opt.text, opt.id, false, false);
                    $type.append(option);
                });

                // حاول حافظ على القيمة إن كانت موجودة
                if (current && $type.find('option[value="'+current+'"]').length) {
                    $type.val(current).trigger('change');
                } else {
                    $type.val((TYPE_OPTIONS[direction]?.[0]?.id) || null).trigger('change');
                }
            }

            function setWalletUI(w) {
                $('#wallet_balance').text(w?.balance ?? '—');
                $('#wallet_total_credit').text(w?.total_credit ?? '—');
                $('#wallet_total_debit').text(w?.total_debit ?? '—');
                $('#wallet_currency').text(w?.currency ?? '—');
            }

            function initUserSelect2() {
                const $el = $('.js-user-select');
                const url = $el.data('users-ajax');

                $el.select2({
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
                });

                // old selected
                const selectedId = $el.data('selected-id');
                const selectedText = $el.data('selected-text');
                if (selectedId && selectedText) {
                    const option = new Option(selectedText, selectedId, true, true);
                    $el.append(option).trigger('change');
                }

                $el.on('change', function () {
                    const userId = $(this).val();
                    if (!userId) return setWalletUI(null);

                    $.get('{{ url('/dashboard/wallets/wallet-info') }}/' + userId)
                        .done(res => setWalletUI(res))
                        .fail(() => setWalletUI(null));
                });
            }

            $(document).ready(function () {
                initUserSelect2();

                // init types
                fillTypes($('#direction').val() || 'credit');

                $('#direction').on('change', function () {
                    fillTypes($(this).val());
                });

                // submit ajax
                $('#wallet_manage_form').on('submit', function (e) {
                    e.preventDefault();

                    const $form = $(this);
                    const formData = new FormData($form[0]);

                    if (window.KH && typeof window.KH.setFormLoading === 'function') {
                        window.KH.setFormLoading($form, true, { text: isAr ? 'جاري الحفظ...' : 'Saving...' });
                    }

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: isAr ? 'تم' : 'Done',
                                text: res.message || '{{ __('wallets.created_successfully') }}',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            if (res.redirect) window.location.href = res.redirect;
                        },
                        error: function (xhr) {
                            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                                    window.KH.showValidationErrors($form, xhr.responseJSON.errors, {
                                        globalAlertSelector: '#form_result'
                                    });
                                }
                            } else {
                                let msg = isAr ? 'حدث خطأ غير متوقع.' : 'Unexpected error occurred.';
                                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                                Swal.fire(isAr ? 'خطأ' : 'Error', msg, 'error');
                            }
                        },
                        complete: function () {
                            if (window.KH && typeof window.KH.setFormLoading === 'function') {
                                window.KH.setFormLoading($form, false);
                            }
                        }
                    });
                });
            });
        })();
    </script>
@endpush