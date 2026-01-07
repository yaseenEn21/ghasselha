@extends('base.layout.app')

@section('content')

@section('top-btns')
    <a href="{{ route('dashboard.customer-groups.index') }}" class="btn btn-light">
        {{ __('customer_groups.back_to_list') }}
    </a>
@endsection

<div class="card">
    <form id="group_form" action="{{ route('dashboard.customer-groups.update', $group->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div id="form_result" class="alert d-none"></div>

            <div class="row g-9">
                <div class="col-lg-8">

                    <div class="card mb-7">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">{{ __('customer_groups.basic_data') }}</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ __('customer_groups.basic_data_hint') }}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('customer_groups.fields.name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $group->name }}"
                                       placeholder="{{ __('customer_groups.placeholders.name') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <label class="fw-semibold fs-6 mb-2 d-block">{{ __('customer_groups.fields.status') }}</label>
                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $group->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">{{ __('customer_groups.active') }}</label>
                            </div>
                            <div class="invalid-feedback d-block"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('customer_groups.save_changes') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('custom-script')
<script>
(function () {
    const isAr = document.documentElement.lang === 'ar';
    const $form = $('#group_form');

    $form.on('submit', function(e) {
        e.preventDefault();

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
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('customer_groups.done') }}",
                    text: res.message || "{{ __('customer_groups.updated_successfully') }}",
                    timer: 2000,
                    showConfirmButton: false
                });

                if (res.redirect) window.location.href = res.redirect;
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    if (window.KH && typeof window.KH.showValidationErrors === 'function') {
                        window.KH.showValidationErrors($form, xhr.responseJSON.errors, { globalAlertSelector: '#form_result' });
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Unexpected error', 'error');
                }
            },
            complete: function() {
                if (window.KH && typeof window.KH.setFormLoading === 'function') {
                    window.KH.setFormLoading($form, false);
                }
            }
        });
    });

})();
</script>
@endpush