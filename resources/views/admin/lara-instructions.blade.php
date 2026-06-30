@extends('layouts.dashboard')
@section('title', 'Instruções Lara')
@section('page-title', 'Instruções Lara')

@section('content')

@if(session('success'))
<div style="background:#e8f5e9;border:1px solid #a5d6a7;color:#2e7d32;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:13px;">
  {{ session('success') }}
</div>
@endif

{{-- Add instruction --}}
<div class="section" style="margin-bottom:24px;">
  <div class="section-header"><span class="section-title">Nova instrução</span></div>
  <div style="padding:20px 24px;">
    <form method="POST" action="{{ route('admin.lara.instructions.store') }}">
      @csrf
      <div style="display:grid;grid-template-columns:140px 1fr auto;gap:12px;align-items:flex-start;">
        <div>
          <label style="font-size:12px;color:#888;font-weight:500;display:block;margin-bottom:6px;">Contexto</label>
          <select name="context" style="width:100%;padding:9px 10px;border:1px solid #ddd;border-radius:8px;font-size:14px;font-family:inherit;background:#fff;outline:none;">
            <option value="provider">Operador</option>
            <option value="tourist">Turista</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;color:#888;font-weight:500;display:block;margin-bottom:6px;">Instrução</label>
          <textarea name="instruction" rows="2" placeholder="Ex: Quando o operador perguntar sobre exclusividade, diga que trabalhamos com múltiplos operadores na mesma cidade..." style="width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;outline:none;" required></textarea>
        </div>
        <div style="padding-top:26px;">
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </div>
      @error('instruction')<p style="color:#c62828;font-size:12px;margin-top:6px;">{{ $message }}</p>@enderror
    </form>
  </div>
</div>

{{-- Instructions list --}}
<div class="section">
  <div class="section-header">
    <span class="section-title">Instruções ativas</span>
    <span style="font-size:13px;color:#aaa;">{{ $instructions->count() }} total</span>
  </div>
  @if($instructions->isEmpty())
    <div class="empty"><div class="empty-icon">📋</div><p>Nenhuma instrução ainda.</p></div>
  @else
  <table>
    <thead>
      <tr>
        <th style="width:100px;">Contexto</th>
        <th>Instrução</th>
        <th style="width:80px;">Status</th>
        <th style="width:80px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($instructions as $inst)
      <tr style="{{ $inst->active ? '' : 'opacity:.45;' }}">
        <td>
          <span class="badge {{ $inst->context === 'provider' ? 'badge-approved' : 'badge-pending' }}">
            {{ $inst->context === 'provider' ? 'Operador' : 'Turista' }}
          </span>
        </td>
        <td style="font-size:13px;line-height:1.5;">{{ $inst->instruction }}</td>
        <td>
          <form method="POST" action="{{ route('admin.lara.instructions.toggle', $inst) }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="font-size:12px;padding:4px 10px;">
              {{ $inst->active ? 'Desativar' : 'Ativar' }}
            </button>
          </form>
        </td>
        <td>
          <form method="POST" action="{{ route('admin.lara.instructions.destroy', $inst) }}" onsubmit="return confirm('Remover esta instrução?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger" style="font-size:12px;padding:4px 10px;">Remover</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif
</div>

@endsection
