@props(['id', 'name', 'label', 'value' => '', 'extras' => ''])

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <input type="number" class="form-control" id="{{ $id }}" name="{{ $name }}"
        value="{{ old($name) ?? $value }}" {{ $extras }}>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
