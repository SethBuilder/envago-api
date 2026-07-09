@extends('layouts.dashboard')
@section('title', 'Artigos')
@section('page-title', 'Artigos')

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
    {{ session('success') }}
  </div>
@endif

<div class="section">
  <div class="section-header">
    <span class="section-title">Todos os artigos</span>
    <span style="font-size:13px;color:#888;">
      {{ $articles->where('status','published')->whereNull('deleted_at')->count() }} publicados ·
      {{ $articles->where('status','draft')->whereNull('deleted_at')->count() }} rascunhos ·
      {{ $articles->whereNotNull('deleted_at')->count() }} arquivados
    </span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Título</th>
        <th>Destino</th>
        <th>Template</th>
        <th>Status</th>
        <th>Story</th>
        <th>Criado</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($articles as $article)
        <tr style="{{ $article->trashed() ? 'opacity:.45;' : '' }}">
          <td>
            <div style="font-weight:500;font-size:13px;">{{ $article->title }}</div>
            <div style="font-size:11px;color:#999;margin-top:2px;">
              <a href="https://envago.live/guias/{{ $article->slug }}" target="_blank" style="color:#999;text-decoration:none;">
                /guias/{{ $article->slug }}
              </a>
            </div>
          </td>
          <td style="font-size:13px;">{{ $article->destination ?? '—' }}</td>
          <td>
            @if($article->template_type)
              <span style="font-size:11px;background:#f5f5f3;padding:2px 8px;border-radius:50px;color:#555;">{{ $article->template_type }}</span>
            @else
              <span style="color:#ccc;">—</span>
            @endif
          </td>
          <td>
            @if($article->trashed())
              <span style="font-size:11px;background:#fef2f2;color:#b91c1c;padding:2px 8px;border-radius:50px;">arquivado</span>
            @elseif($article->status === 'published')
              <span style="font-size:11px;background:#f0fdf4;color:#166534;padding:2px 8px;border-radius:50px;">publicado</span>
            @else
              <span style="font-size:11px;background:#fffbeb;color:#92400e;padding:2px 8px;border-radius:50px;">{{ $article->status }}</span>
            @endif
          </td>
          <td style="text-align:center;">
            @if($article->story && count($article->story->slides ?? []) > 0)
              <a href="https://envago.live/stories/{{ $article->slug }}" target="_blank" style="font-size:11px;color:#FF2924;text-decoration:none;">
                {{ count($article->story->slides) }} slides
              </a>
            @else
              <span style="color:#ddd;font-size:12px;">—</span>
            @endif
          </td>
          <td style="font-size:12px;color:#888;white-space:nowrap;">{{ $article->created_at->format('d/m/y') }}</td>
          <td style="text-align:right;white-space:nowrap;">
            @if($article->trashed())
              <form method="POST" action="{{ route('admin.articles.restore', $article->id) }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:12px;color:#16a34a;font-weight:500;padding:4px 8px;">Restaurar</button>
              </form>
            @else
              <a href="{{ route('admin.articles.edit', $article) }}" style="font-size:12px;color:#111;font-weight:600;text-decoration:none;margin-right:8px;">Editar →</a>
              <a href="https://envago.live/guias/{{ $article->slug }}" target="_blank" style="font-size:12px;color:#888;text-decoration:none;margin-right:8px;">Ver</a>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;color:#999;padding:32px;">Nenhum artigo ainda.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection
