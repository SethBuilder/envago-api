@extends('layouts.dashboard')
@section('title', 'Configurações')
@section('page-title', 'Configurações')

@section('content')

@if(session('success'))
<div style="background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px; padding:14px 20px; margin-bottom:24px; font-size:14px; color:#2e7d32; font-weight:500;">
  {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:#fff5f5; border:1px solid #ffcdd2; border-radius:10px; padding:14px 20px; margin-bottom:24px;">
  <p style="font-size:13px; font-weight:600; color:#c62828; margin:0 0 8px;">Por favor corrija os erros abaixo:</p>
  <ul style="margin:0; padding-left:18px;">
    @foreach($errors->all() as $error)
      <li style="font-size:13px; color:#c62828; margin-bottom:2px;">{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ route('provider.settings.update') }}" style="max-width:560px;">
  @csrf
  @method('PUT')

  {{-- WhatsApp --}}
  <div class="section" style="margin-bottom:20px;">
    <div class="section-header"><span class="section-title">Contato</span></div>
    <div style="padding:24px;">
      <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:8px;">WhatsApp</label>
      <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $provider?->whatsapp_number) }}"
        placeholder="+55 88 99999-9999"
        style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
    </div>
  </div>

  {{-- Bank details for SWIFT payout --}}
  @php $bank = $provider?->bank_details ?? []; @endphp
  <div class="section" style="margin-bottom:20px;">
    <div class="section-header">
      <span class="section-title">Dados bancários para recebimento</span>
      @if(!empty($bank))
        <span class="badge badge-confirmed" style="font-size:11px;">✓ Cadastrado</span>
      @else
        <span class="badge badge-pending" style="font-size:11px;">Pendente</span>
      @endif
    </div>
    <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">
      <p style="font-size:13px; color:#888; margin:0;">Esses dados são usados para transferência bancária internacional (SWIFT). Preenchimento obrigatório para receber repasses.</p>

      @php
        $field = fn($label, $name, $placeholder) => "
          <div>
            <label style='display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;'>$label</label>
            <input type='text' name='$name' value='" . old($name, $bank[str_replace('bank_','',$name)] ?? '') . "'
              placeholder='$placeholder'
              style='width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;'>
          </div>
        ";
      @endphp

      <div>
        <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Nome do beneficiário</label>
        <input type="text" name="bank_beneficiary" value="{{ old('bank_beneficiary', $bank['beneficiary'] ?? '') }}"
          placeholder="Nome completo ou razão social"
          style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
      </div>

      <div>
        <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">CPF / CNPJ</label>
        <input type="text" name="bank_cpf_cnpj" value="{{ old('bank_cpf_cnpj', $bank['cpf_cnpj'] ?? '') }}"
          placeholder="000.000.000-00"
          style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
      </div>

      <div>
        <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Banco</label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $bank['bank_name'] ?? '') }}"
          placeholder="ex: Itaú, Nubank, Banco do Brasil"
          style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
      </div>

      {{-- Address --}}
      <div>
        <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Endereço</label>
        <input type="text" name="bank_address" value="{{ old('bank_address', $bank['address'] ?? '') }}"
          placeholder="ex: Rua das Dunas, 123"
          style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
      </div>

      <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Cidade</label>
          <input type="text" name="bank_city" value="{{ old('bank_city', $bank['city'] ?? '') }}"
            placeholder="ex: Jericoacoara"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Estado (UF)</label>
          <input type="text" name="bank_state" value="{{ old('bank_state', $bank['state'] ?? '') }}"
            placeholder="ex: CE"
            maxlength="2"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box; text-transform:uppercase;">
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">CEP</label>
          <input type="text" name="bank_postcode" value="{{ old('bank_postcode', $bank['postcode'] ?? '') }}"
            placeholder="ex: 62598-000"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Tipo de conta</label>
          <select name="bank_account_type"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box; background:#fff;">
            <option value="">Selecionar...</option>
            <option value="corrente" {{ old('bank_account_type', $bank['account_type'] ?? '') === 'corrente' ? 'selected' : '' }}>Conta Corrente</option>
            <option value="poupanca" {{ old('bank_account_type', $bank['account_type'] ?? '') === 'poupanca' ? 'selected' : '' }}>Conta Poupança</option>
          </select>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">SWIFT / BIC</label>
          <input type="text" name="bank_swift" value="{{ old('bank_swift', $bank['swift'] ?? '') }}"
            placeholder="ex: ITAUBRSP"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:monospace; outline:none; box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Agência</label>
          <input type="text" name="bank_branch" value="{{ old('bank_branch', $bank['branch'] ?? '') }}"
            placeholder="ex: 0001"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
        </div>
      </div>

      <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">Número da conta</label>
          <input type="text" name="bank_account" value="{{ old('bank_account', $bank['account'] ?? '') }}"
            placeholder="ex: 12345-6"
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.7px; margin-bottom:6px;">País</label>
          <input type="text" value="Brasil" disabled
            style="width:100%; padding:10px 14px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; background:#f9f9f9; color:#aaa; box-sizing:border-box;">
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary">Salvar configurações</button>
</form>

@endsection
