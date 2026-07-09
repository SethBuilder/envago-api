@extends('layouts.dashboard')
@section('title', 'Editar Artigo')
@section('page-title', 'Editar Artigo')

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
    {{ session('success') }}
  </div>
@endif

{{-- Header bar --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
  <a href="{{ route('admin.articles') }}" style="font-size:13px;color:#888;text-decoration:none;">← Artigos</a>
  <span style="color:#ddd;">|</span>
  @if($article->status === 'published')
    <span style="font-size:11px;background:#f0fdf4;color:#166534;padding:2px 10px;border-radius:50px;font-weight:600;">publicado</span>
  @else
    <span style="font-size:11px;background:#fffbeb;color:#92400e;padding:2px 10px;border-radius:50px;font-weight:600;">rascunho</span>
  @endif
  <a href="https://envago.live/guias/{{ $article->slug }}" target="_blank" style="font-size:12px;color:#888;text-decoration:none;margin-left:auto;">Ver ao vivo →</a>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

  {{-- Main: content --}}
  <div>
    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
      @csrf

      <div class="section" style="margin-bottom:16px;">
        <div class="section-header"><span class="section-title">Conteúdo HTML</span></div>
        <div style="padding:16px;">
          <textarea name="content" rows="40"
            style="width:100%;font-family:'Courier New',monospace;font-size:12px;line-height:1.5;border:1px solid #e5e5e5;border-radius:8px;padding:12px;resize:vertical;box-sizing:border-box;color:#222;">{{ $article->content }}</textarea>
        </div>
      </div>

      <div style="text-align:right;margin-bottom:24px;">
        <button type="submit"
          style="background:#111;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
          Salvar alterações
        </button>
      </div>

    </form>
  </div>

  {{-- Sidebar: meta + image + publish --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Publish / Unpublish --}}
    <div class="section">
      <div class="section-header"><span class="section-title">Status</span></div>
      <div style="padding:16px;">
        @if($article->status === 'published')
          <p style="font-size:12px;color:#666;margin:0 0 12px;">
            Publicado em {{ $article->published_at?->format('d/m/Y H:i') ?? '—' }}
          </p>
          <form method="POST" action="{{ route('admin.articles.unpublish', $article) }}">
            @csrf
            <button type="submit"
              style="width:100%;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              Despublicar
            </button>
          </form>
        @else
          <p style="font-size:12px;color:#666;margin:0 0 12px;">
            Rascunho — não visível ao público.
          </p>
          <form method="POST" action="{{ route('admin.articles.publish', $article) }}">
            @csrf
            <button type="submit"
              style="width:100%;background:#ff2924;color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              Publicar agora
            </button>
          </form>
        @endif
      </div>
    </div>

    {{-- Cover image --}}
    <div class="section">
      <div class="section-header"><span class="section-title">Imagem de capa</span></div>
      <div style="padding:16px;">
        @if($article->cover_image)
          <img src="{{ $article->cover_image }}" alt=""
            style="width:100%;height:140px;object-fit:cover;border-radius:8px;margin-bottom:12px;">
        @else
          <div style="width:100%;height:140px;background:#E5E1DC;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;justify-content:center;">
            <span style="font-size:12px;color:#999;">sem imagem</span>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
          @csrf
          <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Upload</label>
          <input type="file" name="cover_image" accept="image/*"
            style="width:100%;font-size:12px;margin-bottom:10px;">
          <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">ou URL</label>
          <input type="url" name="cover_image_url" placeholder="https://..."
            style="width:100%;font-size:12px;border:1px solid #e5e5e5;border-radius:6px;padding:7px 10px;box-sizing:border-box;margin-bottom:10px;">
          <button type="submit"
            style="width:100%;background:#f5f5f3;color:#333;border:1px solid #e5e5e5;padding:8px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
            Salvar imagem
          </button>
        </form>
      </div>
    </div>

    {{-- Meta fields --}}
    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
      @csrf
      <div class="section">
        <div class="section-header"><span class="section-title">Detalhes</span></div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">

          <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Título</label>
            <input type="text" name="title" value="{{ $article->title }}"
              style="width:100%;font-size:13px;border:1px solid #e5e5e5;border-radius:6px;padding:7px 10px;box-sizing:border-box;">
          </div>

          <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Destino</label>
            <input type="text" name="destination" value="{{ $article->destination }}"
              style="width:100%;font-size:13px;border:1px solid #e5e5e5;border-radius:6px;padding:7px 10px;box-sizing:border-box;">
          </div>

          <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">SEO Title</label>
            <input type="text" name="meta_title" value="{{ $article->meta_title }}"
              style="width:100%;font-size:13px;border:1px solid #e5e5e5;border-radius:6px;padding:7px 10px;box-sizing:border-box;">
            <span style="font-size:10px;color:#aaa;">{{ strlen($article->meta_title ?? '') }}/60</span>
          </div>

          <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">SEO Description</label>
            <textarea name="meta_description" rows="3"
              style="width:100%;font-size:12px;border:1px solid #e5e5e5;border-radius:6px;padding:7px 10px;box-sizing:border-box;resize:vertical;">{{ $article->meta_description }}</textarea>
            <span style="font-size:10px;color:#aaa;">{{ strlen($article->meta_description ?? '') }}/155</span>
          </div>

          <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em;">Template</label>
            <select name="template_type"
              style="width:100%;font-size:13px;border:1px solid #e5e5e5;border-radius:6px;padding:7px 10px;box-sizing:border-box;background:#fff;">
              <option value="">—</option>
              @foreach(['listicle','guide','itinerary','comparison','pricing'] as $t)
                <option value="{{ $t }}" {{ $article->template_type === $t ? 'selected' : '' }}>{{ $t }}</option>
              @endforeach
            </select>
          </div>

          <div style="font-size:11px;color:#aaa;padding-top:4px;border-top:1px solid #f0f0f0;">
            Slug: <code style="font-size:11px;">/guias/{{ $article->slug }}</code>
          </div>

          <button type="submit"
            style="width:100%;background:#111;color:#fff;border:none;padding:9px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            Salvar detalhes
          </button>
        </div>
      </div>
    </form>

    {{-- Archive --}}
    <div class="section">
      <div class="section-header"><span class="section-title">Zona de perigo</span></div>
      <div style="padding:16px;">
        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
          onsubmit="return confirm('Arquivar este artigo?')">
          @csrf
          @method('DELETE')
          <button type="submit"
            style="width:100%;background:none;border:1px solid #fecaca;color:#b91c1c;padding:8px;border-radius:6px;font-size:12px;cursor:pointer;">
            Arquivar artigo
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

{{-- Web Story Slides --}}
<div class="section" style="margin-top:20px;">
  <div class="section-header" style="display:flex;align-items:center;justify-content:space-between;">
    <span class="section-title">Web Story — Slides</span>
    <span id="slides-count" style="font-size:11px;color:#aaa;"></span>
  </div>
  <div style="padding:16px;">
    <div id="slides-list" style="display:flex;flex-direction:column;gap:12px;"></div>
    <button type="button" onclick="addSlide()"
      style="margin-top:12px;background:#f5f5f3;border:1px solid #e5e5e5;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">
      + Adicionar slide
    </button>
    <form id="slides-form" method="POST" action="{{ route('admin.articles.update', $article) }}" style="margin-top:16px;text-align:right;">
      @csrf
      <input type="hidden" name="slides_json" id="slides-json-input">
      <button type="submit"
        style="background:#111;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
        Salvar slides
      </button>
    </form>
  </div>
</div>

@push('scripts')
<script>
(function () {
  var initialSlides = @json($article->story?->slides ?? []);
  var slides = JSON.parse(JSON.stringify(initialSlides));

  function render() {
    var list = document.getElementById('slides-list');
    document.getElementById('slides-count').textContent = slides.length + ' slide' + (slides.length !== 1 ? 's' : '');
    list.innerHTML = '';
    slides.forEach(function (slide, i) {
      var card = document.createElement('div');
      card.style.cssText = 'border:1px solid #e5e5e5;border-radius:10px;overflow:hidden;';
      card.innerHTML =
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f5f5f3;border-bottom:1px solid #e5e5e5;">' +
          '<span style="font-size:12px;font-weight:600;color:#555;">Slide ' + (i + 1) + '</span>' +
          '<button type="button" onclick="removeSlide(' + i + ')" style="font-size:11px;color:#b91c1c;background:none;border:none;cursor:pointer;padding:2px 6px;">Remover</button>' +
        '</div>' +
        '<div style="padding:12px;display:flex;flex-direction:column;gap:8px;">' +
          field('Título', 'title', slide.title, 'text', i) +
          field('Texto do slide', 'body', slide.body, 'textarea', i) +
          field('Imagem URL', 'image_url', slide.image_url || '', 'text', i) +
          (slide.image_url ? '<img src="' + escHtml(slide.image_url) + '" style="height:80px;object-fit:cover;border-radius:6px;" onerror="this.style.display=\'none\'">' : '') +
          '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">' +
            field('CTA texto', 'cta_text', slide.cta_text || '', 'text', i) +
            field('CTA URL', 'cta_url', slide.cta_url || '', 'text', i) +
          '</div>' +
        '</div>';
      list.appendChild(card);
    });
  }

  function field(label, key, value, type, idx) {
    var id = 'slide_' + idx + '_' + key;
    var inputHtml = type === 'textarea'
      ? '<textarea id="' + id + '" onchange="updateSlide(' + idx + ',\'' + key + '\',this.value)" oninput="updateSlide(' + idx + ',\'' + key + '\',this.value)" rows="2" style="width:100%;font-size:12px;border:1px solid #e5e5e5;border-radius:6px;padding:6px 8px;box-sizing:border-box;resize:vertical;">' + escHtml(value) + '</textarea>'
      : '<input id="' + id + '" type="text" value="' + escHtml(value) + '" onchange="updateSlide(' + idx + ',\'' + key + '\',this.value)" style="width:100%;font-size:12px;border:1px solid #e5e5e5;border-radius:6px;padding:6px 8px;box-sizing:border-box;">';
    return '<div><label style="display:block;font-size:10px;font-weight:600;color:#888;margin-bottom:3px;text-transform:uppercase;letter-spacing:.04em;">' + label + '</label>' + inputHtml + '</div>';
  }

  function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  window.updateSlide = function (idx, key, value) {
    slides[idx][key] = value || null;
    // Re-render image preview without full re-render (avoid losing focus)
    if (key === 'image_url') render();
  };

  window.addSlide = function () {
    slides.push({ title: '', body: '', image_url: null, cta_text: null, cta_url: null });
    render();
  };

  window.removeSlide = function (idx) {
    slides.splice(idx, 1);
    render();
  };

  document.getElementById('slides-form').addEventListener('submit', function () {
    document.getElementById('slides-json-input').value = JSON.stringify(slides);
  });

  render();
})();
</script>
@endpush

@endsection
