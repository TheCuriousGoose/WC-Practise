@props(['id', 'name', 'label', 'value' => 1, 'checked' => false])

<div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="{{ $name }}" id="{{ $id }}"
            value="{{ $value }}" {{ old($name) || $checked ? 'checked' : '' }} {{ $attributes }}>
        <label class="form-check-label" for="{{ $id }}">
            {{ $label }}
        </label>

    </div>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
