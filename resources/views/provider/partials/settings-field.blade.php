@php $wrap = $wrap ?? true; $mono = $mono ?? false; $maxlength = $maxlength ?? ''; @endphp
@if($wrap)<div>@endif
<label class="field-label">{{ $label }}</label>
<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
  disabled class="field-input field-locked"
  @if($maxlength) maxlength="{{ $maxlength }}" @endif
  @if($mono) style="font-family:monospace;" @endif>
@if($wrap)</div>@endif
