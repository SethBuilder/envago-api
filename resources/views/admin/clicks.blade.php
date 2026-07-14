@extends('layouts.dashboard')
@section('title', 'Leads & Cliques')
@section('page-title', 'Leads & Cliques')

@push('styles')
<style>
  .badge-lead      { background: #e8f5e9; color: #2e7d32; }
  .badge-affiliate { background: #f0f0f0; color: #777; }
</style>
@endpush

@section('content')

<div class="section">
  <div class="section-header">
    <span class="section-title">Todos os cliques</span>
    <span style="font-size:12px; color:#aaa;">{{ $clicks->total() }} registro(s)</span>
  </div>

  @if($clicks->isEmpty())
    <div class="empty">
      <div class="empty-icon">📭</div>
      <p>Nenhum clique registrado ainda.</p>
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Data</th>
          <th>Passeio</th>
          <th>Tipo</th>
          <th>Nome</th>
          <th>Email</th>
          <th>WhatsApp</th>
        </tr>
      </thead>
      <tbody>
        @foreach($clicks as $click)
        <tr>
          <td style="color:#888; font-size:13px; white-space:nowrap;">
            {{ $click->created_at->format('d/m/Y H:i') }}
          </td>
          <td style="font-weight:500;">
            @if($click->activity)
              <a href="{{ route('admin.providers.show', $click->activity->provider_id) }}"
                 style="color:#111; text-decoration:none;">
                {{ $click->activity->name }}
              </a>
            @else
              <span style="color:#aaa;">—</span>
            @endif
          </td>
          <td>
            <span class="badge badge-{{ $click->type }}">
              {{ $click->type === 'lead' ? 'Lead' : 'Afiliado' }}
            </span>
          </td>
          <td style="font-size:13px;">{{ $click->name ?: '—' }}</td>
          <td style="font-size:13px; color:#555;">{{ $click->email ?: '—' }}</td>
          <td style="font-size:13px;">
            @if($click->whatsapp)
              <a href="https://wa.me/{{ preg_replace('/\D/', '', $click->whatsapp) }}"
                 target="_blank"
                 style="color:#25D366; text-decoration:none; font-weight:500;">
                {{ $click->whatsapp }}
              </a>
            @else
              <span style="color:#aaa;">—</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    @if($clicks->hasPages())
      <div style="padding:16px 24px; border-top:1px solid #f0ede8; display:flex; gap:8px; align-items:center;">
        @if($clicks->onFirstPage())
          <span class="btn btn-ghost" style="opacity:.4; cursor:default;">← Anterior</span>
        @else
          <a href="{{ $clicks->previousPageUrl() }}" class="btn btn-ghost">← Anterior</a>
        @endif
        <span style="font-size:13px; color:#aaa; flex:1; text-align:center;">
          Página {{ $clicks->currentPage() }} de {{ $clicks->lastPage() }}
        </span>
        @if($clicks->hasMorePages())
          <a href="{{ $clicks->nextPageUrl() }}" class="btn btn-ghost">Próxima →</a>
        @else
          <span class="btn btn-ghost" style="opacity:.4; cursor:default;">Próxima →</span>
        @endif
      </div>
    @endif
  @endif
</div>

@endsection
