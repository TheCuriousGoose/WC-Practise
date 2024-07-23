@props(['id', 'name', 'label', 'options' => [], 'selected' => null])

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <select class="form-select" id="{{ $id }}" name="{{ $name }}" {{ $attributes }}>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" {{ $value == $selected ? 'selected' : '' }}>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
