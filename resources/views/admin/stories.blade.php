@extends('layouts.dashboard')
@section('title', 'Web Stories')
@section('page-title', 'Web Stories')

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
    {{ session('error') }}
  </div>
@endif

<div style="margin-bottom:20px;display:flex;align-items:center;gap:12px;">
  <a href="{{ route('admin.articles') }}" style="font-size:13px;color:#888;text-decoration:none;">← Artigos</a>
  <span style="font-size:13px;color:#999;">{{ $stories->count() }} stories</span>
</div>

<div class="section">
  <div class="section-header"><span class="section-title">Todas as stories</span></div>
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="border-bottom:1px solid #f0f0f0;">
        <th style="text-align:left;padding:10px 16px;color:#888;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Artigo</th>
        <th style="text-align:center;padding:10px 16px;color:#888;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Status</th>
        <th style="text-align:center;padding:10px 16px;color:#888;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Slides</th>
        <th style="text-align:center;padding:10px 16px;color:#888;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Imagens</th>
        <th style="text-align:center;padding:10px 16px;color:#888;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Poster</th>
        <th style="text-align:left;padding:10px 16px;color:#888;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Publicado</th>
        <th style="padding:10px 16px;"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($stories as $story)
        @php
          $total  = count($story->slides);
          $withImg = collect($story->slides)->filter(fn($s) => !empty($s['image_url']))->count();
          $allReady = $withImg === $total && $story->poster_image;
        @endphp
        <tr style="border-bottom:1px solid #f9f9f9;">
          <td style="padding:12px 16px;color:#111;font-weight:500;">
            {{ $story->article?->title ?? '—' }}
            @if($story->article?->destination)
              <span style="display:block;font-size:11px;color:#999;font-weight:400;">{{ $story->article->destination }}</span>
            @endif
          </td>
          <td style="padding:12px 16px;text-align:center;">
            @if($story->status === 'published')
              <span style="font-size:11px;background:#f0fdf4;color:#166534;padding:2px 10px;border-radius:50px;font-weight:600;">publicado</span>
            @else
              <span style="font-size:11px;background:#fffbeb;color:#92400e;padding:2px 10px;border-radius:50px;font-weight:600;">rascunho</span>
            @endif
          </td>
          <td style="padding:12px 16px;text-align:center;color:#555;">{{ $total }}</td>
          <td style="padding:12px 16px;text-align:center;">
            <span style="color:{{ $withImg === $total ? '#166534' : '#b91c1c' }};font-weight:600;">
              {{ $withImg }}/{{ $total }}
            </span>
          </td>
          <td style="padding:12px 16px;text-align:center;">
            @if($story->poster_image)
              <span style="color:#166534;font-size:14px;">✓</span>
            @else
              <span style="color:#b91c1c;font-size:14px;">✗</span>
            @endif
          </td>
          <td style="padding:12px 16px;color:#666;font-size:12px;">
            {{ $story->published_at?->format('d/m/Y') ?? '—' }}
          </td>
          <td style="padding:12px 16px;text-align:right;">
            <a href="{{ route('admin.stories.edit', $story) }}"
               style="font-size:12px;color:#ff2924;text-decoration:none;font-weight:600;">
              Editar →
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" style="padding:32px 16px;text-align:center;color:#999;">Nenhuma story criada ainda.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection
