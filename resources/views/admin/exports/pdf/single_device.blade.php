<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dossiê de Auditoria Detalhado - {{ $device->hostname ?? 'Dispositivo' }}</title>
    <style>
        /* === CONFIGURAÇÕES GERAIS (PAISAGEM / HORIZONTAL) === */
        @page { size: A4 landscape; margin: 0px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .content-wrap { padding: 40px; }
        
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
        
        .text-center { text-align: center; } 
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; } 
        .text-red { color: #ef4444; }
        
        /* === CAPA ESTRUTURADA (Design original do Geral) === */
        .cover-page { background-color: #0f172a; color: white; padding: 0 60px; height: 790px; overflow: hidden; box-sizing: border-box; }
        .cover-content { padding-top: 120px; }
        .cover-header { border-left: 8px solid #3b82f6; padding-left: 30px; margin-bottom: 120px; }
        .cover-header h3 { color: #94a3b8; font-size: 16px; text-transform: uppercase; letter-spacing: 3px; margin: 0 0 10px 0; }
        .cover-header h1 { color: #ffffff; font-size: 44px; margin: 0 0 15px 0; line-height: 1.1; }
        .cover-header h2 { color: #3b82f6; font-size: 20px; margin: 0; font-weight: normal; }
        
        .cover-info-box { background-color: #1e293b; border-top: 4px solid #3b82f6; padding: 25px; border-radius: 6px; }
        .cover-info-table { width: 100%; border-collapse: collapse; }
        .cover-info-table td { padding: 10px; vertical-align: top; border-right: 1px solid #334155; }
        .cover-info-table td:last-child { border-right: none; }
        .cover-info-table .label { color: #94a3b8; display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .cover-info-table .value { color: #ffffff; display: block; font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .cover-info-table .sub-value { font-size: 11px; }

        /* === PÁGINAS DE SEPARAÇÃO (TÍTULOS DE SEÇÃO) === */
        .section-separator { background-color: #1e293b; color: white; height: 790px; box-sizing: border-box; text-align: center; padding-top: 300px; }
        .section-separator h1 { font-size: 36px; color: #ffffff; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .section-separator .divider-line { width: 100px; height: 4px; background-color: #3b82f6; margin: 20px auto; }
        .section-separator p { color: #94a3b8; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }

        /* === SUMÁRIO === */
        .toc-title { font-size: 24px; color: #0f172a; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 30px; text-transform: uppercase; }
        .toc-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .toc-table td { padding: 15px 0; border-bottom: 1px dashed #cbd5e1; color: #334155; }
        .toc-table td strong { color: #0f172a; }

        /* === CABEÇALHOS E TABELAS === */
        .page-header { background-color: #ffffff; border-bottom: 2px solid #e2e8f0; padding: 15px 40px; }
        .page-header table { width: 100%; }
        .page-header .brand { font-size: 14px; font-weight: bold; color: #0f172a; }
        .page-header .brand span { color: #3b82f6; }
        .page-header .doc-title { text-align: right; color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; }

        h2.section-title { font-size: 18px; color: #0f172a; margin: 0 0 15px 0; border-bottom: 2px solid #3b82f6; padding-bottom: 8px; display: inline-block; text-transform: uppercase; }
        h3.chart-title { font-size: 12px; color: #334155; margin-bottom: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
        
        /* === DASHBOARD CARDS === */
        .kpi-row { width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; margin-bottom: 20px; }
        .kpi-card { background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; text-align: center; width: 33.33%; }
        .kpi-card .kpi-value { font-size: 24px; font-weight: bold; color: #0f172a; display: block; margin-bottom: 5px; }
        .kpi-card .kpi-label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        
        .chart-box { background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; text-align: center; }

        /* === TABELAS DE DADOS === */
        .data-table { width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #cbd5e1; margin-bottom: 20px; table-layout: fixed; word-wrap: break-word; }
        .data-table th { background-color: #1e293b; color: #ffffff; font-size: 9px; text-transform: uppercase; padding: 8px; text-align: left; }
        .data-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        .info-list { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        .info-list td { padding: 6px 0; border-bottom: 1px dashed #cbd5e1; font-size: 10px; }
        .info-list td:first-child { font-weight: bold; color: #64748b; width: 40%; }

        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; color: #ffffff; text-align: center; }
        .bg-emerald { background-color: #10b981; } .bg-red { background-color: #ef4444; } .bg-amber { background-color: #f59e0b; } .bg-slate { background-color: #64748b; }
    </style>
</head>
<body>

    @php
        // 1. Variáveis base calculadas a partir dos dados enviados pelo ExportController
        $totalApps = $device->applications ? $device->applications->count() : 0;
        $unauthCount = $unauthorizedApps ? $unauthorizedApps->count() : 0;
        $safeAppsCount = max(0, $totalApps - $unauthCount);
        
        $compColor = $complianceLevel == 100 ? '#10b981' : ($complianceLevel >= 80 ? '#f59e0b' : '#ef4444');
        $unauthNames = $unauthorizedApps ? $unauthorizedApps->pluck('name')->toArray() : [];

        // 2. Extração da Auditoria Web (Filtro seguro para os últimos logs da máquina)
        $knownBrowsers = ['chrome', 'msedge', 'firefox', 'brave', 'opera', 'safari'];
        $filteredWebHistory = collect([]);
        
        if(isset($activityLogs) && $activityLogs->count() > 0) {
            $filteredWebHistory = $activityLogs->filter(function($log) use ($knownBrowsers) {
                $process = strtolower($log->process_name ?? '');
                foreach($knownBrowsers as $browser) {
                    if (str_contains($process, $browser)) {
                        return true;
                    }
                }
                return false;
            });
        }

        // 3. Gráfico do Dispositivo (Idêntico ao formato de Rosca do Geral)
        $chartBase64 = null;
        if($totalApps > 0) {
            $chartConfig = "{
                type: 'doughnut',
                data: {
                    labels: ['Seguros', 'Risco'],
                    datasets: [{
                        data: [$safeAppsCount, $unauthCount],
                        backgroundColor: ['#10b981', '#ef4444']
                    }]
                },
                options: { plugins: { legend: { position: 'right' } }, animation: false }
            }";
            $chartUrl = "https://quickchart.io/chart?w=350&h=200&c=" . urlencode($chartConfig);
            $chartBase64 = @base64_encode(file_get_contents($chartUrl));
        }
    @endphp

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(750, 560, "Pág. {PAGE_NUM} / {PAGE_COUNT}", $fontMetrics->get_font("helvetica", "bold"), 8, array(0.4, 0.4, 0.4));
        }
    </script>

    <div class="cover-page page-break">
        <div class="cover-content">
            @if(!empty($logoBase64))
                <div style="text-align: right; margin-top: -60px; margin-bottom: 60px;">
                    <img src="{{ $logoBase64 }}" style="max-height: 50px;" alt="Logo">
                </div>
            @endif

            <div class="cover-header">
                <h3>Confidencial - Uso Interno</h3>
                <h1>Auditoria e Compliance<br>Individual</h1>
                <h2>Gerado pelo Sistema GDI(PlatID)</h2>
            </div>
            
            <div class="cover-info-box">
                <table class="cover-info-table">
                    <tr>
                        <td width="30%">
                            <span class="label">Data de Emissão</span>
                            <span class="value">{{ now()->format('d/m/Y H:i') }}</span>
                            <span class="sub-value" style="color: #94a3b8;">Gerado automaticamente</span>
                        </td>
                        <td width="35%" style="padding-left: 20px;">
                            <span class="label">Dispositivo Alvo</span>
                            <span class="value">{{ $device->hostname ?? 'N/A' }}</span>
                            <span class="sub-value">
                                @if(isset($device->is_blocked) && $device->is_blocked)
                                    <span style="color: #ef4444; font-weight: bold;">Bloqueado na Rede</span>
                                @else
                                    <span style="color: #10b981; font-weight: bold;">Ativo na Rede</span>
                                @endif
                            </span>
                        </td>
                        <td width="35%" style="padding-left: 20px;">
                            <span class="label">Índice de Saúde Global</span>
                            <span class="value" style="color: {{ $compColor }};">{{ $complianceLevel }}% Seguro</span>
                            <span class="sub-value" style="color: #94a3b8;">Nível de conformidade de software</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="page-break" style="background: #ffffff; height: 790px; box-sizing: border-box;">
        <div class="page-header">
            <table><tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Sumário Executivo</td></tr></table>
        </div>
        
        <div class="content-wrap">
            <h2 class="toc-title">Índice do Relatório Individual</h2>
            <table class="toc-table">
                <tr>
                    <td width="90%"><strong>1. Visão Geral do Dispositivo (Dashboard)</strong><br><span style="font-size: 11px;">Métricas gerais, ficha técnica e gráfico de distribuição de risco.</span></td>
                    <td width="10%" class="text-right"><span style="color: #3b82f6; font-weight: bold;">Sec. 1</span></td>
                </tr>
                <tr>
                    <td><strong>2. Mapeamento Detalhado de Software</strong><br><span style="font-size: 11px;">Identificação de todas as aplicações instaladas e respectiva homologação.</span></td>
                    <td class="text-right"><span style="color: #3b82f6; font-weight: bold;">Sec. 2</span></td>
                </tr>
                <tr>
                    <td><strong>3. Auditoria Recente de Navegação Web</strong><br><span style="font-size: 11px;">Acessos, títulos de janelas e processos de browsers monitorizados.</span></td>
                    <td class="text-right"><span style="color: #3b82f6; font-weight: bold;">Sec. 3</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section-separator page-break">
        <h1>1. Visão Geral do Dispositivo</h1>
        <div class="divider-line"></div>
        <p>Métricas Gerais, Risco e Ficha Técnica</p>
    </div>

    <div class="page-break" style="background: #ffffff; height: 790px; box-sizing: border-box;">
        <div class="page-header">
            <table><tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Dashboard &bull; {{ $device->hostname ?? 'Dispositivo' }}</td></tr></table>
        </div>

        <div class="content-wrap">
            <table class="kpi-row">
                <tr>
                    <td class="kpi-card">
                        <span class="kpi-value" style="color: {{ $compColor }};">{{ $complianceLevel }}%</span>
                        <span class="kpi-label">Índice de Saúde (Compliance)</span>
                    </td>
                    <td class="kpi-card">
                        <span class="kpi-value" style="color: #ef4444;">{{ $unauthCount }}</span>
                        <span class="kpi-label">Aplicações Irregulares</span>
                    </td>
                    <td class="kpi-card">
                        <span class="kpi-value" style="color: #3b82f6;">{{ $totalApps }}</span>
                        <span class="kpi-label">Total de Apps Instalados</span>
                    </td>
                </tr>
            </table>

            <table width="100%" style="border-spacing: 20px 0; margin-left: -20px;">
                <tr>
                    <td width="50%" class="chart-box" style="vertical-align: top;">
                        <h3 class="chart-title">Distribuição de Risco de Software</h3>
                        @if($chartBase64)
                            <img src="data:image/png;base64,{{ $chartBase64 }}" style="max-width: 100%; height: auto; max-height: 180px;" alt="Gráfico de Risco">
                        @else
                            <div style="padding: 40px; color: #94a3b8;">Gráfico indisponível.</div>
                        @endif
                    </td>
                    
                    <td width="50%" class="chart-box" style="vertical-align: top;">
                        <h3 class="chart-title">Ficha Técnica do Ativo</h3>
                        <table class="info-list">
                            <tr><td>Hostname:</td><td class="font-bold" style="color: #0f172a;">{{ $device->hostname ?? 'N/A' }}</td></tr>
                            <tr><td>Sistema Operativo:</td><td>{{ $device->os_version ?? 'Não identificado' }}</td></tr>
                            <tr><td>Endereço IP Local:</td><td style="font-family: monospace;">{{ $device->ip_address ?? 'N/A' }}</td></tr>
                            <tr><td>Usuário Ativo:</td><td>{{ $device->current_user ?? 'N/A' }}</td></tr>
                            <tr><td>Classificação VIP:</td><td>
                                @if(isset($isVip) && $isVip)
                                    <span class="badge bg-amber">Sim</span>
                                @else
                                    <span style="color: #64748b; font-weight: bold;">Não</span>
                                @endif
                            </td></tr>
                            <tr><td>Status do Equipamento:</td><td>
                                @if(isset($device->is_blocked) && $device->is_blocked)
                                    <span class="badge bg-red">Bloqueado pela TI</span>
                                @else
                                    <span class="badge bg-emerald">Ativo na Rede</span>
                                @endif
                            </td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section-separator page-break">
        <h1>2. Mapeamento de Software</h1>
        <div class="divider-line"></div>
        <p>Aplicações homologadas e não homologadas instaladas</p>
    </div>

    <div class="page-break" style="background: #ffffff; min-height: 790px;">
        <div class="page-header">
            <table><tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Inventário de Software &bull; {{ $device->hostname ?? 'Dispositivo' }}</td></tr></table>
        </div>

        <div class="content-wrap">
            @if($totalApps > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">#</th>
                            <th width="70%">Software Identificado</th>
                            <th width="20%" class="text-center">Status / Risco</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($device->applications as $index => $app)
                            @php
                                $isIrregular = in_array($app->name, $unauthNames);
                            @endphp
                            <tr class="avoid-break">
                                <td class="text-center" style="color: #94a3b8;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="font-bold {{ $isIrregular ? 'text-red' : '' }}" style="color: #334155;">{{ $app->name }}</td>
                                <td class="text-center">
                                    @if($isIrregular)
                                        <span class="badge bg-red">Irregular</span>
                                    @else
                                        <span class="badge bg-emerald">Autorizado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; text-align: center; color: #64748b;">
                    Nenhum software registrado para este dispositivo na base de dados.
                </div>
            @endif
        </div>
    </div>

    <div class="section-separator page-break">
        <h1>3. Auditoria Recente de Navegação Web</h1>
        <div class="divider-line"></div>
        <p>Acessos recentes utilizando navegadores na estação</p>
    </div>

    <div class="page-break" style="background: #ffffff; min-height: 790px;">
        <div class="page-header">
            <table><tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Navegação Recente &bull; {{ $device->hostname ?? 'Dispositivo' }}</td></tr></table>
        </div>

        <div class="content-wrap">
            <div style="margin-bottom: 12px; font-size: 10px; color: #64748b;">
                * Exibindo os últimos acessos registados utilizando navegadores homologados (Chrome, Edge, Firefox, Brave, Opera, Safari).
            </div>

            @if($filteredWebHistory->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="15%">Data / Hora</th>
                            <th width="15%">Utilizador</th>
                            <th width="15%" class="text-center">Navegador</th>
                            <th width="55%">Título da Janela / Site Acedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredWebHistory as $log)
                            <tr class="avoid-break">
                                <td>{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $log->username }}</td>
                                <td class="text-center">
                                    @php
                                        $browserName = strtolower(str_replace('.exe', '', $log->process_name));
                                        $displayBrowser = 'Desconhecido';
                                        foreach($knownBrowsers as $kb) {
                                            if (str_contains($browserName, $kb)) {
                                                $displayBrowser = $kb === 'msedge' ? 'Edge' : ucfirst($kb);
                                                break;
                                            }
                                        }
                                        
                                        $colorClass = match(strtolower($displayBrowser)) {
                                            'chrome' => 'background-color: #f59e0b;',
                                            'edge' => 'background-color: #3b82f6;',
                                            'firefox' => 'background-color: #ef4444;',
                                            'brave' => 'background-color: #f97316;',
                                            default => 'background-color: #64748b;'
                                        };
                                    @endphp
                                    <span class="badge" style="{{ $colorClass }}">{{ $displayBrowser }}</span>
                                </td>
                                <td style="color: #0f172a; font-weight: bold;">{{ $log->active_window_title }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 40px; text-align: center; color: #64748b;">
                    Não existem registros recentes de navegação web (uso de browsers) para este dispositivo.
                </div>
            @endif
        </div>
    </div>

</body>
</html>