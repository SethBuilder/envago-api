@extends('layouts.dashboard')
@section('title', 'Fora do Escopo')
@section('page-title', 'Fora do Escopo')

@push('styles')
<style>
  .leads-grid { display:grid; gap:16px; }
  .lead-card {
    background:#fff;border:1px solid #ece9e4;border-radius:12px;padding:20px 24px;
  }
  .lead-offering {
    font-size:17px;font-weight:600;color:#111;margin-bottom:6px;
  }
  .lead-meta {
    display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;
  }
  .lead-chip {
    font-size:12px;color:#666;background:#f5f5f3;border-radius:20px;padding:3px 10px;
  }
  .lead-summary {
    font-size:13px;color:#555;line-height:1.6;border-top:1px solid #f0ede8;padding-top:12px;margin-top:4px;
  }
  .lead-wa { font-size:13px;color:#22c55e;font-weight:500; }
</style>
@endpush

@section('content')

<div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
  <p style="font-size:14px;color:#888;">Operadores que entraram em contato mas oferecem serviços fora do escopo atual do Envago. Use para decisões de expansão.</p>
  <span style="font-size:13px;color:#aaa;">{{ $leads->total() }} entradas</span>
</div>

@if($leads->isEmpty())
  <div class="section">
    <div class="empty"><div class="empty-icon">🗺️</div><p>Nenhum lead fora do escopo ainda.</p></div>
  </div>
@else
<div class="leads-grid">
  @foreach($leads as $lead)
  <div class="lead-card">
    <div class="lead-offering">{{ $lead->offering }}</div>
    <div class="lead-meta">
      @if($lead->business_name)
        <span class="lead-chip">{{ $lead->business_name }}</span>
      @endif
      @if($lead->location)
        <span class="lead-chip">📍 {{ $lead->location }}</span>
      @endif
      @if($lead->price_range)
        <span class="lead-chip">💰 {{ $lead->price_range }}</span>
      @endif
      @if($lead->whatsapp)
        <span class="lead-chip lead-wa">{{ $lead->whatsapp }}</span>
      @endif
      <span class="lead-chip" style="color:#bbb;">{{ $lead->created_at->format('d/m/Y') }}</span>
    </div>
    @if($lead->summary)
      <div class="lead-summary">{{ $lead->summary }}</div>
    @endif
    @if($lead->photos && count($lead->photos))
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
        @foreach($lead->photos as $photo)
          <a href="{{ $photo }}" target="_blank">
            <img src="{{ $photo }}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ece9e4;">
          </a>
        @endforeach
      </div>
    @endif
  </div>
  @endforeach
</div>
@if($leads->hasPages())
<div style="margin-top:20px;">{{ $leads->links() }}</div>
@endif
@endif

@endsection
