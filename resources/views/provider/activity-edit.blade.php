@extends('layouts.dashboard')
@section('title', 'Editar Passeio')
@section('page-title', 'Editar Passeio')

@push('styles')
<style>
.create-wrap { max-width: 640px; }

.step-card { background:#fff; border:1px solid #e8e8e8; border-radius:14px; margin-bottom:16px; overflow:hidden; }
.step-head { padding:20px 24px 0; display:flex; align-items:center; gap:14px; }
.step-num { width:28px;height:28px;border-radius:50%;background:#111;color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.step-title { font-size:15px;font-weight:600;color:#111; }
.step-desc  { font-size:13px;color:#999;margin-top:2px; }
.step-divider { height:1px;background:#f0f0f0;margin:18px 24px 0; }
.step-body { padding:20px 24px 24px;display:flex;flex-direction:column;gap:16px; }

.f-group { display:flex;flex-direction:column;gap:5px; }
.f-label { font-size:13px;font-weight:500;color:#444; }
.f-hint  { font-size:12px;color:#aaa;margin-top:3px; }
.f-error { font-size:12px;color:#dc2626;margin-top:3px;display:none; }
.f-error.show { display:block; }
.f-input {
  width:100%;padding:9px 12px;
  border:1px solid #ddd;border-radius:8px;
  font-size:14px;font-family:inherit;color:#111;
  background:#fff;outline:none;box-sizing:border-box;
  transition:border-color .15s,box-shadow .15s;
}
.f-input:focus { border-color:#111;box-shadow:0 0 0 3px rgba(0,0,0,.06); }
.f-input.err  { border-color:#dc2626; }
.f-col2 { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.f-col3-1 { display:grid;grid-template-columns:2fr 1fr;gap:12px; }
.f-prefix-wrap { position:relative; }
.f-prefix { position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;font-weight:500;color:#888;pointer-events:none; }
.f-prefix-wrap .f-input { padding-left:30px; }

/* Schedule groups */
.sched-group { border:1px solid #e8e8e8;border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:12px;position:relative; }
.sched-group-remove { position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:6px;border:1px solid #e8e8e8;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#bbb;transition:all .15s; }
.sched-group-remove:hover { border-color:#ff2924;color:#ff2924;background:#fff5f5; }
.day-row { display:flex;gap:6px;flex-wrap:wrap; }
.day-btn { width:44px;height:44px;border-radius:8px;border:1.5px solid #e8e8e8;background:#fff;font-size:11px;font-weight:600;color:#888;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;transition:all .15s;user-select:none; }
.day-btn:hover { border-color:#111;color:#111; }
.day-btn.on { background:#111;border-color:#111;color:#fff; }
.day-btn .da { font-size:12px;font-weight:700; }
.day-btn .db { font-size:9px;opacity:.7; }
.time-list { display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
.time-chip { display:flex;align-items:center;gap:6px; }
.time-chip .f-input { width:130px; }
.time-chip-remove { width:28px;height:28px;border-radius:6px;border:1px solid #e8e8e8;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#bbb;transition:all .15s;flex-shrink:0; }
.time-chip-remove:hover { border-color:#ff2924;color:#ff2924;background:#fff5f5; }
.add-time-btn { display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;border:1.5px dashed #d8d8d8;background:#fafafa;color:#888;font-size:12px;font-weight:500;cursor:pointer;transition:all .15s; }
.add-time-btn:hover { border-color:#111;color:#111;background:#fff; }
.add-group-btn { display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:10px;border:1.5px dashed #d8d8d8;background:#fafafa;color:#666;font-size:13px;font-weight:500;cursor:pointer;width:100%;justify-content:center;transition:all .15s; }
.add-group-btn:hover { border-color:#111;color:#111;background:#fff; }

/* Photos */
.photo-drop { border:2px dashed #e0e0e0;border-radius:12px;padding:32px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa; }
.photo-drop:hover,.photo-drop.drag-over { border-color:#111;background:#fff; }
.photo-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;margin-top:12px; }
.media-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;margin-top:12px;align-items:start; }
.photo-thumb { position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;border:1px solid #e8e8e8;background:#f5f5f5;cursor:grab; }
.photo-thumb.sortable-ghost { opacity:.35;border:2px dashed #aaa; }
.photo-thumb.sortable-drag  { cursor:grabbing; }
.photo-thumb img { width:100%;height:100%;object-fit:cover; }
.photo-thumb-remove { position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(0,0,0,.55);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s; }
.photo-thumb-remove:hover { background:rgba(220,38,38,.9); }
.photo-first-badge { position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,.6);color:#fff;font-size:9px;font-weight:600;padding:2px 6px;border-radius:4px;letter-spacing:.3px; }
.photo-existing-badge { position:absolute;bottom:4px;right:4px;background:rgba(0,128,0,.6);color:#fff;font-size:9px;font-weight:600;padding:2px 6px;border-radius:4px; }

/* Videos */
.video-drop { border:2px dashed #e0e0e0;border-radius:12px;padding:32px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa; }
.video-drop:hover,.video-drop.drag-over { border-color:#111;background:#fff; }
.video-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-top:12px; }
.video-thumb { position:relative;aspect-ratio:16/9;border-radius:8px;overflow:hidden;border:1px solid #e8e8e8;background:#111;display:flex;align-items:center;justify-content:center;cursor:grab; }
.video-thumb.sortable-ghost { opacity:.35;border:2px dashed #aaa; }
.video-thumb.sortable-drag  { cursor:grabbing; }
.video-thumb video,.video-thumb img { width:100%;height:100%;object-fit:cover; }
.video-thumb-remove { position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(0,0,0,.55);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;z-index:10; }
.video-thumb-remove:hover { background:rgba(220,38,38,.9); }
.video-existing-badge { position:absolute;bottom:4px;right:4px;background:rgba(0,0,0,.6);color:#fff;font-size:9px;font-weight:600;padding:2px 6px;border-radius:4px; }
.video-play-icon { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none; }
.video-play-icon svg { opacity:.8; }

/* Actions */
.form-actions { display:flex;gap:10px;align-items:center;margin-top:4px; }
.btn-submit { display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:10px;border:none;background:#111;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:opacity .15s,transform .1s;flex:1;justify-content:center; }
.btn-submit:hover:not(:disabled) { opacity:.87; }
.btn-submit:disabled { opacity:.5;cursor:not-allowed; }
.btn-back { display:inline-flex;align-items:center;gap:6px;padding:12px 18px;border-radius:10px;border:1px solid #e8e8e8;background:#fff;color:#555;font-size:14px;font-weight:500;cursor:pointer;text-decoration:none;transition:background .12s; }
.btn-back:hover { background:#f5f5f5; }

.general-error { background:#fff5f5;border:1px solid #fca5a5;border-radius:10px;padding:14px 20px;margin-bottom:16px;font-size:13px;color:#c62828;display:none; }
.general-error.show { display:block; }

.schedules-notice { background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:12px 16px;font-size:13px;color:#854d0e; }

@media(max-width:480px){
  .f-col2,.f-col3-1{grid-template-columns:1fr;}
  .step-head{padding:16px 16px 0;}
  .step-body{padding:16px;}
}
</style>
@endpush

@section('content')

<div id="general-error" class="general-error create-wrap"></div>

<form id="edit-form" class="create-wrap" novalidate>
  @csrf

  {{-- Step 1: Details --}}
  <div class="step-card">
    <div class="step-head">
      <div class="step-num">1</div>
      <div><div class="step-title">Detalhes do passeio</div><div class="step-desc">Nome, categoria, preço e duração</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">

      <div class="f-group">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
          <label class="f-label" style="margin:0;">Nome do passeio <span style="color:#ff2924">*</span></label>
          <button type="button" id="btn-enhance-name" onclick="aiEnhanceName()"
            style="font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #e0e0e0;background:#fafafa;color:#666;cursor:pointer;display:flex;align-items:center;gap:4px;line-height:1;">
            ✨ Traduzir com IA
          </button>
        </div>
        <input type="text" name="name" value="{{ $activity->name }}"
          class="f-input" maxlength="120" autocomplete="off" id="f-name">
        <span class="f-error" id="err-name"></span>
      </div>

      <div class="f-col2">
        <div class="f-group">
          <label class="f-label">Categoria</label>
          <select name="category_id" class="f-input" id="f-category_id">
            <option value="">Sem categoria</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected($activity->category_id == $cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="f-group">
          <label class="f-label" id="price-label">
            {{ $activity->pricing_type === 'per_unit' ? 'Preço por unidade' : 'Preço por pessoa' }}
            <span style="color:#ff2924">*</span>
          </label>
          <div class="f-prefix-wrap">
            <span class="f-prefix">R$</span>
            <input type="number" name="price" value="{{ $activity->price }}"
              class="f-input" min="1" max="99999" step="1" id="f-price">
          </div>
          <div style="display:flex;gap:16px;margin-top:8px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:#444;">
              <input type="radio" name="pricing_type" value="per_person"
                {{ $activity->pricing_type !== 'per_unit' ? 'checked' : '' }}
                onchange="onPricingTypeChange(this.value)"> Por pessoa
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:#444;">
              <input type="radio" name="pricing_type" value="per_unit"
                {{ $activity->pricing_type === 'per_unit' ? 'checked' : '' }}
                onchange="onPricingTypeChange(this.value)"> Por unidade
            </label>
          </div>
          <div id="pricing-unit-wrap" style="display:{{ $activity->pricing_type === 'per_unit' ? 'block' : 'none' }};margin-top:8px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <div>
                <input type="text" name="pricing_unit" class="f-input" id="f-pricing_unit"
                  value="{{ $activity->pricing_unit }}"
                  placeholder="ex: buggy, barco, cavalo" maxlength="30" autocomplete="off">
                <span class="f-hint">Nome da unidade</span>
                <span class="f-error" id="err-pricing_unit"></span>
              </div>
              <div>
                <input type="number" name="unit_capacity" class="f-input" id="f-unit_capacity"
                  value="{{ $activity->unit_capacity }}"
                  placeholder="ex: 4" min="1" max="99">
                <span class="f-hint" id="unit-capacity-hint">Pessoas por unidade</span>
                <span class="f-error" id="err-unit_capacity"></span>
              </div>
            </div>
          </div>
          <span class="f-error" id="err-price"></span>
        </div>
      </div>

      <div class="f-col2">
        <div class="f-group">
          <label class="f-label">Duração <span style="color:#ff2924">*</span></label>
          <div style="position:relative;">
            <input type="number" name="duration_minutes" value="{{ $activity->duration_minutes }}"
              class="f-input" min="15" max="1440" style="padding-right:44px;" id="f-duration_minutes">
            <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:12px;color:#aaa;pointer-events:none;">min</span>
          </div>
          <span class="f-error" id="err-duration_minutes"></span>
        </div>
        <div class="f-group">
          <label class="f-label">Vagas por horário <span style="color:#ff2924">*</span></label>
          <input type="number" name="capacity" value="{{ $activity->capacity }}"
            class="f-input" min="1" max="999" id="f-capacity">
          <span class="f-hint">Quantas pessoas cabem por saída</span>
          <span class="f-error" id="err-capacity"></span>
        </div>
      </div>

      <div class="f-col2">
        <div class="f-group">
          <label class="f-label">Cidade <span style="color:#ff2924">*</span></label>
          <input type="text" name="city" value="{{ $activity->city }}"
            class="f-input" maxlength="80" id="f-city">
          <span class="f-error" id="err-city"></span>
        </div>
        <div class="f-group">
          <label class="f-label">Estado <span style="color:#ff2924">*</span></label>
          <input type="text" name="state" value="{{ $activity->state }}"
            class="f-input" maxlength="2" style="text-transform:uppercase;" id="f-state">
          <span class="f-error" id="err-state"></span>
        </div>
      </div>

      <div class="f-group">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
          <label class="f-label" style="margin:0;">Descrição</label>
          <button type="button" id="btn-enhance-desc" onclick="aiEnhanceDescription()"
            style="font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #e0e0e0;background:#fafafa;color:#666;cursor:pointer;display:flex;align-items:center;gap:4px;line-height:1;">
            ✨ Melhorar com IA
          </button>
        </div>
        <textarea name="description" rows="4" class="f-input"
          style="resize:vertical;line-height:1.5;" maxlength="2000" id="f-description">{{ $activity->description }}</textarea>
        <span class="f-error" id="err-description"></span>
      </div>

      <div class="f-group">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
          <label class="f-label" style="margin:0;">Destaques</label>
          <button type="button" id="btn-extract-hl" onclick="aiExtractHighlights()"
            style="font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #e0e0e0;background:#fafafa;color:#666;cursor:pointer;display:flex;align-items:center;gap:4px;line-height:1;">
            ✨ Extrair com IA
          </button>
        </div>
        <textarea id="f-highlights-raw" rows="4" class="f-input"
          placeholder="Um destaque por linha&#10;ex: Pôr do sol nas dunas&#10;Guia local experiente&#10;Inclui água e protetor solar"
          style="resize:vertical;line-height:1.6;">{{ $activity->highlights ? implode("\n", $activity->highlights) : '' }}</textarea>
        <input type="hidden" name="highlights" id="f-highlights">
        <span class="f-hint">Um destaque por linha. Aparece como lista na página do passeio.</span>
      </div>

      <div class="f-group">
        <label class="f-label">Ponto de encontro</label>
        <input type="text" name="meeting_point" id="meeting-point-input" class="f-input"
          value="{{ $activity->meeting_point }}" autocomplete="off">
        <input type="hidden" name="meeting_point_lat" id="meeting-point-lat" value="{{ $activity->meeting_point_lat }}">
        <input type="hidden" name="meeting_point_lng" id="meeting-point-lng" value="{{ $activity->meeting_point_lng }}">
        <span class="f-hint">
          Onde o turista deve se apresentar.
          @if($activity->meeting_point_lat && $activity->meeting_point_lng)
            <a id="meeting-point-map-link" href="https://www.google.com/maps?q={{ $activity->meeting_point_lat }},{{ $activity->meeting_point_lng }}"
              target="_blank" rel="noopener" style="color:#111;font-weight:500;">Ver no mapa ↗</a>
          @else
            <a id="meeting-point-map-link" href="#" target="_blank" rel="noopener" style="color:#111;font-weight:500;display:none;">Ver no mapa ↗</a>
          @endif
        </span>
        <span class="f-error" id="err-meeting_point"></span>
      </div>

    </div>
  </div>

  {{-- Step 2: Schedules --}}
  <div class="step-card">
    <div class="step-head">
      <div class="step-num">2</div>
      <div><div class="step-title">Horários</div><div class="step-desc">Horários futuros sem reserva serão substituídos</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">
      <div class="schedules-notice">Horários já reservados não serão alterados. Apenas datas futuras sem reserva serão regeneradas.</div>
      <div id="groups-wrap"></div>
      <button type="button" class="add-group-btn" onclick="addGroup()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Adicionar outra combinação de dias
      </button>
      <span class="f-error" id="err-groups"></span>
    </div>
  </div>

  {{-- Step 3: Fotos e Vídeos --}}
  <div class="step-card">
    <div class="step-head">
      <div class="step-num">3</div>
      <div><div class="step-title">Fotos e Vídeos</div><div class="step-desc">A primeira foto é a capa · vídeos: MP4, MOV, WebM · máx 100 MB</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">

      {{-- Existing photos --}}
      @if($activity->media->count() > 0)
      <div class="media-grid" id="existing-media-grid">
        @foreach($activity->media->sortBy('order') as $media)
          @if(in_array($media->type, ['cover', 'gallery']))
            <div class="photo-thumb" id="em-{{ $media->id }}" data-media-id="{{ $media->id }}">
              <img src="{{ $media->url }}" alt="">
              @if($loop->first)
                <span class="photo-first-badge">CAPA</span>
              @endif
              <span class="photo-existing-badge">Salva</span>
              <button type="button" class="photo-thumb-remove" onclick="markDeleteMedia({{ $media->id }})">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
          @else
            <div class="video-thumb" id="em-{{ $media->id }}" data-media-id="{{ $media->id }}">
              @if($media->poster_url)
                <img src="{{ $media->poster_url }}" alt="">
              @else
                <div style="width:100%;height:100%;background:#1a1a1a;"></div>
              @endif
              <div class="video-play-icon">
                <svg width="28" height="28" fill="white" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              </div>
              <span class="video-existing-badge">Salvo</span>
              <button type="button" class="video-thumb-remove" onclick="markDeleteMedia({{ $media->id }})">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
          @endif
        @endforeach
      </div>
      @endif
      <div id="delete-media-inputs"></div>

      {{-- Combined upload drop zone --}}
      <input type="file" id="media-input" accept="image/*,video/mp4,video/quicktime,video/webm" multiple style="display:none;"
        onchange="onMediaSelected(this)">
      <div class="photo-drop" id="media-drop"
        onclick="document.getElementById('media-input').click()"
        ondragover="event.preventDefault();this.classList.add('drag-over')"
        ondragleave="this.classList.remove('drag-over')"
        ondrop="onMediaDrop(event)">
        <svg width="32" height="32" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p style="font-size:14px;font-weight:500;color:#555;margin:0 0 4px;">Adicionar fotos e vídeos</p>
        <p style="font-size:12px;color:#aaa;margin:0;">JPG, PNG, WebP · MP4, MOV, WebM · máx 100 MB cada</p>
      </div>
      <div class="photo-grid" id="photo-grid"></div>
      <div class="video-grid" id="video-grid" style="margin-top:4px;"></div>
      <span class="f-error" id="err-photos"></span>

      {{-- Media URL import --}}
      <div style="border-top:1px solid #f0f0f0;padding-top:16px;margin-top:8px;">
        <div class="f-label" style="margin-bottom:8px;">Importar fotos e vídeos</div>
        <div id="ig-url-list"></div>
        <button type="button" onclick="addIgUrl()" class="add-time-btn" style="margin-top:4px;">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Adicionar link
        </button>
        <p class="f-hint" style="margin-top:6px;">Cole um link direto de foto ou vídeo, ou um post do Instagram.</p>
      </div>

    </div>
  </div>

  {{-- Step 4: Custom Questions --}}
  <div class="step-card" id="questions-card">
    <div class="step-head">
      <div class="step-num">4</div>
      <div><div class="step-title">Perguntas para o cliente</div><div class="step-desc">Informações que você precisa coletar na hora da reserva (ex: pousada para embarque)</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">

      {{-- Existing questions list --}}
      <div id="questions-list">
        @forelse($activity->questions as $q)
        <div class="question-row" id="qrow-{{ $q->id }}" style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid #e8e8e8;border-radius:8px;gap:10px;">
          <div style="flex:1;min-width:0;">
            <span style="font-size:13px;font-weight:500;color:#111;">{{ $q->label }}</span>
            <span style="font-size:11px;color:#aaa;margin-left:8px;">{{ $q->type === 'select' ? 'seleção' : 'texto livre' }}{{ $q->required ? ' · obrigatório' : '' }}</span>
            @if($q->type === 'select' && $q->options)
            <div style="font-size:11px;color:#999;margin-top:2px;">{{ implode(', ', $q->options) }}</div>
            @endif
          </div>
          <button type="button" onclick="deleteQuestion({{ $q->id }})"
            style="flex-shrink:0;width:28px;height:28px;border-radius:6px;border:1px solid #e8e8e8;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#bbb;transition:all .15s;"
            onmouseover="this.style.borderColor='#ff2924';this.style.color='#ff2924';"
            onmouseout="this.style.borderColor='#e8e8e8';this.style.color='#bbb';">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        @empty
        <p id="questions-empty" style="font-size:13px;color:#aaa;margin:0;">Nenhuma pergunta adicionada ainda.</p>
        @endforelse
      </div>

      {{-- Add question form --}}
      <div style="border-top:1px solid #f0f0f0;padding-top:16px;margin-top:4px;">
        <div class="f-label" style="margin-bottom:10px;">Adicionar pergunta</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <input type="text" id="q-label" class="f-input" placeholder="Ex: Em qual pousada você está hospedado?" maxlength="255">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
              <label class="f-label" style="margin-bottom:5px;">Tipo</label>
              <select id="q-type" class="f-input" onchange="onQTypeChange(this.value)">
                <option value="text">Texto livre</option>
                <option value="select">Seleção (lista)</option>
              </select>
            </div>
            <div style="display:flex;align-items:flex-end;">
              <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#555;cursor:pointer;padding-bottom:9px;">
                <input type="checkbox" id="q-required" checked style="width:14px;height:14px;accent-color:#111;"> Obrigatório
              </label>
            </div>
          </div>
          <div id="q-options-wrap" style="display:none;">
            <label class="f-label" style="margin-bottom:5px;">Opções <span style="font-weight:400;color:#aaa;">(uma por linha)</span></label>
            <textarea id="q-options" class="f-input" rows="4" placeholder="Pousada do Sol&#10;Pousada Mar Azul&#10;Hotel Central&#10;Outro"></textarea>
          </div>
          <button type="button" onclick="addQuestion()"
            style="align-self:flex-start;display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;border:none;background:#111;color:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;"
            onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'" id="btn-add-question">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Adicionar
          </button>
        </div>
      </div>

    </div>
  </div>

  <div class="form-actions">
    <a href="{{ route('provider.activities') }}" class="btn-back">Cancelar</a>
    <button type="submit" class="btn-submit" id="submit-btn">Salvar alterações</button>
  </div>

</form>

@push('scripts')
@if(config('services.google.places_key'))
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.places_key') }}&libraries=places&callback=initPlaces" async defer></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
<script>
async function compressPhotos(files) {
  const opts = { maxSizeMB: 1.5, maxWidthOrHeight: 2048, useWebWorker: true };
  return Promise.all(files.map(f => imageCompression(f, opts).catch(() => f)));
}
</script>
<script>
// ── Pricing type toggle ──────────────────────────────────────
function onPricingTypeChange(val) {
  document.getElementById('pricing-unit-wrap').style.display = val === 'per_unit' ? 'block' : 'none';
  document.getElementById('price-label').innerHTML = val === 'per_unit'
    ? 'Preço por unidade <span style="color:#ff2924">*</span>'
    : 'Preço por pessoa <span style="color:#ff2924">*</span>';
  const unit = document.getElementById('f-pricing_unit')?.value.trim() || 'unidade';
  document.getElementById('unit-capacity-hint').textContent = `Pessoas por ${unit}`;
}
document.getElementById('f-pricing_unit')?.addEventListener('input', function() {
  const unit = this.value.trim() || 'unidade';
  document.getElementById('unit-capacity-hint').textContent = `Pessoas por ${unit}`;
});

// ── Google Places ─────────────────────────────────────────────
function initPlaces() {
  const input = document.getElementById('meeting-point-input');
  if (!input || !window.google) return;
  const ac = new google.maps.places.Autocomplete(input, { types: ['establishment', 'geocode'], componentRestrictions: { country: 'br' } });
  ac.addListener('place_changed', () => {
    const place = ac.getPlace();
    if (!place.geometry) return;
    document.getElementById('meeting-point-lat').value = place.geometry.location.lat();
    document.getElementById('meeting-point-lng').value = place.geometry.location.lng();
    const link = document.getElementById('meeting-point-map-link');
    link.href = `https://www.google.com/maps?q=${place.geometry.location.lat()},${place.geometry.location.lng()}`;
    link.style.display = 'inline';
  });
}

// ── Existing photo deletion ───────────────────────────────────
const deletedMedia = new Set();
function markDeleteMedia(id) {
  deletedMedia.add(id);
  document.getElementById('em-' + id)?.remove();
  const inp = document.createElement('input');
  inp.type = 'hidden'; inp.name = 'delete_media[]'; inp.value = id;
  inp.id = 'dm-' + id;
  document.getElementById('delete-media-inputs').appendChild(inp);
}

// ── New photo upload ──────────────────────────────────────────
let selectedFiles = [];

function onMediaSelected(input) { routeFiles([...input.files]); input.value = ''; }
function onMediaDrop(e) {
  e.preventDefault();
  document.getElementById('media-drop').classList.remove('drag-over');
  routeFiles([...e.dataTransfer.files]);
}
function routeFiles(files) {
  const imgs = files.filter(f => f.type.startsWith('image/'));
  const vids = files.filter(f => f.type.startsWith('video/'));
  if (imgs.length) addFiles(imgs);
  if (vids.length) addVideoFiles(vids);
}
function addFiles(files) {
  files.slice(0, 8 - selectedFiles.length).forEach(file => {
    const idx = selectedFiles.push(file) - 1;
    const reader = new FileReader();
    reader.onload = e => addThumb(idx, e.target.result, file.name);
    reader.readAsDataURL(file);
  });
}
function addThumb(idx, src, name) {
  const grid = document.getElementById('photo-grid');
  const div = document.createElement('div');
  div.className = 'photo-thumb'; div.id = 'pt-' + idx;
  div.innerHTML = `
    <img src="${src}" alt="${name}">
    <button type="button" class="photo-thumb-remove" onclick="removeNewPhoto(${idx})">
      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  grid.appendChild(div);
}
function removeNewPhoto(idx) {
  selectedFiles.splice(idx, 1);
  document.getElementById('photo-grid').innerHTML = '';
  selectedFiles.forEach((f, i) => { const r = new FileReader(); r.onload = e => addThumb(i, e.target.result, f.name); r.readAsDataURL(f); });
}

// ── New video upload ──────────────────────────────────────────
let selectedVideos = [];

// ── Instagram URL inputs ───────────────────────────────────────
function addIgUrl(val) {
  const list = document.getElementById('ig-url-list');
  const idx = Date.now();
  const div = document.createElement('div');
  div.id = 'ig-' + idx;
  div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
  div.innerHTML = `<input type="text" class="f-input" placeholder="https://... (foto, vídeo ou Instagram)" style="flex:1;" value="${val||''}">
    <button type="button" class="time-chip-remove" onclick="document.getElementById('ig-${idx}').remove()">
      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  list.appendChild(div);
}
function addVideoFiles(files) {
  files.slice(0, 4 - selectedVideos.length).forEach(file => {
    const idx = selectedVideos.push(file) - 1;
    addVideoThumb(idx, file);
  });
}
function addVideoThumb(idx, file) {
  const grid = document.getElementById('video-grid');
  const div = document.createElement('div');
  div.className = 'video-thumb'; div.id = 'vt-' + idx;
  const url = URL.createObjectURL(file);
  div.innerHTML = `
    <video src="${url}" preload="metadata" style="width:100%;height:100%;object-fit:cover;pointer-events:none;"></video>
    <div class="video-play-icon">
      <svg width="24" height="24" fill="white" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
    </div>
    <button type="button" class="video-thumb-remove" onclick="removeNewVideo(${idx})">
      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  grid.appendChild(div);
}
function removeNewVideo(idx) {
  selectedVideos.splice(idx, 1);
  document.getElementById('video-grid').innerHTML = '';
  selectedVideos.forEach((f, i) => addVideoThumb(i, f));
}

// ── Schedule groups ───────────────────────────────────────────
const DAY_LABELS = [['D','Dom'],['S','Seg'],['T','Ter'],['Q','Qua'],['Q','Qui'],['S','Sex'],['S','Sáb']];
let groupSeq = 0;
const groups = {};

const existingGroups = @json($scheduleGroups);
existingGroups.forEach(g => addGroup(g.days, [g.time]));

function addGroup(defaultDays, defaultTimes) {
  const id = ++groupSeq;
  groups[id] = { days: new Set(defaultDays || []), timeSeq: 0 };
  const wrap = document.getElementById('groups-wrap');
  const div = document.createElement('div');
  div.className = 'sched-group'; div.id = 'g-' + id;
  div.innerHTML = `
    <button type="button" class="sched-group-remove" onclick="removeGroup(${id})">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div>
      <div class="f-label" style="margin-bottom:8px;">Dias da semana</div>
      <div class="day-row">
        ${DAY_LABELS.map(([a,f],i) => `
          <button type="button" class="day-btn${(defaultDays||[]).includes(i)?' on':''}" data-day="${i}" onclick="toggleDay(this,${id})">
            <span class="da">${a}</span><span class="db">${f}</span>
          </button>`).join('')}
        <div id="day-inputs-${id}"></div>
      </div>
    </div>
    <div>
      <div class="f-label" style="margin-bottom:8px;">Horários de saída</div>
      <div class="time-list" id="times-${id}"></div>
      <button type="button" class="add-time-btn" style="margin-top:8px;" onclick="addTime(${id})">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Horário
      </button>
    </div>`;
  wrap.appendChild(div);
  updateDayInputs(id);
  (defaultTimes || ['08:00']).forEach(t => addTime(id, t));
}

function removeGroup(id) {
  if (Object.keys(groups).length <= 1) return;
  delete groups[id];
  document.getElementById('g-' + id)?.remove();
}
function toggleDay(btn, id) {
  const d = +btn.dataset.day;
  groups[id].days.has(d) ? (groups[id].days.delete(d), btn.classList.remove('on'))
                         : (groups[id].days.add(d),    btn.classList.add('on'));
  updateDayInputs(id);
}
function updateDayInputs(id) {
  const wrap = document.getElementById('day-inputs-' + id);
  if (!wrap) return;
  wrap.innerHTML = '';
  groups[id].days.forEach(d => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = `groups[${id}][days][]`; inp.value = d;
    wrap.appendChild(inp);
  });
}
function addTime(id, val) {
  const ts = ++groups[id].timeSeq;
  const chip = document.createElement('div');
  chip.className = 'time-chip'; chip.id = `tc-${id}-${ts}`;
  chip.innerHTML = `
    <input type="time" name="groups[${id}][times][]" value="${val||''}" class="f-input">
    <button type="button" class="time-chip-remove" onclick="removeTime(${id},${ts})">
      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  document.getElementById('times-' + id)?.appendChild(chip);
}
function removeTime(id, ts) {
  const list = document.getElementById('times-' + id);
  if (!list || list.children.length <= 1) return;
  document.getElementById(`tc-${id}-${ts}`)?.remove();
}

// ── AI helpers ────────────────────────────────────────────────
async function aiEnhanceName() {
  const inp = document.getElementById('f-name');
  const btn = document.getElementById('btn-enhance-name');
  const name = inp.value.trim();
  if (!name) { alert('Adicione um nome primeiro.'); return; }
  btn.disabled = true; btn.textContent = 'Traduzindo...';
  try {
    const res = await fetch('{{ route('provider.activities.ai.enhance-name') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
      body: JSON.stringify({ name }),
    });
    const data = await res.json();
    if (data.name) inp.value = data.name;
    else alert(data.error || 'Erro ao traduzir nome.');
  } catch { alert('Erro de conexão.'); }
  btn.disabled = false; btn.innerHTML = '✨ Traduzir com IA';
}

async function aiEnhanceDescription() {
  const ta = document.getElementById('f-description');
  const btn = document.getElementById('btn-enhance-desc');
  const desc = ta.value.trim();
  if (!desc) { alert('Adicione uma descrição primeiro.'); return; }
  btn.disabled = true; btn.textContent = 'Melhorando...';
  try {
    const res = await fetch('{{ route('provider.activities.ai.enhance') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
      body: JSON.stringify({ description: desc }),
    });
    const data = await res.json();
    if (data.description) ta.value = data.description;
    else alert(data.error || 'Erro ao melhorar descrição.');
  } catch { alert('Erro de conexão.'); }
  btn.disabled = false; btn.innerHTML = '✨ Melhorar com IA';
}

async function aiExtractHighlights() {
  const ta = document.getElementById('f-description');
  const raw = document.getElementById('f-highlights-raw');
  const btn = document.getElementById('btn-extract-hl');
  const desc = ta.value.trim();
  if (!desc) { alert('Adicione uma descrição primeiro.'); return; }
  btn.disabled = true; btn.textContent = 'Extraindo...';
  try {
    const res = await fetch('{{ route('provider.activities.ai.highlights') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
      body: JSON.stringify({ description: desc }),
    });
    const data = await res.json();
    if (data.highlights) raw.value = data.highlights.join('\n');
    else alert(data.error || 'Erro ao extrair destaques.');
  } catch { alert('Erro de conexão.'); }
  btn.disabled = false; btn.innerHTML = '✨ Extrair com IA';
}

// ── AJAX Submit ───────────────────────────────────────────────
document.getElementById('edit-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  clearErrors();

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .8s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Salvando...`;

  const hlRaw = document.getElementById('f-highlights-raw').value;
  const hlItems = hlRaw.split('\n').map(s => s.trim()).filter(Boolean);
  document.getElementById('f-highlights').value = JSON.stringify(hlItems);

  const formData = new FormData(this);
  formData.delete('photos[]');
  formData.delete('videos[]');
  const compressed = await compressPhotos(selectedFiles);
  compressed.forEach(f => formData.append('photos[]', f));
  selectedVideos.forEach(f => formData.append('videos[]', f));
  document.querySelectorAll('#ig-url-list input').forEach(inp => {
    const v = inp.value.trim();
    if (v) formData.append('media_urls[]', v);
  });

  try {
    const res = await fetch('{{ route('provider.activities.update', $activity) }}', {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
      body: formData,
    });

    const data = await res.json();

    if (res.ok) {
      btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Salvo!`;
      setTimeout(() => window.location.href = data.redirect, 600);
    } else if (res.status === 422) {
      showErrors(data.errors || {});
      btn.disabled = false;
      btn.innerHTML = 'Salvar alterações';
    } else {
      showGeneralError(data.message || 'Erro inesperado. Tente novamente.');
      btn.disabled = false;
      btn.innerHTML = 'Salvar alterações';
    }
  } catch {
    showGeneralError('Erro de conexão. Tente novamente.');
    btn.disabled = false;
    btn.innerHTML = 'Salvar alterações';
  }
});

function showErrors(errors) {
  Object.entries(errors).forEach(([key, msgs]) => {
    const direct = document.getElementById('err-' + key);
    if (direct) { direct.textContent = msgs[0]; direct.classList.add('show'); return; }
    const root = key.split('.')[0];
    const el = document.getElementById('err-' + root);
    if (el && !el.textContent) { el.textContent = msgs[0]; el.classList.add('show'); }
  });
  Object.keys(errors).forEach(key => {
    const inp = document.getElementById('f-' + key);
    if (inp) inp.classList.add('err');
  });
  document.querySelector('.f-error.show')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function clearErrors() {
  document.querySelectorAll('.f-error').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
  document.querySelectorAll('.f-input.err').forEach(el => el.classList.remove('err'));
  document.getElementById('general-error').classList.remove('show');
}
function showGeneralError(msg) {
  const el = document.getElementById('general-error');
  el.textContent = msg; el.classList.add('show');
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
// ── Custom questions ──────────────────────────────────────────
const CSRF = document.querySelector('[name=_token]').value;
const STORE_URL  = '{{ route('provider.activities.questions.store', $activity) }}';
const DESTROY_URL = (id) => `{{ route('provider.activities.questions.destroy', [$activity, '__ID__']) }}`.replace('__ID__', id);

function onQTypeChange(val) {
  document.getElementById('q-options-wrap').style.display = val === 'select' ? 'block' : 'none';
}

async function addQuestion() {
  const label    = document.getElementById('q-label').value.trim();
  const type     = document.getElementById('q-type').value;
  const required = document.getElementById('q-required').checked;
  const options  = document.getElementById('q-options').value.trim();

  if (!label) { alert('Escreva a pergunta antes de adicionar.'); return; }
  if (type === 'select' && !options) { alert('Adicione pelo menos uma opção.'); return; }

  const btn = document.getElementById('btn-add-question');
  btn.disabled = true; btn.textContent = 'Adicionando...';

  try {
    const res = await fetch(STORE_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ label, type, required, options }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.message || 'Erro ao adicionar.'); return; }

    // Render new question row
    const empty = document.getElementById('questions-empty');
    if (empty) empty.remove();

    const list = document.getElementById('questions-list');
    const div = document.createElement('div');
    div.className = 'question-row';
    div.id = 'qrow-' + data.id;
    div.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid #e8e8e8;border-radius:8px;gap:10px;';
    const typeLabel  = data.type === 'select' ? 'seleção' : 'texto livre';
    const reqLabel   = data.required ? ' · obrigatório' : '';
    const optsLabel  = data.type === 'select' && data.options?.length ? `<div style="font-size:11px;color:#999;margin-top:2px;">${data.options.join(', ')}</div>` : '';
    div.innerHTML = `
      <div style="flex:1;min-width:0;">
        <span style="font-size:13px;font-weight:500;color:#111;">${data.label}</span>
        <span style="font-size:11px;color:#aaa;margin-left:8px;">${typeLabel}${reqLabel}</span>
        ${optsLabel}
      </div>
      <button type="button" onclick="deleteQuestion(${data.id})"
        style="flex-shrink:0;width:28px;height:28px;border-radius:6px;border:1px solid #e8e8e8;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#bbb;transition:all .15s;"
        onmouseover="this.style.borderColor='#ff2924';this.style.color='#ff2924';"
        onmouseout="this.style.borderColor='#e8e8e8';this.style.color='#bbb';">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>`;
    list.appendChild(div);

    // Reset form
    document.getElementById('q-label').value = '';
    document.getElementById('q-type').value = 'text';
    document.getElementById('q-required').checked = true;
    document.getElementById('q-options').value = '';
    document.getElementById('q-options-wrap').style.display = 'none';
  } catch { alert('Erro de conexão.'); }
  finally { btn.disabled = false; btn.textContent = ''; btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Adicionar'; }
}

async function deleteQuestion(id) {
  if (!confirm('Remover esta pergunta?')) return;
  try {
    const res = await fetch(DESTROY_URL(id), {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    if (res.ok) {
      document.getElementById('qrow-' + id)?.remove();
      if (!document.querySelector('#questions-list .question-row')) {
        const p = document.createElement('p');
        p.id = 'questions-empty';
        p.style.cssText = 'font-size:13px;color:#aaa;margin:0;';
        p.textContent = 'Nenhuma pergunta adicionada ainda.';
        document.getElementById('questions-list').appendChild(p);
      }
    }
  } catch { alert('Erro de conexão.'); }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function() {
  const csrfToken = document.querySelector('[name=_token]').value;
  const reorderUrl = '{{ route('provider.activities.media.reorder', $activity) }}';

  function saveOrder(grid) {
    const ids = [...grid.querySelectorAll('[data-media-id]')].map(el => el.dataset.mediaId);
    if (ids.length < 2) return;
    fetch(reorderUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ ids }),
    });
  }

  const mediaGrid = document.getElementById('existing-media-grid');
  if (mediaGrid && mediaGrid.querySelectorAll('[data-media-id]').length > 1) {
    Sortable.create(mediaGrid, {
      animation: 150,
      ghostClass: 'sortable-ghost',
      dragClass: 'sortable-drag',
      onEnd: () => saveOrder(mediaGrid),
    });
  }
})();
</script>
@endpush

@endsection
