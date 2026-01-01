@extends('base.layout.app')

@section('content')
<div class="card">
  <form action="{{ route('dashboard.users.store') }}" method="POST" class="needs-validation" novalidate>
    @csrf
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label">الاسم</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">البريد الإلكتروني</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">الجوال</label>
          <input type="text" name="mobile" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">كلمة المرور</label>
          <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">الدور</label>
          <select name="role_id" class="form-select" required>
            <option value="" disabled selected>اختر الدور</option>
            @foreach($roles as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex gap-2">
      <button class="btn btn-primary">حفظ</button>
      <a href="{{ route('dashboard.users.index') }}" class="btn btn-light">إلغاء</a>
    </div>
  </form>
</div>
@endsection
