@extends('layouts.dashboard')
@section('title', 'Novo Passeio')
@section('page-title', 'Novo Passeio')

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
.preview-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#166534;display:flex;align-items:center;gap:10px; }

/* Photos */
.photo-drop { border:2px dashed #e0e0e0;border-radius:12px;padding:32px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa; }
.photo-drop:hover,.photo-drop.drag-over { border-color:#111;background:#fff; }
.photo-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;margin-top:12px; }
.photo-thumb { position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;border:1px solid #e8e8e8;background:#f5f5f5; }
.photo-thumb img { width:100%;height:100%;object-fit:cover; }
.photo-thumb-remove { position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(0,0,0,.55);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s; }
.photo-thumb-remove:hover { background:rgba(220,38,38,.9); }
.photo-first-badge { position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,.6);color:#fff;font-size:9px;font-weight:600;padding:2px 6px;border-radius:4px;letter-spacing:.3px; }

/* Actions */
.form-actions { display:flex;gap:10px;align-items:center;margin-top:4px; }
.btn-submit { display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:10px;border:none;background:#111;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:opacity .15s,transform .1s;flex:1;justify-content:center; }
.btn-submit:hover:not(:disabled) { opacity:.87; }
.btn-submit:active:not(:disabled) { transform:scale(.98); }
.btn-submit:disabled { opacity:.5;cursor:not-allowed; }
.btn-back { display:inline-flex;align-items:center;gap:6px;padding:12px 18px;border-radius:10px;border:1px solid #e8e8e8;background:#fff;color:#555;font-size:14px;font-weight:500;cursor:pointer;text-decoration:none;transition:background .12s; }
.btn-back:hover { background:#f5f5f5; }

.general-error { background:#fff5f5;border:1px solid #fca5a5;border-radius:10px;padding:14px 20px;margin-bottom:16px;font-size:13px;color:#c62828;display:none; }
.general-error.show { display:block; }

@media(max-width:480px){
  .f-col2,.f-col3-1{grid-template-columns:1fr;}
  .step-head{padding:16px 16px 0;}
  .step-body{padding:16px;}
}
</style>
@endpush

@section('content')

<div id="general-error" class="general-error create-wrap"></div>

<form id="create-form" class="create-wrap" novalidate>
  @csrf

  {{-- Step 1 --}}
  <div class="step-card">
    <div class="step-head">
      <div class="step-num">1</div>
      <div><div class="step-title">Detalhes do passeio</div><div class="step-desc">Nome, categoria, preço e duração</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">

      <div class="f-group">
        <label class="f-label">Nome do passeio <span style="color:#ff2924">*</span></label>
        <input type="text" name="name" placeholder="ex: Passeio de Buggy nas Dunas"
          class="f-input" maxlength="120" autocomplete="off" id="f-name">
        <span class="f-error" id="err-name"></span>
      </div>

      <div class="f-col2">
        <div class="f-group">
          <label class="f-label">Categoria</label>
          <select name="category_id" class="f-input" id="f-category_id">
            <option value="">Sem categoria</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
          <span class="f-error" id="err-category_id"></span>
        </div>
        <div class="f-group">
          <label class="f-label">Preço por pessoa <span style="color:#ff2924">*</span></label>
          <div class="f-prefix-wrap">
            <span class="f-prefix">R$</span>
            <input type="number" name="price" placeholder="150"
              class="f-input" min="1" max="99999" step="1" id="f-price">
          </div>
          <span class="f-error" id="err-price"></span>
        </div>
      </div>

      <div class="f-col2">
        <div class="f-group">
          <label class="f-label">Duração <span style="color:#ff2924">*</span></label>
          <div style="position:relative;">
            <input type="number" name="duration_minutes" placeholder="90"
              class="f-input" min="15" max="1440" style="padding-right:44px;" id="f-duration_minutes">
            <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:12px;color:#aaa;pointer-events:none;">min</span>
          </div>
          <span class="f-error" id="err-duration_minutes"></span>
        </div>
        <div class="f-group">
          <label class="f-label">Vagas por horário <span style="color:#ff2924">*</span></label>
          <input type="number" name="capacity" placeholder="4"
            class="f-input" min="1" max="999" id="f-capacity">
          <span class="f-hint">Quantas pessoas cabem por saída</span>
          <span class="f-error" id="err-capacity"></span>
        </div>
      </div>

      <div class="f-col2">
        <div class="f-group">
          <label class="f-label">Cidade <span style="color:#ff2924">*</span></label>
          <input type="text" name="city" placeholder="ex: Jericoacoara"
            class="f-input" maxlength="80" id="f-city">
          <span class="f-error" id="err-city"></span>
        </div>
        <div class="f-group">
          <label class="f-label">Estado <span style="color:#ff2924">*</span></label>
          <input type="text" name="state" placeholder="CE"
            class="f-input" maxlength="2" style="text-transform:uppercase;" id="f-state">
          <span class="f-error" id="err-state"></span>
        </div>
      </div>

      <div class="f-group">
        <label class="f-label">Descrição</label>
        <textarea name="description" rows="3" class="f-input"
          placeholder="O que o turista vai ver e fazer, o que está incluído, ponto de encontro..."
          style="resize:vertical;line-height:1.5;" maxlength="2000" id="f-description"></textarea>
        <span class="f-error" id="err-description"></span>
      </div>

      <div class="f-group">
        <label class="f-label">Ponto de encontro</label>
        <input type="text" name="meeting_point" id="meeting-point-input" class="f-input"
          placeholder="ex: Estacionamento da Duna do Pôr do Sol, Jericoacoara, CE"
          autocomplete="off">
        <input type="hidden" name="meeting_point_lat" id="meeting-point-lat">
        <input type="hidden" name="meeting_point_lng" id="meeting-point-lng">
        <span class="f-hint">Onde o turista deve se apresentar. <a id="meeting-point-map-link" href="#" target="_blank" rel="noopener" style="color:#111;font-weight:500;display:none;">Ver no mapa ↗</a></span>
        <span class="f-error" id="err-meeting_point"></span>
      </div>

    </div>
  </div>

  {{-- Step 2 --}}
  <div class="step-card">
    <div class="step-head">
      <div class="step-num">2</div>
      <div><div class="step-title">Horários</div><div class="step-desc">Adicione quantas combinações de dias + horas quiser</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">
      <div id="groups-wrap"></div>
      <button type="button" class="add-group-btn" onclick="addGroup()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Adicionar outra combinação de dias
      </button>
      <span class="f-error" id="err-groups"></span>
    </div>
  </div>

  {{-- Step 3 --}}
  <div class="step-card">
    <div class="step-head">
      <div class="step-num">3</div>
      <div><div class="step-title">Fotos <span style="color:#ff2924">*</span></div><div class="step-desc">Mínimo 1 foto. A primeira será a capa da página.</div></div>
    </div>
    <div class="step-divider"></div>
    <div class="step-body">
      <input type="file" name="photos[]" id="photo-input" accept="image/*" multiple style="display:none;"
        onchange="onPhotosSelected(this)">
      <div class="photo-drop" id="photo-drop"
        onclick="document.getElementById('photo-input').click()"
        ondragover="event.preventDefault();this.classList.add('drag-over')"
        ondragleave="this.classList.remove('drag-over')"
        ondrop="onPhotoDrop(event)">
        <svg width="32" height="32" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p style="font-size:14px;font-weight:500;color:#555;margin:0 0 4px;">Clique para escolher fotos</p>
        <p style="font-size:12px;color:#aaa;margin:0;">ou arraste aqui · JPG, PNG · máx. 8 fotos · 5 MB cada</p>
      </div>
      <div class="photo-grid" id="photo-grid"></div>
      <span class="f-error" id="err-photos"></span>
    </div>
  </div>

  <div class="form-actions">
    <a href="{{ route('provider.activities') }}" class="btn-back">Cancelar</a>
    <button type="submit" class="btn-submit" id="submit-btn">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
      Criar passeio
    </button>
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
// ── Google Places autocomplete ────────────────────────────────
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

// ── Schedule groups ──────────────────────────────────────────
const DAY_LABELS = [['D','Dom'],['S','Seg'],['T','Ter'],['Q','Qua'],['Q','Qui'],['S','Sex'],['S','Sáb']];
let groupSeq = 0;
const groups = {};

addGroup([1,2,3,4,5], ['08:00']);

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
  updatePreview();
}

function removeGroup(id) {
  if (Object.keys(groups).length <= 1) return;
  delete groups[id];
  document.getElementById('g-' + id)?.remove();
  updatePreview();
}

function toggleDay(btn, id) {
  const d = +btn.dataset.day;
  groups[id].days.has(d) ? (groups[id].days.delete(d), btn.classList.remove('on'))
                         : (groups[id].days.add(d),    btn.classList.add('on'));
  updateDayInputs(id);
  updatePreview();
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
    <input type="time" name="groups[${id}][times][]" value="${val||''}" class="f-input" required onchange="updatePreview()">
    <button type="button" class="time-chip-remove" onclick="removeTime(${id},${ts})">
      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  document.getElementById('times-' + id)?.appendChild(chip);
  updatePreview();
}

function removeTime(id, ts) {
  const list = document.getElementById('times-' + id);
  if (!list || list.children.length <= 1) return;
  document.getElementById(`tc-${id}-${ts}`)?.remove();
  updatePreview();
}

function updatePreview() {}

// ── Photos ───────────────────────────────────────────────────
let selectedFiles = [];

function onPhotosSelected(input) { addFiles([...input.files]); input.value = ''; }

function onPhotoDrop(e) {
  e.preventDefault();
  document.getElementById('photo-drop').classList.remove('drag-over');
  addFiles([...e.dataTransfer.files].filter(f => f.type.startsWith('image/')));
}

function addFiles(files) {
  files.slice(0, 8 - selectedFiles.length).forEach(file => {
    const idx = selectedFiles.push(file) - 1;
    const reader = new FileReader();
    reader.onload = e => addThumb(idx, e.target.result, file.name);
    reader.readAsDataURL(file);
  });
  syncPhotoInput();
}

function addThumb(idx, src, name) {
  const grid = document.getElementById('photo-grid');
  const div = document.createElement('div');
  div.className = 'photo-thumb'; div.id = 'pt-' + idx;
  div.innerHTML = `
    <img src="${src}" alt="${name}">
    ${idx === 0 ? '<span class="photo-first-badge">CAPA</span>' : ''}
    <button type="button" class="photo-thumb-remove" onclick="removePhoto(${idx})">
      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  grid.appendChild(div);
}

function removePhoto(idx) {
  selectedFiles.splice(idx, 1);
  const grid = document.getElementById('photo-grid'); grid.innerHTML = '';
  selectedFiles.forEach((f, i) => { const r = new FileReader(); r.onload = e => addThumb(i, e.target.result, f.name); r.readAsDataURL(f); });
  syncPhotoInput();
}

function syncPhotoInput() {
  const dt = new DataTransfer();
  selectedFiles.forEach(f => dt.items.add(f));
  document.getElementById('photo-input').files = dt.files;
  document.getElementById('photo-drop').style.display = selectedFiles.length >= 8 ? 'none' : 'block';
}

// ── AJAX Submit ──────────────────────────────────────────────
document.getElementById('create-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  clearErrors();

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .8s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Criando...`;

  const formData = new FormData(this);
  formData.delete('photos[]');
  const compressed = await compressPhotos(selectedFiles);
  compressed.forEach(f => formData.append('photos[]', f));

  try {
    const res = await fetch('{{ route('provider.activities.store') }}', {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
      body: formData,
    });

    const data = await res.json();

    if (res.ok) {
      btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Criado!`;
      setTimeout(() => window.location.href = data.redirect, 600);
    } else if (res.status === 422) {
      showErrors(data.errors || {});
      btn.disabled = false;
      btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg> Criar passeio`;
    } else {
      showGeneralError(data.message || 'Erro inesperado. Tente novamente.');
      btn.disabled = false;
      btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg> Criar passeio`;
    }
  } catch {
    showGeneralError('Erro de conexão. Tente novamente.');
    btn.disabled = false;
    btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg> Criar passeio`;
  }
});

function showErrors(errors) {
  Object.entries(errors).forEach(([key, msgs]) => {
    const direct = document.getElementById('err-' + key);
    if (direct) { direct.textContent = msgs[0]; direct.classList.add('show'); return; }
    // Nested: groups.1.days → err-groups, photos.0 → err-photos
    const root = key.split('.')[0];
    const el = document.getElementById('err-' + root);
    if (el && !el.textContent) { el.textContent = msgs[0]; el.classList.add('show'); }
  });
  // Mark inputs with errors
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
@endpush

@endsection
