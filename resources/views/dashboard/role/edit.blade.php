@extends('base.layout.app')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="mb-0">تعديل الدور: {{ $role->name }}</h3>
    <a href="{{ route('dashboard.roles.index') }}" class="btn btn-light">رجوع</a>
  </div>

  <form action="{{ route('dashboard.roles.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card-body">
      <div class="mb-5">
        <label class="form-label">اسم الدور</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $role->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      @foreach($permissions as $module => $list)
        <div class="mb-4 p-4 border rounded-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-bold">{{ t('module.'.$module) }}</div>
            <div>
              <label class="form-check form-check-sm form-check-custom">
                <input type="checkbox" class="form-check-input module-toggle"
                       data-module="{{ $module }}">
                <span class="form-check-label">تحديد الكل</span>
              </label>
            </div>
          </div>

          <div class="row g-3">
            @foreach($list as $perm)
              <div class="col-md-3">
                <label class="form-check form-check-sm form-check-custom">
                  <input class="form-check-input perm-checkbox"
                         type="checkbox"
                         name="permissions[]"
                         value="{{ $perm->name }}"
                         data-module="{{ $module }}"
                         {{ in_array($perm->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                  <span class="form-check-label">{{ t(key: 'roles.'.explode('.',$perm->name)[1]) }}</span>
                </label>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>

    <div class="card-footer d-flex gap-2">
      <button class="btn btn-primary">حفظ التعديل</button>
      <a href="{{ route('dashboard.roles.index') }}" class="btn btn-light">إلغاء</a>
    </div>
  </form>
</div>
@endsection

@push('custom-script')
<script>
  // تحديد/إلغاء تحديد صلاحيات الوحدة كاملة
  document.querySelectorAll('.module-toggle').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
      const module = this.dataset.module;
      document.querySelectorAll('.perm-checkbox[data-module="'+module+'"]').forEach(function (cb) {
        cb.checked = toggle.checked;
      });
    });
  });
</script>
@endpush
