<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') · Envago</title>
<link rel="apple-touch-icon" sizes="57x57"   href="/apple-touch-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60"   href="/apple-touch-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72"   href="/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76"   href="/apple-touch-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
<link rel="apple-touch-icon"                 href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="196x196" href="/favicon-196x196.png">
<link rel="icon" type="image/png" sizes="128x128" href="/favicon-128.png">
<link rel="icon" type="image/png" sizes="96x96"   href="/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="32x32"   href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16"   href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#FF2924">
<meta name="msapplication-TileColor"          content="#FF2924">
<meta name="msapplication-TileImage"          content="/mstile-144x144.png">
<meta name="msapplication-config"             content="/browserconfig.xml">
<meta name="theme-color"                      content="#FF2924">
@include('partials._gtag')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --red: #FF2924;
    --dark: #111;
    --sidebar-w: 240px;
  }

  body { font-family: 'Inter', sans-serif; background: #f5f5f3; color: #111; display: flex; min-height: 100vh; }

  /* Sidebar */
  .sidebar {
    width: var(--sidebar-w);
    background: #fff;
    border-right: 1px solid #ece9e4;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
  }

  .sidebar-logo {
    padding: 24px 20px 18px;
    border-bottom: 1px solid #f0ede8;
  }
  .sidebar-role {
    font-size: 11px;
    color: #bbb;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-top: 5px;
  }

  .sidebar-nav {
    flex: 1;
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    gap: 1px;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 8px;
    color: #666;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 500;
    transition: background .12s, color .12s;
  }
  .nav-item:hover { background: #f5f5f3; color: #111; }
  .nav-item.active { background: #fff1f1; color: var(--red); }
  .nav-item svg { flex-shrink: 0; opacity: .6; }
  .nav-item.active svg { opacity: 1; }

  .sidebar-footer {
    padding: 12px 10px;
    border-top: 1px solid #f0ede8;
  }

  /* Main */
  .main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .topbar {
    background: #fff;
    border-bottom: 1px solid #ece9e4;
    padding: 0 32px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
  }
  .topbar-title { font-size: 16px; font-weight: 600; color: #111; }
  .topbar-user { font-size: 13px; color: #888; display: flex; align-items: center; gap: 8px; }
  .topbar-avatar {
    width: 32px; height: 32px; border-radius: 50%; background: var(--red);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px; font-weight: 600;
  }

  .content { padding: 32px; flex: 1; }

  /* Cards */
  .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 32px; }
  .stat-card { background: #fff; border-radius: 12px; padding: 20px 24px; border: 1px solid #ece9e4; }
  .stat-label { font-size: 12px; color: #999; font-weight: 500; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 8px; }
  .stat-value { font-size: 28px; font-weight: 700; color: #111; line-height: 1; }
  .stat-sub { font-size: 12px; color: #aaa; margin-top: 4px; }

  /* Table */
  .section { background: #fff; border-radius: 12px; border: 1px solid #ece9e4; overflow: hidden; margin-bottom: 24px; }
  .section-header { padding: 18px 24px; border-bottom: 1px solid #f0ede8; display: flex; align-items: center; justify-content: space-between; }
  .section-title { font-size: 15px; font-weight: 600; color: #111; }
  table { width: 100%; border-collapse: collapse; }
  th { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #999; letter-spacing: .8px; text-transform: uppercase; background: #fafaf8; border-bottom: 1px solid #f0ede8; }
  td { padding: 14px 16px; font-size: 14px; color: #333; border-bottom: 1px solid #f7f5f2; vertical-align: middle; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #fafaf8; }

  /* Badge */
  .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: .3px; }
  .badge-pending  { background: #fff8e1; color: #b45309; }
  .badge-approved { background: #e8f5e9; color: #2e7d32; }
  .badge-rejected { background: #fce4e4; color: #c62828; }
  .badge-confirmed { background: #e8f5e9; color: #2e7d32; }
  .badge-cancelled { background: #fce4e4; color: #c62828; }

  /* Buttons */
  .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: opacity .15s; }
  .btn:hover { opacity: .85; }
  .btn-primary { background: #111; color: #fff; }
  .btn-danger  { background: #FF2924; color: #fff; }
  .btn-ghost   { background: transparent; color: #555; border: 1.5px solid #e5e5e5; }

  /* Empty state */
  .empty { text-align: center; padding: 48px 24px; color: #aaa; }
  .empty-icon { font-size: 36px; margin-bottom: 12px; }
  .empty p { font-size: 14px; }

  /* Mobile sidebar */
  .sidebar-overlay {
    display: none;
    position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 99;
  }
  .hamburger {
    display: none;
    background: none; border: none; cursor: pointer; padding: 4px; color: #111;
  }

  @media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); transition: transform .25s ease; }
    .sidebar.open { transform: translateX(0); }
    .sidebar-overlay.open { display: block; }
    .main { margin-left: 0; }
    .hamburger { display: flex; align-items: center; }
    .content { padding: 20px 16px; }
    .topbar { padding: 0 16px; }
  }

  /* Profile dropdown */
  .topbar-user { position: relative; cursor: pointer; user-select: none; }
  .topbar-user:hover .topbar-avatar { opacity: .85; }
  .profile-dropdown {
    display: none;
    position: absolute; top: calc(100% + 8px); right: 0;
    background: #fff; border: 1px solid #ece9e4; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.1); min-width: 180px; overflow: hidden; z-index: 200;
  }
  .profile-dropdown.open { display: block; }
  .profile-dropdown-name {
    padding: 12px 16px; font-size: 13px; font-weight: 600; color: #111;
    border-bottom: 1px solid #f0ede8; pointer-events: none;
  }
  .profile-dropdown-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; font-size: 13px; color: #555;
    text-decoration: none; cursor: pointer; width: 100%;
    background: none; border: none; font-family: inherit; text-align: left;
    transition: background .1s;
  }
  .profile-dropdown-item:hover { background: #f7f5f2; color: #111; }
  .profile-dropdown-item.danger { color: #c62828; }
  .profile-dropdown-item.danger:hover { background: #fff5f5; }
</style>
@stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('provider.dashboard') }}">
      <img src="/logo.svg" alt="Envago" style="height:32px; width:auto;">
    </a>
    <div class="sidebar-role">{{ auth()->user()->isAdmin() ? 'Admin' : 'Operador' }}</div>
  </div>

  <nav class="sidebar-nav">
    @if(auth()->user()->isAdmin())
      <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="{{ route('admin.providers') }}" class="nav-item {{ request()->routeIs('admin.providers*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Operadores
      </a>
      <a href="{{ route('admin.payouts') }}" class="nav-item {{ request()->routeIs('admin.payouts*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Pagamentos
      </a>
      <a href="{{ route('admin.clicks') }}" class="nav-item {{ request()->routeIs('admin.clicks*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Leads & Cliques
      </a>
      <div style="padding:8px 12px 2px;font-size:10px;color:#ccc;letter-spacing:1px;text-transform:uppercase;font-weight:600;">Lara</div>
      <a href="{{ route('admin.lara.chat') }}" class="nav-item {{ request()->routeIs('admin.lara.chat') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Lara
      </a>
      <a href="{{ route('admin.lara.conversations') }}" class="nav-item {{ request()->routeIs('admin.lara.conversations*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 8h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="6" rx="1"/></svg>
        Conversas
      </a>
      <a href="{{ route('admin.scope-leads') }}" class="nav-item {{ request()->routeIs('admin.scope-leads') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Fora do Escopo
      </a>
      <a href="{{ route('admin.lara.instructions') }}" class="nav-item {{ request()->routeIs('admin.lara.instructions*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        Instruções
      </a>
      <div style="padding:8px 12px 2px;font-size:10px;color:#ccc;letter-spacing:1px;text-transform:uppercase;font-weight:600;">Conteúdo</div>
      <a href="{{ route('admin.articles') }}" class="nav-item {{ request()->routeIs('admin.articles*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        Artigos
      </a>
      <a href="{{ route('admin.stories') }}" class="nav-item {{ request()->routeIs('admin.stories*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/><line x1="9" y1="15" x2="12" y2="15"/></svg>
        Stories
      </a>
    @else
      <a href="{{ route('provider.dashboard') }}" class="nav-item {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="{{ route('provider.bookings') }}" class="nav-item {{ request()->routeIs('provider.bookings') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
        Reservas
      </a>
      <a href="{{ route('provider.activities') }}" class="nav-item {{ request()->routeIs('provider.activities') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Meus Passeios
      </a>
      <a href="{{ route('provider.payouts') }}" class="nav-item {{ request()->routeIs('provider.payouts*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Pagamentos
      </a>
      <a href="{{ route('provider.settings') }}" class="nav-item {{ request()->routeIs('provider.settings*') ? 'active' : '' }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Configurações
      </a>
    @endif
  </nav>

  <div class="sidebar-footer">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-item" style="width:100%; background:none; border:none; cursor:pointer; color:#999; font-family:inherit; text-align:left;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair
      </button>
    </form>
  </div>
</aside>

<div class="main">
  @if(session('impersonating_from'))
  <div style="background:#FF2924; color:#fff; text-align:center; padding:10px 16px; font-size:13px; font-weight:500; display:flex; align-items:center; justify-content:center; gap:16px;">
    <span>Você está vendo como <strong>{{ auth()->user()->name }}</strong></span>
    <form method="POST" action="{{ route('stop.impersonating') }}" style="margin:0;">
      @csrf
      <button type="submit" style="background:rgba(255,255,255,.2); border:none; color:#fff; padding:4px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">← Voltar para admin</button>
    </form>
  </div>
  @endif
  <div class="topbar">
    <div style="display:flex; align-items:center; gap:12px;">
      <button class="hamburger" onclick="openSidebar()" aria-label="Menu">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="topbar-title">@yield('page-title')</span>
    </div>
    <div class="topbar-user" onclick="toggleProfileDropdown()">
      <span style="font-size:13px; color:#888;">{{ auth()->user()->name }}</span>
      @php $__logo = auth()->user()->provider?->logo_url; @endphp
      <div class="topbar-avatar" style="{{ $__logo ? 'background:none;padding:0;overflow:hidden;' : '' }}">
        @if($__logo)
          <img src="{{ $__logo }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
        @else
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        @endif
      </div>
      <div class="profile-dropdown" id="profile-dropdown">
        <div class="profile-dropdown-name">{{ auth()->user()->name }}</div>
        @if(!auth()->user()->isAdmin())
        <a href="{{ route('provider.settings') }}" class="profile-dropdown-item">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          Configurações
        </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
          @csrf
          <button type="submit" class="profile-dropdown-item danger">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="content">
    @yield('content')
  </div>
</div>

@stack('scripts')
<script>
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebar-overlay').classList.add('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('open');
}
function toggleProfileDropdown() {
  document.getElementById('profile-dropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
  var user = e.target.closest('.topbar-user');
  if (!user) document.getElementById('profile-dropdown').classList.remove('open');
});
</script>

@php
$__laraUser     = auth()->user();
$__laraProv     = $__laraUser->provider;
if ($__laraProv) {
    $__laraActs    = $__laraProv->activities()->with('category')->where('is_active', true)->get();
    $__laraPayouts = \App\Models\Payout::where('provider_id', $__laraProv->id)->where('status', 'pending')->count();
    $__laraLines   = [
        'Operator: ' . $__laraProv->business_name,
        'Email: ' . $__laraUser->email,
        'City: ' . ($__laraProv->city ?? 'not set'),
        'Status: ' . $__laraProv->status,
        'Bank details: ' . ($__laraProv->bank_details ? 'provided' : 'NOT provided — operator cannot receive payouts yet'),
        'Logo: ' . ($__laraProv->logo_url ? 'uploaded' : 'NOT uploaded yet'),
        'Pending payouts awaiting transfer: ' . $__laraPayouts,
        'Active tours: ' . ($__laraActs->isEmpty() ? 'none' : $__laraActs->map(fn($a) => $a->name . ' (R$' . number_format($a->price, 0) . ', ' . ($a->category?->name ?? 'no category') . ')')->join(', ')),
    ];
    $__laraProviderInfo = implode("\n", $__laraLines);
} else {
    $__laraProviderInfo = null;
}
@endphp

@if(!auth()->user()->isAdmin())
<div id="lara-widget">
  <button id="lara-btn" onclick="laraOpen()" aria-label="Ajuda da Lara">
    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
  </button>

  <div id="lara-panel" style="display:none;">
    <div id="lara-header">
      <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div style="flex:1;">
        <div style="color:#fff;font-weight:600;font-size:14px;">Lara</div>
        <div style="color:rgba(255,255,255,.75);font-size:11px;">Envago · assistente</div>
      </div>
      <button onclick="laraClose()" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,.75);padding:4px;line-height:1;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div id="lara-messages"></div>
    <form id="lara-form" onsubmit="laraSend(event)">
      <textarea id="lara-input" placeholder="Escreva aqui..." rows="1" onkeydown="laraKeyDown(event)"></textarea>
      <button type="submit" id="lara-send">
        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </button>
    </form>
  </div>
</div>

<style>
  #lara-btn {
    position:fixed;bottom:24px;right:24px;z-index:1000;
    width:56px;height:56px;border-radius:50%;
    background:#FF2924;border:none;cursor:pointer;
    box-shadow:0 4px 20px rgba(255,41,36,.4);
    display:flex;align-items:center;justify-content:center;
    transition:transform .15s,box-shadow .15s;
  }
  #lara-btn:hover { transform:scale(1.07); }
  #lara-panel {
    position:fixed;bottom:24px;right:24px;z-index:1000;
    width:360px;height:540px;background:#fff;border-radius:16px;
    box-shadow:0 8px 40px rgba(0,0,0,.15);
    display:flex;flex-direction:column;overflow:hidden;
    animation:laraSlideUp .2s ease;
  }
  #lara-header {
    background:#FF2924;padding:14px 16px;
    display:flex;align-items:center;gap:10px;flex-shrink:0;
  }
  #lara-messages {
    flex:1;overflow-y:auto;padding:16px 12px;
    background:#f5f5f3;display:flex;flex-direction:column;gap:8px;
  }
  .lara-msg { display:flex; }
  .lara-msg.user { justify-content:flex-end; }
  .lara-msg.assistant { justify-content:flex-start; }
  .lara-bubble {
    max-width:80%;padding:9px 13px;border-radius:12px;
    font-size:13px;line-height:1.5;box-shadow:0 1px 3px rgba(0,0,0,.07);
    white-space:pre-wrap;
  }
  .lara-msg.user .lara-bubble { background:#FF2924;color:#fff;border-bottom-right-radius:3px; }
  .lara-msg.assistant .lara-bubble { background:#fff;color:#111;border-bottom-left-radius:3px; }
  .lara-typing { display:flex;gap:4px;align-items:center;padding:10px 14px;background:#fff;border-radius:12px;border-bottom-left-radius:3px;box-shadow:0 1px 3px rgba(0,0,0,.07); }
  .lara-dot { width:6px;height:6px;border-radius:50%;background:#ccc;display:inline-block; }
  .lara-dot:nth-child(1){animation:laraDot .9s 0s infinite;}
  .lara-dot:nth-child(2){animation:laraDot .9s .15s infinite;}
  .lara-dot:nth-child(3){animation:laraDot .9s .3s infinite;}
  #lara-form {
    padding:10px 12px;background:#fff;border-top:1px solid #f0ede8;
    display:flex;gap:8px;align-items:flex-end;flex-shrink:0;
  }
  #lara-input {
    flex:1;resize:none;border:1.5px solid #e5e5e5;border-radius:10px;
    padding:8px 12px;font-size:13px;font-family:inherit;outline:none;
    line-height:1.5;max-height:80px;overflow-y:auto;
  }
  #lara-input:focus { border-color:#FF2924; }
  #lara-send {
    width:36px;height:36px;border-radius:50%;background:#FF2924;
    border:none;cursor:pointer;display:flex;align-items:center;
    justify-content:center;flex-shrink:0;transition:background .15s;
  }
  @keyframes laraSlideUp { from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);} }
  @keyframes laraDot { 0%,60%,100%{transform:translateY(0);opacity:.4;}30%{transform:translateY(-4px);opacity:1;} }
</style>

<script>
(function() {
  var providerInfo = @json($__laraProviderInfo);

  var sessionId = localStorage.getItem('lara_session_id');
  if (!sessionId) { sessionId = ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,function(c){return(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16);}); localStorage.setItem('lara_session_id', sessionId); }

  var typing = false;
  var initialized = false;
  window.laraOpen = function() {
    document.getElementById('lara-btn').style.display = 'none';
    document.getElementById('lara-panel').style.display = 'flex';
    document.getElementById('lara-panel').style.flexDirection = 'column';
    setTimeout(function(){ document.getElementById('lara-input').focus(); }, 300);
    if (!initialized) { initialized = true; var h = new Date().getHours(); var saud = h < 12 ? 'Bom dia' : h < 18 ? 'Boa tarde' : 'Boa noite'; appendMsg('assistant', saud + '! Sou a Lara. Como posso ajudar?'); }
  };

  window.laraClose = function() {
    document.getElementById('lara-panel').style.display = 'none';
    document.getElementById('lara-btn').style.display = 'flex';
  };

  window.laraSend = function(e) {
    e.preventDefault();
    var text = document.getElementById('lara-input').value.trim();
    if (!text || typing) return;
    document.getElementById('lara-input').value = '';
    laraSendMsg(text, false);
  };

  window.laraKeyDown = function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); window.laraSend(e); }
  };

  function laraSendMsg(text, isInit) {
    if (!isInit) appendMsg('user', text);
    typing = true;
    var typingEl = appendTyping();

    fetch('/api/lara/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ session_id: sessionId, message: text, context: 'provider_dashboard', provider_info: providerInfo })
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      var delay = Math.min(Math.max(data.reply.length * 40, 1000), 4000);
      setTimeout(function() {
        typingEl.remove();
        typing = false;
        appendMsg('assistant', data.reply);
      }, delay);
    })
    .catch(function(){ typingEl.remove(); typing = false; });
  }

  function appendMsg(role, text) {
    var msgs = document.getElementById('lara-messages');
    var row = document.createElement('div');
    row.className = 'lara-msg ' + role;
    var bubble = document.createElement('div');
    bubble.className = 'lara-bubble';
    bubble.textContent = text;
    row.appendChild(bubble);
    msgs.appendChild(row);
    msgs.scrollTop = msgs.scrollHeight;
    return row;
  }

  function appendTyping() {
    var msgs = document.getElementById('lara-messages');
    var row = document.createElement('div');
    row.className = 'lara-msg assistant';
    var inner = document.createElement('div');
    inner.className = 'lara-typing';
    inner.innerHTML = '<span class="lara-dot"></span><span class="lara-dot"></span><span class="lara-dot"></span>';
    row.appendChild(inner);
    msgs.appendChild(row);
    msgs.scrollTop = msgs.scrollHeight;
    return row;
  }
})();
</script>
@endif
</body>
</html>
