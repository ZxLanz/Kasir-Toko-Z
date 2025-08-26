@props(['name', 'value' => '', 'options' => []])

@php
    $val = old($name, $value);
@endphp

<select name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
    @foreach ($options as $key => $label)
        <option value="{{ is_array($label) ? $label[0] : $key }}" {{ $val == (is_array($label) ? $label[0] : $key) ? 'selected' : '' }}>
            {{ is_array($label) ? $label[1] : $label }}
        </option>
    @endforeach
</select>

@error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror
