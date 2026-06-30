@extends('layouts.dashboard')
@section('title', 'Configurações')
@section('page-title', 'Configurações')

@push('styles')
<style>
.settings-wrap { max-width: 600px; }

/* Section */
.cfg-section { background: #fff; border: 1px solid #e8e8e8; border-radius: 12px; margin-bottom: 16px; overflow: hidden; }
.cfg-section-head { padding: 18px 24px 0; }
.cfg-section-title { font-size: 15px; font-weight: 600; color: #111; }
.cfg-section-desc { font-size: 13px; color: #888; margin-top: 3px; }
.cfg-divider { height: 1px; background: #f0f0f0; margin: 18px 24px 0; }
.cfg-body { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 14px; }

/* Fields */
.field-group { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 13px; font-weight: 500; color: #444; }
.field-input {
  width: 100%; padding: 9px 12px;
  border: 1px solid #ddd; border-radius: 8px;
  font-size: 14px; font-family: inherit;
  background: #fafafa; outline: none; box-sizing: border-box;
  transition: border-color .15s, background .15s, box-shadow .15s;
}
.field-input:disabled { cursor: default; }
.field-input:not(:disabled) { color: #111; background: #fff; }
.field-input:not(:disabled):focus { border-color: #111; box-shadow: 0 0 0 3px rgba(0,0,0,.06); }
.field-col2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field-col3-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }
.field-col1-3 { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; }

/* Action bar */
.cfg-actions { padding: 0 24px 24px; display: flex; gap: 8px; }
.cfg-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
  cursor: pointer; border: none; transition: opacity .15s, transform .1s;
}
.cfg-btn:active { transform: scale(.98); }
.cfg-btn-primary { background: #111; color: #fff; flex: 1; justify-content: center; }
.cfg-btn-primary:hover { opacity: .85; }
.cfg-btn-ghost { background: #f4f4f4; color: #555; }
.cfg-btn-ghost:hover { background: #ebebeb; }
.cfg-btn-edit { background: #111; color: #fff; width: 100%; justify-content: center; padding: 11px 18px; }
.cfg-btn-edit:hover { opacity: .85; }

/* Bank empty state */
.bank-empty {
  display: flex; align-items: center; gap: 14px;
  padding: 18px 0; color: #999; font-size: 13px;
}
.bank-empty-icon {
  width: 36px; height: 36px; border-radius: 8px; background: #f4f4f4;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
</style>
@endpush

@section('content')
@php $bank = $provider?->bank_details ?? []; @endphp

{{-- Toast --}}
@if(session('success'))
<div id="success-toast" style="position:fixed;bottom:28px;right:28px;z-index:9999;background:#111;color:#fff;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.18);display:flex;align-items:center;gap:10px;max-width:340px;">
  <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="#22c55e"/><path d="M7 12.5l3.5 3.5 6.5-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
  {{ session('success') }}
</div>
<script>setTimeout(()=>{const t=document.getElementById('success-toast');if(t){t.style.transition='opacity .4s';t.style.opacity='0';setTimeout(()=>t.remove(),400);}},3500);</script>
@endif

@if($errors->any())
<div style="background:#fff5f5;border:1px solid #fca5a5;border-radius:10px;padding:14px 20px;margin-bottom:16px;max-width:600px;">
  @foreach($errors->all() as $error)
    <p style="font-size:13px;color:#c62828;margin:0 0 2px;">{{ $error }}</p>
  @endforeach
</div>
@endif

<div class="settings-wrap">

  {{-- Logo (separate form, submits via AJAX so main form state is unaffected) --}}
  <form id="logo-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="type" value="logo">
    <div class="cfg-section">
      <div class="cfg-section-head">
        <div class="cfg-section-title">Logo</div>
        <div class="cfg-section-desc">Aparece na sua página pública no envago.live</div>
      </div>
      <div class="cfg-divider"></div>
      <div class="cfg-body" style="flex-direction:row; align-items:center; gap:16px; flex-wrap:wrap;">
        <div id="logo-preview" style="width:64px;height:64px;border-radius:10px;border:1px solid #e8e8e8;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:#f7f7f7;">
          @if($provider?->logo_url)
            <img src="{{ $provider->logo_url }}" style="width:100%;height:100%;object-fit:cover;">
          @else
            <svg width="24" height="24" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          @endif
        </div>
        <div style="flex:1;min-width:200px;">
          <input type="file" name="logo" id="logo-input" accept="image/*" style="display:none;" onchange="onLogoSelected(this)">
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <button type="button" onclick="document.getElementById('logo-input').click()" class="cfg-btn cfg-btn-ghost" style="font-size:13px;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Escolher imagem
            </button>
            <button type="button" id="logo-save-btn" onclick="submitLogo()" class="cfg-btn cfg-btn-primary" style="display:none;flex:none;">
              Salvar logo
            </button>
            <button type="button" id="logo-cancel-btn" onclick="cancelLogo()" class="cfg-btn cfg-btn-ghost" style="display:none;">
              Cancelar
            </button>
          </div>
          <p id="logo-hint" style="font-size:12px;color:#aaa;margin-top:6px;">PNG ou JPG · máx. 2 MB</p>
          <p id="logo-filename" style="display:none;font-size:12px;color:#666;margin-top:6px;"></p>
          <p id="logo-status" style="display:none;font-size:12px;margin-top:6px;"></p>
        </div>
      </div>
    </div>
  </form>

  {{-- WhatsApp (own AJAX form) --}}
  <form id="wa-form">
    @csrf
    <input type="hidden" name="type" value="whatsapp">
    <div class="cfg-section">
      <div class="cfg-section-head" style="display:flex;align-items:flex-start;justify-content:space-between;">
        <div>
          <div class="cfg-section-title">Contato</div>
        </div>
        <button type="button" id="wa-edit-btn" onclick="enableWa()" class="cfg-btn cfg-btn-ghost" style="padding:6px 12px;font-size:12px;margin-top:2px;">
          <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Editar
        </button>
      </div>
      <div class="cfg-divider"></div>
      <div class="cfg-body">
        <div class="field-group">
          <label class="field-label">WhatsApp</label>
          <input type="tel" id="wa-input" name="whatsapp_number" value="{{ old('whatsapp_number', $provider?->whatsapp_number) }}"
            placeholder="(88) 99999-9999" disabled class="field-input">
        </div>
        <div id="wa-actions" style="display:none;flex-direction:row;gap:8px;">
          <button type="button" onclick="cancelWa()" class="cfg-btn cfg-btn-ghost">Cancelar</button>
          <button type="button" onclick="submitWa()" class="cfg-btn cfg-btn-primary">Salvar</button>
        </div>
        <p id="wa-status" style="display:none;font-size:12px;margin-top:2px;"></p>
      </div>
    </div>
  </form>

  {{-- Bank details form --}}
  <form method="POST" action="{{ route('provider.settings.update') }}" id="settings-form">
    @csrf @method('PUT')

    {{-- Bank details --}}
    <div class="cfg-section">
      <div class="cfg-section-head" style="display:flex;align-items:flex-start;justify-content:space-between;">
        <div>
          <div class="cfg-section-title">Dados bancários</div>
          <div class="cfg-section-desc">Para receber repasses via transferência bancária</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:2px;">
          @if(!empty($bank))
            <span class="badge badge-confirmed" style="font-size:11px;">✓ Cadastrado</span>
          @endif
          <button type="button" id="bank-edit-btn" onclick="enableEdit()" class="cfg-btn cfg-btn-ghost" style="padding:6px 12px;font-size:12px;">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Editar
          </button>
        </div>
      </div>
      <div class="cfg-divider"></div>

      {{-- Empty state shown when no bank data and not editing --}}
      @if(empty($bank))
      <div id="bank-empty" class="cfg-body" style="padding-bottom:20px;">
        <div class="bank-empty">
          <div class="bank-empty-icon">
            <svg width="18" height="18" fill="none" stroke="#bbb" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
          <span>Nenhum dado bancário cadastrado. Clique em <strong>Editar</strong> para adicionar.</span>
        </div>
      </div>
      @endif

      {{-- Fields — always in DOM, hidden when empty and not editing --}}
      <div id="bank-fields" class="cfg-body" @if(empty($bank)) style="display:none;" @endif>

        <div class="field-group">
          <label class="field-label">Nome do beneficiário</label>
          <input type="text" name="bank_beneficiary" value="{{ old('bank_beneficiary', $bank['beneficiary'] ?? '') }}"
            placeholder="Nome completo ou razão social" disabled class="field-input">
        </div>

        <div class="field-col2">
          <div class="field-group">
            <label class="field-label">CPF / CNPJ</label>
            <input type="text" name="bank_cpf_cnpj" value="{{ old('bank_cpf_cnpj', $bank['cpf_cnpj'] ?? '') }}"
              placeholder="000.000.000-00" disabled class="field-input">
          </div>
          <div class="field-group">
            <label class="field-label">Banco</label>
            <input type="text" name="bank_name" value="{{ old('bank_name', $bank['bank_name'] ?? '') }}"
              placeholder="ex: Itaú, Nubank" disabled class="field-input">
          </div>
        </div>

        <div class="field-group">
          <label class="field-label">Endereço</label>
          <input type="text" name="bank_address" value="{{ old('bank_address', $bank['address'] ?? '') }}"
            placeholder="ex: Rua das Dunas, 123" disabled class="field-input">
        </div>

        <div class="field-col3-1">
          <div class="field-group">
            <label class="field-label">Cidade</label>
            <input type="text" name="bank_city" value="{{ old('bank_city', $bank['city'] ?? '') }}"
              placeholder="ex: Jericoacoara" disabled class="field-input">
          </div>
          <div class="field-group">
            <label class="field-label">Estado (UF)</label>
            <input type="text" name="bank_state" value="{{ old('bank_state', $bank['state'] ?? '') }}"
              placeholder="CE" maxlength="2" disabled class="field-input">
          </div>
        </div>

        <div class="field-col2">
          <div class="field-group">
            <label class="field-label">CEP</label>
            <input type="text" name="bank_postcode" value="{{ old('bank_postcode', $bank['postcode'] ?? '') }}"
              placeholder="62598-000" disabled class="field-input">
          </div>
          <div class="field-group">
            <label class="field-label">Tipo de conta</label>
            <select name="bank_account_type" disabled class="field-input">
              <option value="">Selecionar...</option>
              <option value="corrente" {{ old('bank_account_type', $bank['account_type'] ?? '') === 'corrente' ? 'selected' : '' }}>Conta Corrente</option>
              <option value="poupanca" {{ old('bank_account_type', $bank['account_type'] ?? '') === 'poupanca' ? 'selected' : '' }}>Conta Poupança</option>
            </select>
          </div>
        </div>

        <div class="field-col2">
          <div class="field-group">
            <label class="field-label">SWIFT / BIC</label>
            <input type="text" name="bank_swift" value="{{ old('bank_swift', $bank['swift'] ?? '') }}"
              placeholder="ex: ITAUBRSP" disabled class="field-input" style="font-family:monospace;letter-spacing:.5px;">
          </div>
          <div class="field-group">
            <label class="field-label">Agência</label>
            <input type="text" name="bank_branch" value="{{ old('bank_branch', $bank['branch'] ?? '') }}"
              placeholder="ex: 0001" disabled class="field-input">
          </div>
        </div>

        <div class="field-col3-1">
          <div class="field-group">
            <label class="field-label">Número da conta</label>
            <input type="text" name="bank_account" value="{{ old('bank_account', $bank['account'] ?? '') }}"
              placeholder="ex: 12345-6" disabled class="field-input">
          </div>
          <div class="field-group">
            <label class="field-label">País</label>
            <input type="text" value="Brasil" disabled data-always-disabled class="field-input">
          </div>
        </div>

      </div>
    </div>

    {{-- Action bar — only visible when editing --}}
    <div id="bar-edit" class="cfg-actions" style="display:none;padding-left:0;padding-right:0;">
      <button type="button" onclick="cancelEdit()" class="cfg-btn cfg-btn-ghost">
        Cancelar
      </button>
      <button type="submit" class="cfg-btn cfg-btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Salvar alterações
      </button>
    </div>

  </form>
</div>

<script>
const originalValues = {};
const bankIsEmpty = {{ empty($bank) ? 'true' : 'false' }};

// ── Logo (AJAX, no page reload) ────────────────────────────
function onLogoSelected(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('logo-preview').innerHTML =
      '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
  };
  reader.readAsDataURL(input.files[0]);
  document.getElementById('logo-filename').textContent = input.files[0].name;
  document.getElementById('logo-filename').style.display = 'block';
  document.getElementById('logo-hint').style.display = 'none';
  document.getElementById('logo-save-btn').style.display = 'inline-flex';
  document.getElementById('logo-cancel-btn').style.display = 'inline-flex';
  document.getElementById('logo-status').style.display = 'none';
}

function cancelLogo() {
  document.getElementById('logo-input').value = '';
  document.getElementById('logo-save-btn').style.display = 'none';
  document.getElementById('logo-cancel-btn').style.display = 'none';
  document.getElementById('logo-filename').style.display = 'none';
  document.getElementById('logo-status').style.display = 'none';
  document.getElementById('logo-hint').style.display = 'block';
  @if($provider?->logo_url)
  document.getElementById('logo-preview').innerHTML =
    '<img src="{{ $provider->logo_url }}" style="width:100%;height:100%;object-fit:cover;">';
  @else
  document.getElementById('logo-preview').innerHTML =
    '<svg width="24" height="24" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
  @endif
}

async function submitLogo() {
  const btn = document.getElementById('logo-save-btn');
  const status = document.getElementById('logo-status');
  btn.disabled = true;
  btn.textContent = 'Salvando…';

  const form = document.getElementById('logo-form');
  const data = new FormData(form);
  data.append('_method', 'PUT');

  try {
    const res = await fetch('{{ route('provider.settings.update') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('#logo-form [name=_token]').value },
      body: data,
    });
    if (res.ok) {
      status.textContent = '✓ Logo salvo';
      status.style.color = '#22c55e';
      status.style.display = 'block';
      document.getElementById('logo-save-btn').style.display = 'none';
      document.getElementById('logo-cancel-btn').style.display = 'none';
      document.getElementById('logo-filename').style.display = 'none';
      document.getElementById('logo-hint').style.display = 'block';
      document.getElementById('logo-input').value = '';
    } else {
      throw new Error();
    }
  } catch {
    status.textContent = 'Erro ao salvar. Tente novamente.';
    status.style.color = '#c62828';
    status.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.textContent = 'Salvar logo';
  }
}

// ── WhatsApp ───────────────────────────────────────────────
let waOriginal = '';

function enableWa() {
  waOriginal = document.getElementById('wa-input').value;
  document.getElementById('wa-input').disabled = false;
  document.getElementById('wa-input').focus();
  document.getElementById('wa-edit-btn').style.display = 'none';
  document.getElementById('wa-actions').style.display = 'flex';
  document.getElementById('wa-status').style.display = 'none';
}

function cancelWa() {
  document.getElementById('wa-input').value = waOriginal;
  document.getElementById('wa-input').disabled = true;
  document.getElementById('wa-edit-btn').style.display = 'inline-flex';
  document.getElementById('wa-actions').style.display = 'none';
  document.getElementById('wa-status').style.display = 'none';
}

async function submitWa() {
  const btn = document.querySelector('#wa-actions .cfg-btn-primary');
  const status = document.getElementById('wa-status');
  btn.disabled = true; btn.textContent = 'Salvando…';

  const data = new FormData(document.getElementById('wa-form'));
  data.append('_method', 'PUT');

  try {
    const res = await fetch('{{ route('provider.settings.update') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('#wa-form [name=_token]').value },
      body: data,
    });
    if (res.ok) {
      status.textContent = '✓ Salvo';
      status.style.color = '#22c55e';
      status.style.display = 'block';
      document.getElementById('wa-input').disabled = true;
      document.getElementById('wa-edit-btn').style.display = 'inline-flex';
      document.getElementById('wa-actions').style.display = 'none';
    } else { throw new Error(); }
  } catch {
    status.textContent = 'Erro ao salvar.';
    status.style.color = '#c62828';
    status.style.display = 'block';
  } finally {
    btn.disabled = false; btn.textContent = 'Salvar';
  }
}

// ── Bank details edit/cancel ───────────────────────────────
function enableEdit() {
  document.querySelectorAll('#settings-form .field-input').forEach(el => {
    const key = el.getAttribute('name');
    if (key) originalValues[key] = el.value;
    if (!el.dataset.alwaysDisabled) el.disabled = false;
  });
  if (bankIsEmpty) {
    document.getElementById('bank-empty').style.display = 'none';
    document.getElementById('bank-fields').style.display = 'flex';
  }
  document.getElementById('bank-edit-btn').style.display = 'none';
  document.getElementById('bar-edit').style.display = 'flex';
}

function cancelEdit() {
  document.querySelectorAll('#settings-form .field-input').forEach(el => {
    const key = el.getAttribute('name');
    if (key && originalValues[key] !== undefined) el.value = originalValues[key];
    if (!el.dataset.alwaysDisabled) el.disabled = true;
  });
  if (bankIsEmpty) {
    document.getElementById('bank-empty').style.display = 'flex';
    document.getElementById('bank-fields').style.display = 'none';
  }
  document.getElementById('bank-edit-btn').style.display = 'inline-flex';
  document.getElementById('bar-edit').style.display = 'none';
}

@if($errors->any()) enableEdit(); @endif
</script>

@endsection
