@extends('layouts.dashboard')
@section('title', 'Editar Story')
@section('page-title', 'Editar Story')

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

{{-- Header bar --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
  <a href="{{ route('admin.stories') }}" style="font-size:13px;color:#888;text-decoration:none;">← Stories</a>
  <span style="color:#ddd;">|</span>
  @if($story->status === 'published')
    <span style="font-size:11px;background:#f0fdf4;color:#166534;padding:2px 10px;border-radius:50px;font-weight:600;">publicado</span>
  @else
    <span style="font-size:11px;background:#fffbeb;color:#92400e;padding:2px 10px;border-radius:50px;font-weight:600;">rascunho</span>
  @endif
  <span style="font-size:13px;color:#555;font-weight:500;">{{ $story->article?->title }}</span>
  @if($story->status === 'published')
    <a href="https://envago.live/story/{{ $story->article?->slug }}" target="_blank"
       style="font-size:12px;color:#888;text-decoration:none;margin-left:auto;">Ver story →</a>
  @endif
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

  {{-- Main: slides --}}
  <div>
    @foreach($story->slides as $i => $slide)
      @php
        $hasImage = !empty($slide['image_url']);
        $num      = $i + 1;
        $total    = count($story->slides);
      @endphp
      <div class="section" style="margin-bottom:12px;">
        <div class="section-header" style="display:flex;align-items:center;justify-content:space-between;">
          <span class="section-title">Slide {{ $num }} / {{ $total }}</span>
          @if($hasImage)
            <span style="font-size:11px;color:#166534;font-weight:600;">✓ com imagem</span>
          @else
            <span style="font-size:11px;color:#b91c1c;font-weight:600;">✗ sem imagem</span>
          @endif
        </div>
        <div style="display:grid;grid-template-columns:1fr 180px;gap:16px;padding:16px;">

          {{-- Text preview --}}
          <div>
            <p style="font-size:13px;font-weight:600;color:#111;margin:0 0 4px;">{{ $slide['title'] ?? '' }}</p>
            <p style="font-size:12px;color:#666;margin:0 0 12px;line-height:1.5;">{{ $slide['body'] ?? '' }}</p>
            @if(!empty($slide['cta_text']))
              <span style="font-size:11px;background:#fff5f5;color:#ff2924;border:1px solid #fecaca;padding:2px 8px;border-radius:4px;">
                CTA: {{ $slide['cta_text'] }}
              </span>
            @endif
          </div>

          {{-- Image area — own form per slide --}}
          <div>
            @if($hasImage)
              <img src="{{ $slide['image_url'] }}" alt="Slide {{ $num }}"
                style="width:100%;aspect-ratio:9/16;object-fit:cover;border-radius:8px;margin-bottom:10px;">
            @else
              <div style="width:100%;aspect-ratio:9/16;background:#ECEAE6;border-radius:8px;margin-bottom:10px;display:flex;align-items:center;justify-content:center;">
                <span style="font-size:11px;color:#999;">sem imagem</span>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.stories.update', $story) }}" enctype="multipart/form-data">
              @csrf
              <label style="display:block;font-size:10px;font-weight:600;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Upload</label>
              <input type="file" name="slide_image_{{ $i }}" accept="image/*"
                style="width:100%;font-size:11px;margin-bottom:8px;">

              <label style="display:block;font-size:10px;font-weight:600;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">ou URL</label>
              <input type="url" name="slide_image_url_{{ $i }}" placeholder="https://..."
                value="{{ $slide['image_url'] ?? '' }}"
                style="width:100%;font-size:11px;border:1px solid #e5e5e5;border-radius:6px;padding:6px 8px;box-sizing:border-box;margin-bottom:10px;">

              <button type="submit"
                style="width:100%;background:#111;color:#fff;border:none;padding:8px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                Salvar
              </button>
            </form>
          </div>

        </div>
      </div>
    @endforeach
  </div>

  {{-- Sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Publish / Unpublish --}}
    <div class="section">
      <div class="section-header"><span class="section-title">Status</span></div>
      <div style="padding:16px;">
        @php
          $slidesDone = collect($story->slides)->filter(fn($s) => !empty($s['image_url']))->count();
          $total      = count($story->slides);
          $canPublish = $slidesDone === $total && $story->poster_image;
        @endphp

        @if($story->status === 'published')
          <p style="font-size:12px;color:#666;margin:0 0 12px;">
            Publicado em {{ $story->published_at?->format('d/m/Y H:i') ?? '—' }}
          </p>
          <form method="POST" action="{{ route('admin.stories.unpublish', $story) }}">
            @csrf
            <button type="submit"
              style="width:100%;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              Despublicar
            </button>
          </form>
        @else
          <p style="font-size:12px;color:#666;margin:0 0 8px;">
            Rascunho — não visível ao público.
          </p>
          <p style="font-size:11px;color:#999;margin:0 0 12px;">
            {{ $slidesDone }}/{{ $total }} slides com imagem
            @if(!$story->poster_image) · sem poster @endif
          </p>
          <form method="POST" action="{{ route('admin.stories.publish', $story) }}">
            @csrf
            <button type="submit" {{ $canPublish ? '' : 'disabled' }}
              style="width:100%;background:{{ $canPublish ? '#ff2924' : '#e5e5e5' }};color:{{ $canPublish ? '#fff' : '#aaa' }};border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:{{ $canPublish ? 'pointer' : 'not-allowed' }};">
              Publicar agora
            </button>
          </form>
          @if(!$canPublish)
            <p style="font-size:11px;color:#aaa;margin:8px 0 0;text-align:center;">
              Adicione todas as imagens e o poster
            </p>
          @endif
        @endif
      </div>
    </div>

    {{-- Poster image --}}
    <div class="section">
      <div class="section-header"><span class="section-title">Poster (Google Discover)</span></div>
      <div style="padding:16px;">
        <p style="font-size:11px;color:#999;margin:0 0 10px;line-height:1.5;">
          Imagem portrait (9:16) exibida no Google Discover. Min 640×853px.
        </p>
        @if($story->poster_image)
          <img src="{{ $story->poster_image }}" alt="Poster"
            style="width:100%;aspect-ratio:9/16;object-fit:cover;border-radius:8px;margin-bottom:12px;">
        @else
          <div style="width:100%;aspect-ratio:9/16;background:#ECEAE6;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;justify-content:center;">
            <span style="font-size:11px;color:#999;">sem poster</span>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.stories.update', $story) }}" enctype="multipart/form-data">
          @csrf
          <label style="display:block;font-size:10px;font-weight:600;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Upload</label>
          <input type="file" name="poster_image" accept="image/*"
            style="width:100%;font-size:11px;margin-bottom:8px;">
          <label style="display:block;font-size:10px;font-weight:600;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">ou URL</label>
          <input type="url" name="poster_image_url" placeholder="https://..."
            style="width:100%;font-size:11px;border:1px solid #e5e5e5;border-radius:6px;padding:6px 8px;box-sizing:border-box;margin-bottom:10px;">
          <button type="submit"
            style="width:100%;background:#f5f5f3;color:#333;border:1px solid #e5e5e5;padding:8px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
            Salvar poster
          </button>
        </form>
      </div>
    </div>

    {{-- Info --}}
    <div class="section">
      <div class="section-header"><span class="section-title">Informações</span></div>
      <div style="padding:16px;font-size:12px;color:#666;display:flex;flex-direction:column;gap:8px;">
        <div>
          <span style="color:#999;">Artigo:</span>
          <a href="{{ route('admin.articles.edit', $story->article) }}"
             style="color:#ff2924;text-decoration:none;margin-left:4px;">
            {{ $story->article?->title }}
          </a>
        </div>
        <div>
          <span style="color:#999;">Slides:</span>
          <span style="margin-left:4px;">{{ count($story->slides) }}</span>
        </div>
        <div>
          <span style="color:#999;">URL:</span>
          <code style="font-size:11px;margin-left:4px;">/story/{{ $story->article?->slug }}</code>
        </div>
        <div>
          <span style="color:#999;">Criada:</span>
          <span style="margin-left:4px;">{{ $story->created_at->format('d/m/Y') }}</span>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection
