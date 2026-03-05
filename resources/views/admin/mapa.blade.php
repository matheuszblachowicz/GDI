@extends('layouts.main')

@section('title', 'Global Device Intelligence')
@section('header', 'Monitoramento Geográfico e Ativos')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }

    /* Glassmorphism Sidebar */
    .indigo-sidebar {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(224, 231, 255, 0.5);
        box-shadow: 0 20px 25px -5px rgba(67, 56, 202, 0.05);
    }

    /* Modern Marker */
    .locality-marker {
        background: #4f46e5;
        color: white;
        border-radius: 12px;
        font-weight: 800;
        border: 3px solid #ffffff;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    /* Map Wrapper */
    .map-wrapper {
        position: relative;
        border-radius: 2.5rem;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        background: #1e293b;
    }

    /* Street View Floating Container */
    #street-view-container {
        position: absolute;
        bottom: 24px;
        right: 24px;
        width: 400px;
        height: 280px;
        border-radius: 1.5rem;
        border: 6px solid white;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        z-index: 2000;
        display: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    /* Estilo para quando estiver em Fullscreen */
    #street-view-container:fullscreen {
        width: 100vw;
        height: 100vh;
        border: none;
        border-radius: 0;
        bottom: 0;
        right: 0;
    }

    /* Popup Fixes */
    .leaflet-popup-content-wrapper { border-radius: 1.5rem !important; padding: 0 !important; overflow: hidden !important; }
    .leaflet-popup-content { margin: 0 !important; width: 300px !important; }

    .hostname-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        max-width: 100%;
    }

    .status-indicator { width: 10px; height: 10px; border-radius: 50%; shrink: 0; }
    .status-online { background: #10b981; }
    .status-blocked { background: #ef4444; }
</style>
@endpush

@section('content')
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-140px)]">
    
    <div class="w-full lg:w-[420px] indigo-sidebar rounded-[2.5rem] p-8 flex flex-col">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.487V6.513a2 2 0 011.553-1.943L9 2l5.447 2.724A2 2 0 0116 6.513v8.974a2 2 0 01-1.553 1.943L9 20z"></path></svg>
            </div>
            <div>
                <h3 class="text-xl font-extrabold text-indigo-900 tracking-tight">Fleet Intel</h3>
                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Active Discovery</span>
            </div>
        </div>

        <div class="relative mb-6">
            <input type="text" id="filterInput" placeholder="Buscar cidade ou host..." 
                   class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            <svg class="w-5 h-5 absolute left-4 top-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>

        <div id="sidebarList" class="flex-1 overflow-y-auto space-y-4 pr-2"></div>
    </div>

    <div class="flex-1 map-wrapper relative">
        <div id="map" class="w-full h-full z-0"></div>
        
        <div id="street-view-container">
            <div id="pano" style="width: 100%; height: 100%;"></div>
            
            <div class="absolute top-3 right-3 flex gap-2 z-[2001]">
                <button onclick="toggleFullscreen()" title="Tela Cheia"
                        class="bg-white/90 hover:bg-white text-indigo-600 rounded-full w-8 h-8 flex items-center justify-center shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
                <button onclick="closeStreetView()" title="Fechar"
                        class="bg-white/90 hover:bg-white text-indigo-600 rounded-full w-8 h-8 flex items-center justify-center shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
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

    document.addEventListener("DOMContentLoaded", function() {
        const map = L.map('map', { zoomControl: false, attributionControl: false }).setView([0,0], 2);
        
        L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
            maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        }).addTo(map);

        streetViewService = new google.maps.StreetViewService();
        panorama = new google.maps.StreetViewPanorama(document.getElementById("pano"), {
            visible: false,
            addressControl: false,
            linksControl: false,
            panControl: false,
            enableCloseButton: false
        });

        const leafletMarkers = {};
        const bounds = [];

        locations.forEach((loc, index) => {
            const lat = parseFloat(loc.lat);
            const lng = parseFloat(loc.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            bounds.push([lat, lng]);
            const marker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: '',
                    html: `<div class="locality-marker ${loc.machines.some(m => m.blocked) ? 'warning' : ''}" style="width:40px;height:40px">${loc.total}</div>`,
                    iconSize: [40, 40]
                })
            }).addTo(map);

            let machinesHtml = loc.machines.map(m => `
                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-100 mb-2 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 overflow-hidden flex-1">
                        <div class="status-indicator ${m.blocked ? 'status-blocked' : 'status-online'}"></div>
                        <div class="overflow-hidden flex-1">
                            <p class="text-[11px] font-bold text-slate-800 m-0 hostname-text" title="${m.hostname}">${m.hostname}</p>
                            <p class="text-[9px] text-slate-400 font-mono m-0">${m.ip || '---'}</p>
                        </div>
                    </div>
                    <button onclick="openStreetView(${lat}, ${lng})" class="ml-2 p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            `).join('');

            marker.bindPopup(`
                <div class="overflow-hidden">
                    <div class="bg-indigo-600 p-4 text-white">
                        <h4 class="text-xs font-black uppercase tracking-widest m-0">${loc.city || 'Regional'}</h4>
                    </div>
                    <div class="p-3 bg-slate-50 max-h-60 overflow-y-auto">${machinesHtml}</div>
                </div>
            `);
            leafletMarkers[index] = marker;
        });

        if (bounds.length > 0) map.fitBounds(bounds, { padding: [100, 100] });

        function renderSidebar(q = '') {
            const list = document.getElementById('sidebarList');
            list.innerHTML = '';
            locations.forEach((loc, i) => {
                if (loc.city?.toLowerCase().includes(q.toLowerCase())) {
                    const card = document.createElement('div');
                    card.className = "bg-white p-5 rounded-[2rem] border border-slate-100 hover:border-indigo-200 hover:shadow-xl cursor-pointer transition-all group";
                    card.onclick = () => { map.flyTo([loc.lat, loc.lng], 16); setTimeout(() => leafletMarkers[i].openPopup(), 1000); };
                    card.innerHTML = `
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-slate-800 text-sm truncate group-hover:text-indigo-600">${loc.city || 'Desconhecido'}</h4>
                            <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-1 rounded-lg font-bold">${loc.total} PCs</span>
                        </div>
                        <div class="space-y-2">
                            ${loc.machines.slice(0, 2).map(m => `
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <div class="w-1.5 h-1.5 rounded-full ${m.blocked ? 'bg-red-400' : 'bg-emerald-400'}"></div>
                                    <span class="text-[11px] text-slate-500 hostname-text" title="${m.hostname}">${m.hostname}</span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    list.appendChild(card);
                }
            });
        }
        document.getElementById('filterInput').addEventListener('input', e => renderSidebar(e.target.value));
        renderSidebar();
    });

    // STREET VIEW APROXIMADO
    window.openStreetView = function(lat, lng) {
        streetViewService.getPanorama({
            location: { lat, lng },
            radius: 100,
            source: google.maps.StreetViewSource.OUTDOOR
        }, (data, status) => {
            if (status === "OK") {
                document.getElementById('street-view-container').style.display = 'block';
                panorama.setPano(data.location.pano);
                panorama.setPov({ heading: 180, pitch: 0 });
                panorama.setVisible(true);
                google.maps.event.trigger(panorama, 'resize');
            } else {
                alert("Visualização de rua não disponível nesta região.");
            }
        });
    };

    window.closeStreetView = () => {
        // Se estiver em fullscreen, sai antes de fechar
        if (document.fullscreenElement) document.exitFullscreen();
        document.getElementById('street-view-container').style.display = 'none';
    };

    // NOVA FUNÇÃO: TOGGLE FULLSCREEN
    window.toggleFullscreen = function() {
        const container = document.getElementById('street-view-container');
        
        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(err => {
                alert(`Erro ao tentar entrar em modo tela cheia: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    };

    // Ajusta o panorama quando o tamanho do container mudar (fullscreen)
    document.addEventListener('fullscreenchange', () => {
        if (panorama) google.maps.event.trigger(panorama, 'resize');
    });
</script>
@endpush