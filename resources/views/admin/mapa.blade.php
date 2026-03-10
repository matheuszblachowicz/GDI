@extends('layouts.main')

@section('title', 'Global Device Intelligence | Monitoramento NOC')
@section('header', 'Centro de Monitoramento de Ativos')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; margin: 0; overflow: hidden; }
    main { padding: 0 !important; }

    .map-container {
        position: relative;
        width: 100%;
        height: calc(100vh - 70px); 
        overflow: hidden;
        background-color: #020617;
    }

    #map { width: 100%; height: 100%; z-index: 0; }

    /* FONTE DO TERMINAL */
    .font-hud { font-family: 'JetBrains Mono', monospace; }

    /* TICKER (LETREIRO NOC) */
    .kiosk-ticker {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 36px;
        background: rgba(15, 23, 42, 0.9);
        border-bottom: 1px solid rgba(239, 68, 68, 0.4);
        z-index: 1000;
        display: flex;
        align-items: center;
        overflow: hidden;
        transform: translateY(-100%);
        opacity: 0;
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        backdrop-filter: blur(10px);
    }
    .ticker-label {
        background: #991b1b;
        color: white;
        font-weight: 800;
        font-size: 11px;
        padding: 0 16px;
        height: 100%;
        display: flex;
        align-items: center;
        box-shadow: 10px 0 20px rgba(0,0,0,0.5);
        z-index: 2;
        letter-spacing: 0.1em;
    }
    .ticker-content {
        color: #fca5a5;
        font-size: 12px;
        white-space: nowrap;
        animation: ticker-slide 40s linear infinite;
        padding-left: 20px;
    }
    @keyframes ticker-slide {
        0% { transform: translateX(100vw); }
        100% { transform: translateX(-100%); }
    }

    /* SIDEBAR E SCROLLBAR */
    .floating-sidebar {
        position: absolute; top: 16px; left: 16px; width: 320px; max-height: calc(100% - 32px);
        background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px;
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.5); z-index: 1000;
        display: flex; flex-direction: column; color: white;
    }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.4); }

    /* MARCADORES MAPA */
    .modern-marker {
        display: flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 50%; color: white; font-weight: 800; font-size: 11px; 
        border: 2px solid rgba(255, 255, 255, 0.9); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5), inset 0 -2px 4px rgba(0,0,0,0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); position: relative;
    }
    .modern-marker::after {
        content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%);
        width: 5px; height: 5px; border-radius: 50%; background: white; box-shadow: 0 0 8px rgba(255,255,255,0.8);
    }
    .modern-marker:hover { transform: translateY(-3px) scale(1.15); z-index: 1000 !important; }
    .status-healthy { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .status-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .status-critical { background: linear-gradient(135deg, #ef4444, #b91c1c); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); animation: pulse-critical 2s infinite; }
    @keyframes pulse-critical { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); transform: scale(0.95); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); transform: scale(1); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); transform: scale(0.95); } }

    /* POPUP LEAFLET */
    .leaflet-popup-content-wrapper {
        background: rgba(15, 23, 42, 0.85) !important; backdrop-filter: blur(20px) !important; -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 16px !important; padding: 0 !important;
        overflow: hidden; color: white !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }
    .leaflet-popup-tip { background: rgba(15, 23, 42, 0.85) !important; border: 1px solid rgba(255,255,255,0.1); }
    .leaflet-popup-content { margin: 0 !important; width: 300px !important; }
    .leaflet-container a.leaflet-popup-close-button { color: rgba(255,255,255,0.7) !important; padding: 12px !important; z-index: 10; font-size: 18px !important; }
    .leaflet-control-attribution { display: none !important; }

    /* SVG GAUGE PROGRESS */
    .circular-chart { display: block; margin: 0 auto; max-width: 100%; max-height: 40px; }
    .circle-bg { fill: none; stroke: rgba(255, 255, 255, 0.1); stroke-width: 3.8; }
    .circle { fill: none; stroke-width: 2.8; stroke-linecap: round; transition: stroke-dasharray 1s ease-out; }
    .color-green { stroke: #10b981; }
    .color-amber { stroke: #f59e0b; }
    .color-red { stroke: #ef4444; }

    #street-view-container {
        position: absolute; bottom: 20px; right: 20px; width: 400px; height: 260px;
        border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 30px 60px rgba(0,0,0,0.6);
        z-index: 2000; display: none; overflow: hidden;
    }
    #street-view-container:fullscreen { width: 100vw; height: 100vh; border: none; border-radius: 0; bottom: 0; right: 0; }
</style>
@endpush

@section('content')
<div class="map-container" id="mainMapContainer">
    <div id="map"></div>

    <div id="kioskTicker" class="kiosk-ticker font-hud">
        <div class="ticker-label">SATCOM LINK</div>
        <div class="ticker-content" id="tickerText">Aguardando dados da frota...</div>
    </div>
    
    <button id="restoreSidebarBtn" onclick="toggleSidebar()" title="Mostrar Painel de Pesquisa"
            class="absolute top-4 left-4 z-[999] w-10 h-10 bg-slate-900/80 backdrop-blur border border-white/10 rounded-xl text-white flex items-center justify-center shadow-lg hover:bg-slate-800 transition-all duration-500 opacity-0 pointer-events-none transform -translate-x-10">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </button>

    <div id="mainSidebar" class="floating-sidebar p-4 transition-all duration-500 ease-in-out">
        <div class="flex items-start justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30 border border-white/10 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="overflow-hidden">
                    <h3 class="text-lg font-extrabold text-white tracking-tight m-0 truncate">Rastreamento Por Satelite</h3>
                    <p class="text-[9px] text-blue-300 font-semibold uppercase tracking-widest mt-0.5 truncate">Gestão de Ativos</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button onclick="toggleKioskMode()" title="Modo NOC (Tela Cheia)" class="p-1.5 text-slate-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors border border-transparent hover:border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                </button>
                <button onclick="toggleSidebar()" title="Esconder Painel" class="p-1.5 text-slate-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors border border-transparent hover:border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
            </div>
        </div>

        <div class="relative mb-4">
            <input type="text" id="filterInput" placeholder="Procurar local..." 
                   class="w-full pl-10 pr-3 py-2.5 bg-white/5 border border-white/10 rounded-lg text-xs text-white placeholder-slate-400 focus:border-blue-400 focus:bg-white/10 focus:ring-1 focus:ring-blue-400 outline-none transition-all shadow-inner backdrop-blur-sm">
            <svg class="w-4 h-4 absolute left-3.5 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>

        <div id="sidebarList" class="flex-1 overflow-y-auto space-y-2.5 pr-1.5 pb-2"></div>
    </div>

    <div id="kioskLocationList" class="absolute top-12 right-6 w-56 max-h-[calc(100vh-180px)] z-[1000] bg-slate-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 shadow-2xl transition-all duration-700 ease-out opacity-0 translate-x-12 pointer-events-none flex flex-col">
        <div class="flex items-center gap-2 mb-3 border-b border-white/10 pb-3">
            <svg class="w-4 h-4 text-blue-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <h4 class="text-white text-[10px] font-extrabold tracking-widest uppercase m-0">Zonas Monitoradas</h4>
        </div>
        <div id="kioskListContent" class="overflow-y-auto space-y-2 pr-1 flex-1 font-hud"></div>
    </div>

    <div id="kioskLegend" class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-[1000] bg-slate-900/85 backdrop-blur-xl border border-white/10 rounded-2xl py-3 px-6 lg:px-8 shadow-[0_20px_50px_-10px_rgba(0,0,0,0.7)] transition-all duration-700 ease-out opacity-0 translate-y-12 pointer-events-none flex flex-wrap items-center justify-between gap-6 lg:gap-8 min-w-[700px]">
        
        <div class="flex items-center gap-4 border-r border-white/10 pr-6 lg:pr-8">
            <div class="relative w-10 h-10">
                <svg viewBox="0 0 36 36" class="circular-chart color-green" id="gaugeSvg">
                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="circle" id="gaugePath" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-[10px] font-hud font-bold text-white" id="gaugeText">0%</span>
                </div>
            </div>
            <div>
                <span class="block text-[9px] text-slate-400 uppercase tracking-widest mb-0.5">Global Score</span>
                <span class="text-sm font-bold text-white tracking-wide" id="complianceStatusText">Calculando...</span>
            </div>
        </div>

        <div class="flex items-center gap-5 text-[11px] font-semibold text-slate-300">
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_8px_#3b82f6]"></span>Compliance Saudavel</div>
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_#f59e0b]"></span> Aviso( < 80%)</div>
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_#ef4444] animate-pulse"></span> Crítico</div>
        </div>

        <div class="flex items-center gap-6 pl-6 lg:pl-8 border-l border-white/10 font-hud">
            <div>
                <span class="flex items-center gap-1.5 text-[9px] text-slate-400 mb-1 uppercase tracking-widest"><div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div> Sessões Ativas</span>
                <span class="text-base font-bold text-white leading-none"><span id="hudActive" class="text-emerald-400">0</span> <span class="text-slate-500 text-xs">/ <span id="hudTotal">0</span></span></span>
            </div>
            <div>
                <span class="block text-[9px] text-red-400/80 mb-1 uppercase tracking-widest">Bloqueios</span>
                <span id="hudBlocked" class="text-base font-bold text-red-500 leading-none">0</span>
            </div>
        </div>
    </div>

    <div id="street-view-container">
        <div id="pano" style="width: 100%; height: 100%;"></div>
        <div class="absolute top-3 right-3 flex gap-2 z-[2001]">
            <button onclick="toggleStreetViewFullscreen()" class="bg-black/50 hover:bg-black/80 backdrop-blur border border-white/20 text-white rounded-lg w-8 h-8 flex items-center justify-center transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg></button>
            <button onclick="closeStreetView()" class="bg-red-500/80 hover:bg-red-500 backdrop-blur border border-red-400 text-white rounded-lg w-8 h-8 flex items-center justify-center transition-all shadow-md"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_KEY') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const locations = @json($locations);
    let panorama = null;
    let streetViewService = null;
    let mapInstance = null;
    let leafletMarkers = {}; 

    document.addEventListener("DOMContentLoaded", function() {
        
        // -----------------------------------------------------
        // PROCESSAMENTO DE DADOS (KPIs, TICKER, GAUGE)
        // -----------------------------------------------------
        let totalDevices = 0;
        let blockedDevices = 0;
        let activeUsers = 0;
        let totalComplianceSum = 0;
        let devicesWithComplianceData = 0;
        let alertMessages = [];

        locations.forEach(loc => {
            totalDevices += loc.total;
            
            loc.machines.forEach(m => {
                if (m.blocked) {
                    blockedDevices++;
                    alertMessages.push(`[🚨 ALERTA] Máquina bloqueada: ${m.hostname} (${m.ip || 'S/ IP'}) na zona de ${loc.city || 'Desconhecida'}. Requer intervenção.`);
                }
                
                if (m.user && m.user !== 'Sem registo' && m.user.trim() !== '') {
                    activeUsers++;
                }

                if (m.compliance !== undefined) {
                    totalComplianceSum += m.compliance;
                    devicesWithComplianceData++;
                    if (m.compliance < 80 && !m.blocked) {
                        alertMessages.push(`[⚠️ AVISO] Violação de Política: ${m.hostname} com nível crítico de conformidade (${m.compliance}%).`);
                    }
                }
            });
        });

        // Atualiza Ticker de Texto
        const tickerEl = document.getElementById('tickerText');
        if (alertMessages.length === 0) {
            tickerEl.innerHTML = `<span class="text-emerald-400">[✔️ SISTEMA NOMINAL] Varredura concluída. 0 anomalias detectadas. Todas as diretrizes de segurança aplicadas com sucesso. Frota a operar em capacidade ótima.</span>`;
        } else {
            let joinedMsgs = alertMessages.join(' &nbsp; &nbsp; | &nbsp; &nbsp; ');
            tickerEl.innerHTML = `${joinedMsgs} &nbsp; &nbsp; | &nbsp; &nbsp; ${joinedMsgs}`;
        }

        // Atualiza HUD Metrics
        document.getElementById('hudTotal').innerText = totalDevices;
        document.getElementById('hudActive').innerText = activeUsers;
        document.getElementById('hudBlocked').innerText = blockedDevices;

        // Atualiza Gráfico de Compliance Global
        let globalCompliance = devicesWithComplianceData > 0 ? Math.round(totalComplianceSum / devicesWithComplianceData) : 100;
        document.getElementById('gaugeText').innerText = globalCompliance + '%';
        document.getElementById('gaugePath').setAttribute('stroke-dasharray', `${globalCompliance}, 100`);
        
        let gaugeSvg = document.getElementById('gaugeSvg');
        let statusText = document.getElementById('complianceStatusText');
        
        gaugeSvg.classList.remove('color-green', 'color-amber', 'color-red');
        if (globalCompliance >= 80) {
            gaugeSvg.classList.add('color-green');
            statusText.innerText = "Saudável";
            statusText.className = "text-sm font-bold text-emerald-400 tracking-wide";
        } else if (globalCompliance >= 50) {
            gaugeSvg.classList.add('color-amber');
            statusText.innerText = "Aviso";
            statusText.className = "text-sm font-bold text-amber-400 tracking-wide";
        } else {
            gaugeSvg.classList.add('color-red');
            statusText.innerText = "Crítico";
            statusText.className = "text-sm font-bold text-red-500 tracking-wide animate-pulse";
        }

        // -----------------------------------------------------
        // INICIALIZAÇÃO DO MAPA
        // -----------------------------------------------------
        mapInstance = L.map('map', { zoomControl: false, attributionControl: false }).setView([15, 0], 3);
        L.control.zoom({ position: 'bottomleft' }).addTo(mapInstance);
        L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] }).addTo(mapInstance);

        streetViewService = new google.maps.StreetViewService();
        panorama = new google.maps.StreetViewPanorama(document.getElementById("pano"), { visible: false, addressControl: false, linksControl: false, panControl: false, enableCloseButton: false });

        const bounds = [];

        locations.forEach((loc, index) => {
            const lat = parseFloat(loc.lat);
            const lng = parseFloat(loc.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            bounds.push([lat, lng]);

            let isCritical = loc.type === 'blocked' || loc.machines.some(m => m.blocked);
            let isWarning = loc.type === 'low_compliance' || loc.machines.some(m => m.compliance !== undefined && m.compliance < 80);
            
            let markerClass = isCritical ? 'status-critical' : (isWarning ? 'status-warning' : 'status-healthy');
            let headerBgColor = isCritical ? 'bg-gradient-to-r from-red-600 to-red-800' : (isWarning ? 'bg-gradient-to-r from-amber-500 to-orange-600' : 'bg-gradient-to-r from-blue-600 to-indigo-700');

            const marker = L.marker([lat, lng], {
                icon: L.divIcon({ className: '', html: `<div class="modern-marker ${markerClass}">${loc.total}</div>`, iconSize: [32, 42], iconAnchor: [16, 42] })
            }).addTo(mapInstance);

            let machinesHtml = loc.machines.map(m => {
                let mStatus = m.blocked ? 'CRÍTICO' : 'ONLINE';
                let mStatusColor = m.blocked ? 'text-red-400 bg-red-400/10 border-red-400/30' : 'text-emerald-400 bg-emerald-400/10 border-emerald-400/30';
                let userText = (m.user && m.user !== 'Sem registo') ? `👦 ${m.user}` : `⚪ Inativo`;
                let compText = m.compliance !== undefined ? `<span class="text-slate-400 pl-1.5 border-l border-white/10 ml-1.5">Comp: <span class="text-white">${m.compliance}%</span></span>` : '';
                
                return `
                <div class="flex items-center justify-between p-2.5 bg-white/5 hover:bg-white/10 border border-white/5 rounded-lg mb-2 transition-colors group">
                    <div class="flex-1 overflow-hidden">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[12px] font-bold text-white truncate group-hover:text-blue-300 transition-colors" title="${m.hostname}">${m.hostname}</span>
                            <span class="text-[8px] font-extrabold px-1.5 py-0.5 rounded border ${mStatusColor}">${mStatus}</span>
                        </div>
                        <div class="flex items-center text-[10px] text-slate-400 mb-0.5">
                            <span class="truncate">${userText}</span>
                        </div>
                        <div class="flex items-center text-[9px] text-slate-500 font-hud">
                            <span>IP: <span class="text-slate-300">${m.ip || '---'}</span></span>
                            ${compText}
                        </div>
                    </div>
                    <button onclick="openStreetView(${lat}, ${lng})" class="ml-2 p-1.5 bg-white/5 hover:bg-blue-500 text-slate-300 hover:text-white rounded-md transition-all border border-white/10" title="Ver Local">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            `}).join('');

            marker.bindPopup(`
                <div class="flex flex-col w-full">
                    <div class="${headerBgColor} p-4 text-white flex justify-between items-center shadow-md">
                        <h4 class="text-sm font-bold m-0 truncate pr-2 tracking-wide">${loc.city || 'Desconhecido'}</h4>
                        <span class="text-[9px] font-black bg-black/30 backdrop-blur-sm px-2 py-1 rounded border border-white/20 shadow-inner">${loc.total} DISP</span>
                    </div>
                    <div class="p-3 bg-transparent max-h-[250px] overflow-y-auto">${machinesHtml}</div>
                </div>
            `);
            leafletMarkers[index] = marker;
        });

        if (bounds.length > 0) mapInstance.fitBounds(bounds, { padding: [80, 80], maxZoom: 14 });

        // RENDERIZAÇÃO DA SIDEBAR PRINCIPAL
        function renderSidebar(q = '') {
            const list = document.getElementById('sidebarList');
            list.innerHTML = '';
            
            locations.forEach((loc, i) => {
                if (loc.city?.toLowerCase().includes(q.toLowerCase())) {
                    let isCritical = loc.type === 'blocked' || loc.machines.some(m => m.blocked);
                    let isWarning = loc.type === 'low_compliance' || loc.machines.some(m => m.compliance !== undefined && m.compliance < 80);
                    let glowClass = isCritical ? 'shadow-[0_0_10px_rgba(239,68,68,0.2)] border-red-500/50' : (isWarning ? 'shadow-[0_0_10px_rgba(245,158,11,0.15)] border-amber-500/50' : 'hover:shadow-[0_0_15px_rgba(59,130,246,0.15)] border-white/10 hover:border-blue-500/50');
                    let dotClass = isCritical ? 'bg-red-500 shadow-[0_0_5px_#ef4444]' : (isWarning ? 'bg-amber-500' : 'bg-blue-500');

                    const card = document.createElement('div');
                    card.className = `bg-white/5 p-3 border rounded-xl cursor-pointer transition-all duration-300 ${glowClass} group`;
                    card.onclick = () => { mapInstance.flyTo([loc.lat, loc.lng], 15, { duration: 1.5 }); setTimeout(() => leafletMarkers[i].openPopup(), 1500); };
                    
                    card.innerHTML = `
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full ${dotClass}"></div>
                                <h4 class="font-bold text-white text-[13px] truncate group-hover:text-blue-300 transition-colors">${loc.city || 'Desconhecido'}</h4>
                            </div>
                            <span class="text-[9px] font-bold bg-white/10 text-slate-200 border border-white/10 px-1.5 py-0.5 rounded-md">${loc.total} PCs</span>
                        </div>
                        <div class="space-y-1 pl-3 border-l border-white/10 ml-1">
                            ${loc.machines.slice(0, 3).map(m => `
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400 font-medium truncate w-3/4 group-hover:text-slate-300 transition-colors font-hud text-[10px]">${m.hostname}</span>
                                    <span class="w-1.5 h-1.5 rounded-full ${m.blocked ? 'bg-red-400' : 'bg-emerald-400'}"></span>
                                </div>
                            `).join('')}
                            ${loc.machines.length > 3 ? `<div class="text-[9px] text-blue-400/70 font-semibold pt-0.5">+${loc.machines.length - 3} ativos ocultos</div>` : ''}
                        </div>
                    `;
                    list.appendChild(card);
                }
            });
        }
        
        // RENDERIZAÇÃO DA LISTA KIOSK DIREITA
        function renderKioskList() {
            const list = document.getElementById('kioskListContent');
            list.innerHTML = '';
            locations.forEach((loc, i) => {
                let isCritical = loc.type === 'blocked' || loc.machines.some(m => m.blocked);
                let isWarning = loc.type === 'low_compliance' || loc.machines.some(m => m.compliance !== undefined && m.compliance < 80);
                let dotClass = isCritical ? 'bg-red-500 shadow-[0_0_5px_#ef4444]' : (isWarning ? 'bg-amber-500' : 'bg-blue-500 shadow-[0_0_5px_#3b82f6]');
                let borderClass = isCritical ? 'border-red-500/30 hover:border-red-400' : (isWarning ? 'border-amber-500/30 hover:border-amber-400' : 'border-white/5 hover:border-blue-400/50');

                const item = document.createElement('div');
                item.className = `flex justify-between items-center p-2 bg-white/5 border ${borderClass} rounded-lg cursor-pointer hover:bg-white/10 transition-all`;
                item.onclick = () => { mapInstance.flyTo([loc.lat, loc.lng], 15, { duration: 1.5 }); setTimeout(() => leafletMarkers[i].openPopup(), 1500); };
                
                item.innerHTML = `
                    <div class="flex items-center gap-2 overflow-hidden pr-2">
                        <div class="w-2 h-2 rounded-full shrink-0 ${dotClass}"></div>
                        <span class="text-slate-200 text-[10px] font-semibold truncate hover:text-white">${loc.city || 'Desconhecido'}</span>
                    </div>
                    <span class="text-[9px] font-bold text-white bg-black/40 px-1.5 py-0.5 rounded border border-white/10 shrink-0">${loc.total}</span>
                `;
                list.appendChild(item);
            });
        }

        document.getElementById('filterInput').addEventListener('input', e => renderSidebar(e.target.value));
        renderSidebar(); renderKioskList();
    });

    // -----------------------------------------------------
    // SISTEMA DE NAVEGAÇÃO E MODO NOC (QUIOSQUE)
    // -----------------------------------------------------
    let isSidebarHidden = false;

    window.toggleSidebar = function() {
        const sidebar = document.getElementById('mainSidebar');
        const restoreBtn = document.getElementById('restoreSidebarBtn');
        const hudLegend = document.getElementById('kioskLegend');
        const kioskLocationList = document.getElementById('kioskLocationList');
        const kioskTicker = document.getElementById('kioskTicker');
        
        isSidebarHidden = !isSidebarHidden;

        if (isSidebarHidden) {
            // Esconder Modo Pesquisa -> Ativar Modo NOC (HUD + Alertas)
            sidebar.classList.add('-translate-x-[150%]', 'opacity-0');
            restoreBtn.classList.remove('opacity-0', 'pointer-events-none', '-translate-x-10');
            
            // Mostrar Painéis NOC
            hudLegend.classList.remove('opacity-0', 'translate-y-12', 'pointer-events-none');
            kioskLocationList.classList.remove('opacity-0', 'translate-x-12', 'pointer-events-none');
            kioskTicker.classList.remove('opacity-0', '-translate-y-[100%]');
            
            // Desloca botões de zoom Leaflet para não ficarem embaixo do HUD
            document.querySelector('.leaflet-bottom.leaflet-left').style.bottom = '100px';
        } else {
            // Voltar ao Modo Pesquisa
            sidebar.classList.remove('-translate-x-[150%]', 'opacity-0');
            restoreBtn.classList.add('opacity-0', 'pointer-events-none', '-translate-x-10');
            
            // Ocultar Painéis NOC
            hudLegend.classList.add('opacity-0', 'translate-y-12', 'pointer-events-none');
            kioskLocationList.classList.add('opacity-0', 'translate-x-12', 'pointer-events-none');
            kioskTicker.classList.add('opacity-0', '-translate-y-[100%]');
            
            // Volta botões Leaflet ao normal
            document.querySelector('.leaflet-bottom.leaflet-left').style.bottom = '10px';
        }
    };

    window.toggleKioskMode = function() {
        const mapContainer = document.getElementById('mainMapContainer');
        if (!document.fullscreenElement) {
            mapContainer.requestFullscreen().then(() => { if (!isSidebarHidden) toggleSidebar(); }).catch(err => { alert(`Erro: ${err.message}`); });
        } else { document.exitFullscreen(); }
    };

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement && isSidebarHidden) toggleSidebar(); 
    });

    // -----------------------------------------------------
    // STREET VIEW
    // -----------------------------------------------------
    window.openStreetView = function(lat, lng) {
        // Função utilitária para abrir o visualizador de panorama
        const showPano = (panoData) => {
            document.getElementById('street-view-container').style.display = 'block';
            panorama.setPano(panoData.location.pano);
            panorama.setPov({ heading: 180, pitch: 0 });
            panorama.setVisible(true);
            google.maps.event.trigger(panorama, 'resize');
        };

        // 1ª Tentativa: Busca o Street View mais próximo num raio de 20 metros (Outdoor)
        streetViewService.getPanorama({
            location: { lat, lng },
            radius: 20,
            preference: google.maps.StreetViewPreference.NEAREST,
            source: google.maps.StreetViewSource.OUTDOOR
        }, (data, status) => {
            if (status === "OK") {
                showPano(data);
            } else {
                // Fallback (2ª Tentativa): Busca qualquer visualização numa área maior de 200 metros
                streetViewService.getPanorama({
                    location: { lat, lng },
                    radius: 200,
                    preference: google.maps.StreetViewPreference.NEAREST
                }, (fallbackData, fallbackStatus) => {
                    if (fallbackStatus === "OK") {
                        showPano(fallbackData);
                    } else {
                        alert("Visualização local indisponível para esta coordenada exata e nas redondezas.");
                    }
                });
            }
        });
    };

    window.closeStreetView = () => {
        if (document.fullscreenElement === document.getElementById('street-view-container')) {
            document.exitFullscreen();
        }
        document.getElementById('street-view-container').style.display = 'none';
    };

    window.toggleStreetViewFullscreen = function() {
        const container = document.getElementById('street-view-container');
        if (document.fullscreenElement !== container) { 
            container.requestFullscreen().catch(e => console.error(e)); 
        } else { 
            document.exitFullscreen(); 
        }
    };
    
    document.addEventListener('fullscreenchange', () => { 
        if (panorama) google.maps.event.trigger(panorama, 'resize'); 
        if (mapInstance) setTimeout(() => mapInstance.invalidateSize(), 200);
    });
</script>
@endpush