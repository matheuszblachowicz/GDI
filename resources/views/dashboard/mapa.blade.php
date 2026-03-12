@extends('layouts.app')

@section('title', 'Mapa de Máquinas - Distribuição')
@section('header', 'Distribuição Geográfica das Máquinas')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        #map { height: 75vh; width: 100%; z-index: 10; }
    </style>
@endpush

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div id="map" class="rounded-lg shadow-md border border-gray-300"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializa o mapa centrado no Brasil
            var map = L.map('map').setView([-14.235004, -51.92528], 4);

            // Adiciona a camada do OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // Agrupador de marcadores
            var markers = L.markerClusterGroup();
            
            // Dados passados pelo Controller
            var devices = @json($devices);

            devices.forEach(function(device) {
                // Cria o marcador apenas se tiver coordenadas válidas
                if (device.latitude && device.longitude) {
                    var marker = L.marker([device.latitude, device.longitude])
                        .bindPopup(
                            '<div class="text-sm p-1">' +
                            '<strong class="block text-blue-600 mb-1">Detalhes da Máquina</strong>' +
                            '<span class="block text-gray-800"><b>Hostname:</b> ' + device.hostname + '</span>' +
                            '<span class="block text-gray-800"><b>IP:</b> ' + device.ip_address + '</span>' +
                            '</div>'
                        );
                    markers.addLayer(marker);
                }
            });

            // Adiciona os clusters ao mapa
            map.addLayer(markers);
        });
    </script>
@endpush