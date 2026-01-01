@props([
    'id' => 'attachments',
    'name' => 'attachments[]',
    'label' => 'مرفقات (صور / فيديو / PDF / Word)',
])

<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <input type="file"
           id="{{ $id }}"
           name="{{ $name }}"
           class="form-control js-attachments-input"
           multiple
           accept="image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
</div>
