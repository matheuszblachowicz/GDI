<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Auditoria GDI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 landscape; margin: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #1e293b; background: #ffffff; }
        .page-break { page-break-after: always; }
        
        /* Capa Premium */
        .cover { height: 210mm; width: 297mm; background: #060b14; color: #f8fafc; position: relative; overflow: hidden; }
        .cover-content { position: relative; padding: 50mm 40mm; z-index: 10; }
        .cover-title { font-size: 46px; font-weight: 800; color: #ffffff; margin-bottom: 20px; }
        .cover-subtitle { font-size: 16px; color: #cbd5e1; border-left: 3px solid #0ea5e9; padding-left: 20px; }
        
        /* Dashboard */
        .page-padding { padding: 20mm; }
        .section-header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .brand { font-size: 16px; font-weight: 800; }
        .brand span { color: #0ea5e9; }
        
        .kpi-row { display: flex; gap: 15px; margin-bottom: 30px; }
        .kpi-card { flex: 1; border: 1px solid #cbd5e1; border-top: 3px solid #0f172a; padding: 15px; }
        .kpi-val { font-size: 24px; font-weight: 800; color: #0f172a; }

        /* Tabela Matriz */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background: #0f172a; color: #ffffff; padding: 10px; text-align: left; font-size: 8px; text-transform: uppercase; }
        .data-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        .data-table tr:nth-child(even) { background: #f8fafc; }
        
        .badge { padding: 3px 6px; font-weight: 700; font-size: 7px; border-radius: 2px; text-transform: uppercase; }
        .bg-success { background: #ecfdf5; color: #059669; }
        .bg-error { background: #fef2f2; color: #dc2626; }
        .bg-warning { background: #fffbeb; color: #d97706; }
    </style>
</head>
<body>

    <div class="cover page-break">
        <div class="cover-content">
            <h1 class="cover-title">Auditoria de Inventário<br>e Conformidade GDI</h1>
            <p class="cover-subtitle">Relatório estrutural de hardware e software não homologado.</p>
        </div>
    </div>

    @foreach($devices->chunk(25) as $chunk)
    <div class="page-padding page-break">
        <div class="section-header">
            <div class="brand">MATRIZ DE <span>INVENTÁRIO</span></div>
            <div class="doc-id">GDI-{{ now()->format('Ymd') }}</div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%">Hostname</th>
                    <th style="width: 15%">IP / Cidade</th>
                    <th style="width: 15%">Utilizador Atual</th>
                    <th style="width: 25%">Hardware / SO</th>
                    <th style="width: 12%">RAM / Disco</th>
                    <th style="width: 8%">Status</th>
                    <th style="width: 10%">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $device)
                    <tr>
                        <td style="font-weight: bold;">{{ $device->hostname }}</td>
                        <td>{{ $device->ip_address }}<br><small>{{ $device->city }}</small></td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($device->user_display, 15) }}<br>
                            @if($device->is_vip_view) <span class="badge bg-warning" style="font-size: 6px;">VIP</span> @endif
                        </td>
                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($device->cpu_display, 30) }}</strong><br>
                            <small>{{ $device->os_display }}</small>
                        </td>
                        <td>
                            <span style="color: #0ea5e9; font-weight: bold;">{{ $device->ram_display }}</span><br>
                            <small>{{ $device->storage_display }} ({{ $device->disk_type_display }})</small>
                        </td>
                        <td>
                            @if($device->is_blocked) <span class="badge bg-error">Bloq.</span> @else <span class="badge bg-success">Ativo</span> @endif
                        </td>
                        <td style="font-weight: 800; color: {{ $device->compliance_score >= 80 ? '#059669' : '#dc2626' }}">
                            {{ $device->compliance_score }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

</body>
</html>