@extends('layouts.main')

@section('title', 'Mapa de Localidades')
@section('header', 'Distribuição Geográfica do Parque Informático')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Estilo para os marcadores numéricos personalizados */
    .locality-marker {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border-radius: 50%;
        text-align: center;
        font-weight: bold;
        border: 3px solid white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .locality-marker.warning {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
    }
    
    /* Personalização do Popup do Leaflet para o nosso tema Tailwind */
    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        padding: 0;
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0;
        width: 320px !important;
    }
    .leaflet-container a.leaflet-popup-close-button {
        color: white;
        padding: 8px;
    }
</style>
@endpush

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-2 animate-fade-in-up">
    <div id="map" class="w-full h-[700px] rounded-2xl z-0"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Dados recebidos do Controller
        const locations = @json($locations);
        
        // Inicializa o mapa (centrado em Portugal/Brasil temporariamente, ajusta-se depois com o bounds)
        const map = L.map('map').setView([39.3999, -8.2245], 6);

        // TileLayer Moderno (CartoDB Voyager)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Se não houver locais, paramos por aqui
        if(locations.length === 0) return;

        // Array para guardar todas as coordenadas para auto-ajustar o zoom
        const bounds = [];

        locations.forEach(loc => {
            const lat = parseFloat(loc.lat);
            const lng = parseFloat(loc.lng);
            bounds.push([lat, lng]);

            // Verifica se alguma máquina neste local está bloqueada para dar destaque visual
            const hasBlocked = loc.machines.some(m => m.blocked);
            const markerClass = hasBlocked ? 'locality-marker warning' : 'locality-marker';

            // Ícone Personalizado mostrando a quantidade de máquinas no local
            const customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="${markerClass}" style="width: 40px; height: 40px;">${loc.total}</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            // Constrói o HTML do Popup (Lista de Máquinas)
            let popupHtml = `
                <div class="bg-slate-800 text-white p-4">
                    <h3 class="font-bold text-lg">Localidade (Filial)</h3>
                    <p class="text-xs text-slate-300">${loc.total} máquina(s) registada(s) aqui.</p>
                </div>
                <div class="max-h-60 overflow-y-auto p-0 bg-white">
                    <ul class="divide-y divide-slate-100">
            `;

            loc.machines.forEach(machine => {
                const statusBadge = machine.blocked 
                    ? `<span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded">Bloqueada</span>`
                    : `<span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Ativa</span>`;

                popupHtml += `
                    <li class="p-4 hover:bg-slate-50 transition">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-slate-800 text-sm">${machine.hostname}</span>
                            ${statusBadge}
                        </div>
                        <div class="text-xs text-slate-500 font-mono mb-1">${machine.ip}</div>
                        <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-600">
                            <div class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                                ${machine.user.charAt(0).toUpperCase()}
                            </div>
                            ${machine.user}
                        </div>
                    </li>
                `;
            });

            popupHtml += `</ul></div>`;

            // Adiciona o marcador ao mapa
            L.marker([lat, lng], {icon: customIcon})
             .bindPopup(popupHtml)
             .addTo(map);
        });

        // Ajusta o zoom do mapa automaticamente para mostrar todos os marcadores na tela
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
    });
</script>
@endpush