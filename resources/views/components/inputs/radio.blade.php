@props(['id', 'name', 'label', 'options' => [], 'selected' => null])

<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    @foreach ($options as $value => $text)
        <div class="form-check">
            <input class="form-check-input" type="radio" name="{{ $name }}"
                id="{{ $id }}-{{ $value }}" value="{{ $value }}"
                {{ $value == $selected ? 'checked' : '' }} {{ $attributes }}>
            <label class="form-check-label" for="{{ $id }}-{{ $value }}">
                {{ $text }}
            </label>
        </div>
    @endforeach
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
