<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dossiê de Auditoria Detalhado</title>
    <style>
        @page { margin: 0px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .content-wrap { padding: 30px 40px; }
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
        
        .cover-page { background-color: #0f172a; color: white; height: 100%; position: relative; padding: 50px; }
        .cover-header { margin-top: 150px; border-left: 8px solid #3b82f6; padding-left: 30px; }
        .cover-header h3 { color: #94a3b8; font-size: 16px; text-transform: uppercase; letter-spacing: 3px; margin: 0 0 10px 0; }
        .cover-header h1 { color: #ffffff; font-size: 42px; margin: 0 0 15px 0; line-height: 1.1; }
        .cover-header h2 { color: #3b82f6; font-size: 20px; margin: 0; font-weight: normal; }
        .cover-footer { position: absolute; bottom: 80px; left: 80px; right: 80px; border-top: 1px solid #334155; padding-top: 20px; }
        .cover-footer table { width: 100%; color: #94a3b8; font-size: 12px; }
        .cover-footer strong { color: #ffffff; display: block; margin-bottom: 5px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }

        .page-header { background-color: #ffffff; border-bottom: 2px solid #e2e8f0; padding: 15px 40px; }
        .page-header table { width: 100%; }
        .page-header .brand { font-size: 14px; font-weight: bold; color: #0f172a; }
        .page-header .brand span { color: #3b82f6; }
        .page-header .doc-title { text-align: right; color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; }

        h2.section-title { font-size: 18px; color: #0f172a; margin: 0 0 15px 0; border-bottom: 2px solid #3b82f6; padding-bottom: 8px; display: inline-block; text-transform: uppercase; }
        h3.chart-title { font-size: 12px; color: #334155; margin-bottom: 10px; text-transform: uppercase; font-weight: bold; }
        
        .chart-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .chart-table td { padding: 4px 0; font-size: 10px; vertical-align: middle; }
        .chart-bg { background-color: #e2e8f0; width: 100%; height: 10px; border-radius: 5px; overflow: hidden; display: block; }
        .chart-fill { height: 10px; display: block; }

        .data-table { width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #cbd5e1; margin-bottom: 20px; }
        .data-table th { background-color: #1e293b; color: #ffffff; font-size: 9px; text-transform: uppercase; padding: 8px; text-align: left; }
        .data-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; color: #ffffff; text-align: center; }
        .bg-emerald { background-color: #10b981; } .bg-red { background-color: #ef4444; } .bg-amber { background-color: #f59e0b; } .bg-slate { background-color: #64748b; }
        
        .text-center { text-align: center; } .font-bold { font-weight: bold; } .text-red { color: #ef4444; }
    </style>
</head>
<body>

    @php
        $totalDevices = $devices->count();
        $blockedDevices = 0;
        $vipDevices = 0;
        $totalCompliance = 0;
        
        $osDistribution = [];
        $riskDistribution = ['Seguro (100%)' => 0, 'Atenção (80-99%)' => 0, 'Crítico (<80%)' => 0];
        $allUnauthorizedApps = []; 

        foreach($devices as $device) {
            if($device->is_blocked) {
                $blockedDevices++;
            }
            
            $isVip = in_array($device->current_user, $vipsList);
            if($isVip) {
                $vipDevices++;
            }

            $os = $device->os_version ?? 'Desconhecido';
            if(!isset($osDistribution[$os])) {
                $osDistribution[$os] = 0;
            }
            $osDistribution[$os]++;

            $comp = 0;
            if ($isVip) { 
                $comp = 100; 
            } else {
                $totalApps = $device->applications->count();
                if ($totalApps > 0 && !empty($allowedAppsList)) {
                    $unauth = $device->applications->filter(function($app) use ($allowedAppsList) {
                        foreach($allowedAppsList as $allowed) {
                            if(stripos($app->name, trim($allowed)) !== false) return false;
                        }
                        return true;
                    });
                    
                    $comp = max(0, round((($totalApps - $unauth->count()) / $totalApps) * 100));
                    
                    foreach($unauth as $ua) {
                        if(!isset($allUnauthorizedApps[$ua->name])) {
                            $allUnauthorizedApps[$ua->name] = [];
                        }
                        if(!in_array($device->hostname, $allUnauthorizedApps[$ua->name])) {
                            $allUnauthorizedApps[$ua->name][] = $device->hostname;
                        }
                    }
                }
            }
            $device->calculated_compliance = $comp;
            $device->is_vip_calculated = $isVip;
            $totalCompliance += $comp;

            if($comp == 100) {
                $riskDistribution['Seguro (100%)']++;
            } elseif($comp >= 80) {
                $riskDistribution['Atenção (80-99%)']++;
            } else {
                $riskDistribution['Crítico (<80%)']++;
            }
        }

        $avgCompliance = $totalDevices > 0 ? round($totalCompliance / $totalDevices) : 0;
        arsort($osDistribution);
        
        uasort($allUnauthorizedApps, function($a, $b) {
            return count($b) <=> count($a);
        });
    @endphp

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(750, 570, "Pág. {PAGE_NUM} / {PAGE_COUNT}", $fontMetrics->get_font("helvetica", "bold"), 8, array(0.4, 0.4, 0.4));
        }
    </script>

    <div class="cover-page page-break">
        <div class="cover-header">
            <h3>Confidencial - Uso Interno</h3>
            <h1>Dossiê Completo de Auditoria,<br>Compliance e Segurança Web</h1>
            <h2>Gerado pelo Sistema GDI</h2>
        </div>
        
        <div class="cover-footer">
            <table>
                <tr>
                    <td width="33%"><strong>Data de Emissão</strong><br>{{ now()->format('d/m/Y \à\s H:i') }}</td>
                    <td width="33%"><strong>Âmbito Tecnológico</strong><br>{{ $totalDevices }} Máquinas Analisadas</td>
                    <td width="33%"><strong>Índice de Saúde Global</strong><br>{{ $avgCompliance }}% de Conformidade</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="page-header">
        <table>
            <tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Relatório Detalhado &bull; {{ now()->format('d/m/Y') }}</td></tr>
        </table>
    </div>

    <div class="content-wrap">
        <h2 class="section-title">1. Análise Gráfica do Parque (Dashboard)</h2>
        <table width="100%" style="margin-bottom: 30px; border-spacing: 20px 0; margin-left: -20px;">
            <tr>
                <td width="50%" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; vertical-align: top;">
                    <h3 class="chart-title">Distribuição de Risco (Compliance)</h3>
                    <table class="chart-table">
                        @foreach($riskDistribution as $label => $qtd)
                            @php 
                                $pct = $totalDevices > 0 ? ($qtd / $totalDevices) * 100 : 0; 
                                $color = str_contains($label, 'Seguro') ? '#10b981' : (str_contains($label, 'Crítico') ? '#ef4444' : '#f59e0b');
                            @endphp
                            <tr>
                                <td width="30%"><strong>{{ $label }}</strong></td>
                                <td width="55%">
                                    <div class="chart-bg"><div class="chart-fill" style="width: {{ $pct }}%; background-color: {{ $color }};"></div></div>
                                </td>
                                <td width="15%" class="text-center">{{ $qtd }} maq. ({{ round($pct) }}%)</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
                
                <td width="50%" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; vertical-align: top;">
                    <h3 class="chart-title">Fragmentação de Sistemas Operativos</h3>
                    <table class="chart-table">
                        @foreach(array_slice($osDistribution, 0, 5) as $os => $qtd)
                            @php $pct = $totalDevices > 0 ? ($qtd / $totalDevices) * 100 : 0; @endphp
                            <tr>
                                <td width="40%"><strong>{{ \Illuminate\Support\Str::limit($os, 20) }}</strong></td>
                                <td width="45%">
                                    <div class="chart-bg"><div class="chart-fill" style="width: {{ $pct }}%; background-color: #3b82f6;"></div></div>
                                </td>
                                <td width="15%" class="text-center">{{ $qtd }} maq.</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        <div class="page-break"></div>
        <h2 class="section-title">2. Mapeamento Detalhado de Software Irregular</h2>

        @if(count($allUnauthorizedApps) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="35%">Software Não Homologado</th>
                        <th width="10%" class="text-center">Qtd. Máquinas</th>
                        <th width="55%">Hostnames Identificados (Foco de Risco)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allUnauthorizedApps as $appName => $hostnames)
                        <tr class="avoid-break">
                            <td class="font-bold text-red">{{ $appName }}</td>
                            <td class="text-center font-bold">{{ count($hostnames) }}</td>
                            <td style="font-family: monospace; color: #475569; line-height: 1.4;">
                                {{ implode(', ', $hostnames) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background: #ecfdf5; border: 1px solid #10b981; padding: 20px; text-align: center; color: #047857; font-weight: bold;">
                Nenhum software irregular identificado na rede. Compliance Perfeito.
            </div>
        @endif

        <div class="page-break"></div>
        <h2 class="section-title">3. Auditoria Recente de Navegação Web</h2>

        @if(count($webHistory) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="15%">Data / Hora</th>
                        <th width="15%">Hostname</th>
                        <th width="15%">Utilizador</th>
                        <th width="10%">Browser</th>
                        <th width="45%">Título da Janela / Site Acedido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($webHistory as $log)
                        <tr class="avoid-break">
                            <td>{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="font-bold">{{ $log->device->hostname ?? 'N/A' }}</td>
                            <td>{{ $log->username }}</td>
                            <td><span class="badge bg-slate">{{ str_replace('.exe', '', $log->process_name) }}</span></td>
                            <td style="color: #334155;">{{ \Illuminate\Support\Str::limit($log->active_window_title, 80) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; text-align: center; color: #64748b;">
                Sem dados de navegação web recentes capturados.
            </div>
        @endif

        <div class="page-break"></div>
        <h2 class="section-title">4. Matriz Geral de Ativos</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th width="15%">Hostname</th>
                    <th width="15%">Endereço IP</th>
                    <th width="20%">Utilizador Ativo</th>
                    <th width="15%">Localização</th>
                    <th width="10%" class="text-center">VIP</th>
                    <th width="10%" class="text-center">Status</th>
                    <th width="15%" class="text-center">Compliance</th>
                </tr>
            </thead>
            <tbody>
                @if($devices->count() > 0)
                    @foreach($devices as $device)
                        <tr class="avoid-break">
                            <td class="font-bold">{{ $device->hostname }}</td>
                            <td style="font-family: monospace;">{{ $device->ip_address ?? 'N/A' }}</td>
                            <td>{{ $device->current_user ?? 'Sem Registo' }}</td>
                            <td>{{ $device->city ?? 'N/A' }}</td>
                            
                            <td class="text-center">
                                @if($device->is_vip_calculated)
                                    <span class="badge bg-amber">Sim</span>
                                @else
                                    Não
                                @endif
                            </td>
                            
                            <td class="text-center">
                                @if($device->is_blocked)
                                    <span class="badge bg-red">Bloqueado</span>
                                @else
                                    <span class="badge bg-emerald">Ativo</span>
                                @endif
                            </td>
                            
                            <td class="text-center font-bold" style="color: {{ $device->calculated_compliance >= 80 ? '#10b981' : ($device->is_vip_calculated ? '#f59e0b' : '#ef4444') }}; font-size: 11px;">
                                {{ $device->calculated_compliance }}%
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                            Nenhum dispositivo encontrado no sistema.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>