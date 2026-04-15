<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Auditoria GDI - Confidencial</title>
    <style>
        /* === NÚCLEO DE PERFORMANCE === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 landscape; margin: 0; }
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; background: #ffffff; -webkit-font-smoothing: antialiased; }
        
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* === CAPA ULTRA-PREMIUM === */
        .cover { 
            height: 210mm; width: 297mm; 
            background-color: #060b14; /* Deep Obsidian */
            color: #f8fafc; 
            position: relative; 
            overflow: hidden; 
        }
        
        /* Malha/Grid Tecnológico no Fundo (Rápido para renderizar) */
        .cover-grid {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;
            background-image: 
                linear-gradient(rgba(14, 165, 233, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(14, 165, 233, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        /* Acentos Visuais Geométricos */
        .cover-accent-1 { position: absolute; top: -10%; right: -5%; width: 40%; height: 120%; background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(6, 11, 20, 0) 100%); transform: rotate(15deg); z-index: 2; border-left: 1px solid rgba(14, 165, 233, 0.2); }
        .cover-accent-2 { position: absolute; bottom: 10%; left: 0; width: 60%; height: 2px; background: linear-gradient(90deg, rgba(14, 165, 233, 0.8) 0%, transparent 100%); z-index: 2; }
        
        .cover-content { position: relative; padding: 50mm 40mm; z-index: 10; }
        
        /* Marcação de Segurança Superior */
        .security-badge { display: inline-block; border: 1px solid #0ea5e9; color: #0ea5e9; font-size: 8px; text-transform: uppercase; letter-spacing: 2px; padding: 6px 12px; margin-bottom: 30px; background: rgba(14, 165, 233, 0.05); }

        .cover-tag { color: #94a3b8; text-transform: uppercase; letter-spacing: 4px; font-weight: 600; margin-bottom: 15px; font-size: 11px; }
        .cover-title { font-size: 46px; font-weight: 800; line-height: 1.1; margin-bottom: 25px; color: #ffffff; letter-spacing: -1px; }
        .cover-subtitle { font-size: 16px; color: #cbd5e1; font-weight: 300; border-left: 3px solid #0ea5e9; padding-left: 20px; max-width: 600px; line-height: 1.5; }
        
        .cover-footer { position: absolute; bottom: 25mm; left: 40mm; width: calc(100% - 80mm); display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; z-index: 10; }
        .cover-meta { display: inline-block; margin-right: 50px; }
        .meta-label { font-size: 8px; text-transform: uppercase; color: #64748b; display: block; letter-spacing: 1.5px; margin-bottom: 4px; }
        .meta-value { font-size: 16px; font-weight: 600; color: #e2e8f0; }

        /* Marca d'água gigante no canto inferior direito */
        .watermark { position: absolute; bottom: -20px; right: -20px; font-size: 200px; font-weight: 900; color: rgba(255,255,255,0.02); z-index: 2; line-height: 1; letter-spacing: -10px; pointer-events: none; }

        /* === INTERIOR (Páginas Claras para Contraste) === */
        .page-padding { padding: 20mm; height: 210mm; position: relative; }
        
        /* Cabeçalho das Páginas Internas */
        .section-header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 30px; display: table; width: 100%; }
        .sh-col-1 { display: table-cell; text-align: left; vertical-align: bottom; }
        .sh-col-2 { display: table-cell; text-align: right; vertical-align: bottom; }
        .brand { font-size: 16px; font-weight: 800; color: #0f172a; letter-spacing: 1px; }
        .brand span { color: #0ea5e9; font-weight: 400; }
        .doc-id { font-size: 8px; color: #64748b; font-family: monospace; letter-spacing: 1px; }

        /* === SUMÁRIO ESTRUTURADO === */
        .toc-title { font-size: 22px; font-weight: 800; margin-bottom: 40px; color: #0f172a; text-transform: uppercase; letter-spacing: 2px; }
        .toc-item { border-bottom: 1px solid #e2e8f0; padding: 18px 0; display: table; width: 100%; }
        .toc-num-cell { display: table-cell; width: 40px; vertical-align: top; }
        .toc-content-cell { display: table-cell; vertical-align: top; }
        .toc-number { color: #0ea5e9; font-weight: 800; font-size: 16px; }
        .toc-text { font-size: 14px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px; }
        .toc-desc { font-size: 10px; color: #64748b; }

        /* === DASHBOARD === */
        .kpi-row { display: table; width: 100%; border-spacing: 15px 0; margin-left: -7.5px; margin-bottom: 30px; }
        .kpi-card { display: table-cell; background: #ffffff; border: 1px solid #cbd5e1; border-top: 3px solid #0f172a; padding: 20px; width: 25%; }
        .kpi-card.highlight { border-top: 3px solid #0ea5e9; background: #f8fafc; }
        .kpi-title { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
        .kpi-val { font-size: 28px; font-weight: 800; color: #0f172a; margin-top: 8px; line-height: 1; }

        .dash-split { display: table; width: 100%; table-layout: fixed; }
        .dash-col { display: table-cell; width: 50%; padding-right: 30px; }
        .dash-col:last-child { padding-right: 0; padding-left: 30px; border-left: 1px solid #e2e8f0; }
        .chart-title { font-size: 11px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
        
        .stat-item { margin-bottom: 12px; }
        .stat-header { display: table; width: 100%; margin-bottom: 4px; }
        .stat-label { display: table-cell; text-align: left; font-size: 10px; color: #334155; }
        .stat-val { display: table-cell; text-align: right; font-size: 10px; font-weight: 700; color: #0f172a; }
        .compliance-bar { background: #e2e8f0; height: 4px; width: 100%; overflow: hidden; }
        .compliance-fill { height: 100%; background: #0ea5e9; }

        /* === TABELAS DE ALTA PERFORMANCE === */
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 10px; border-bottom: 2px solid #0f172a; }
        .data-table th { background: #0f172a; color: #ffffff; font-size: 8px; text-transform: uppercase; padding: 12px 10px; text-align: left; letter-spacing: 1px; border-right: 1px solid rgba(255,255,255,0.1); }
        .data-table th:last-child { border-right: none; }
        .data-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .data-table tr:nth-child(even) { background: #f8fafc; }
        
        /* STATUS BADGES REFINADOS */
        .badge { padding: 4px 8px; font-weight: 700; font-size: 7px; text-transform: uppercase; letter-spacing: 1px; border-radius: 2px; }
        .bg-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .bg-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .bg-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        
        .score-box { display: inline-block; padding: 4px 0; width: 40px; text-align: center; font-weight: 800; font-size: 10px; }
    </style>
</head>
<body>

    <div class="cover page-break">
        <div class="cover-grid"></div>
        <div class="cover-accent-1"></div>
        <div class="cover-accent-2"></div>
        <div class="watermark">GDI</div>
        
        <div class="cover-content">
            <div class="security-badge">Documento Restrito &bull; TLP: Amber</div>
            <div class="cover-tag">Relatório Analítico de Sistemas</div>
            <h1 class="cover-title">Auditoria de Inventário<br>e Conformidade de Ativos</h1>
            <div class="cover-subtitle">Mapeamento estrutural, identificação de vulnerabilidades sistêmicas e consolidação de hardware e software não homologado.</div>
        </div>
        
        <div class="cover-footer">
            <div>
                <div class="cover-meta">
                    <span class="meta-label">Data de Emissão</span>
                    <span class="meta-value">{{ now()->format('d M Y, H:i') }}</span>
                </div>
                <div class="cover-meta">
                    <span class="meta-label">Escopo Avaliado</span>
                    <span class="meta-value">{{ $totalDevices }} Máquinas</span>
                </div>
            </div>
            <div>
                <div class="cover-meta" style="margin-right: 0; text-align: right;">
                    <span class="meta-label">Compliance Global</span>
                    <span class="meta-value" style="color: #0ea5e9; font-size: 24px;">{{ $avgCompliance }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="page-padding page-break">
        <div class="section-header">
            <div class="sh-col-1"><div class="brand">GDI<span>PLATLOG</span></div></div>
            <div class="sh-col-2"><div class="doc-id">DOC_ID: GDI-{{ now()->format('Ymd-Hi') }}</div></div>
        </div>
        
        <h2 class="toc-title">Índice Analítico</h2>
        
        <div class="toc-item">
            <div class="toc-num-cell"><span class="toc-number">01</span></div>
            <div class="toc-content-cell">
                <span class="toc-text">Resumo Executivo e Dashboard de Risco</span>
                <span class="toc-desc">Visão panorâmica de conformidade global, alertas críticos e distribuição física e lógica do parque tecnológico.</span>
            </div>
        </div>
        
        <div class="toc-item">
            <div class="toc-num-cell"><span class="toc-number">02</span></div>
            <div class="toc-content-cell">
                <span class="toc-text">Matriz de Inventário e Status de Endpoint</span>
                <span class="toc-desc">Listagem técnica detalhada de dispositivos, especificações de hardware (CPU, RAM, Disco) e status de rede.</span>
            </div>
        </div>
        
        <div class="toc-item">
            <div class="toc-num-cell"><span class="toc-number">03</span></div>
            <div class="toc-content-cell">
                <span class="toc-text">Mapeamento de Software Não Homologado</span>
                <span class="toc-desc">Relatório de incidentes e identificação de aplicações fora da política de segurança instaladas no ambiente.</span>
            </div>
        </div>
    </div>

    <div class="page-padding page-break">
        <div class="section-header">
            <div class="sh-col-1"><div class="brand">01. <span>DASHBOARD ANALÍTICO</span></div></div>
            <div class="sh-col-2"><div class="doc-id">Visão Geral do Parque</div></div>
        </div>
        
        <div class="kpi-row">
            <div class="kpi-card highlight">
                <span class="kpi-title">Média de Compliance</span>
                <div class="kpi-val" style="color: #0ea5e9;">{{ $avgCompliance }}%</div>
            </div>
            <div class="kpi-card">
                <span class="kpi-title">Dispositivos Ativos</span>
                <div class="kpi-val">{{ $activeDevices }}</div>
            </div>
            <div class="kpi-card">
                <span class="kpi-title">Bloqueios Ativos</span>
                <div class="kpi-val" style="color: #dc2626;">{{ $blockedDevices }}</div>
            </div>
            <div class="kpi-card">
                <span class="kpi-title">Hosts em Risco Crítico</span>
                <div class="kpi-val">{{ $riskDistribution['Crítico (<80%)'] ?? 0 }}</div>
            </div>
        </div>

        <div class="dash-split">
            <div class="dash-col">
                <div class="chart-title">Sistemas Operacionais Identificados</div>
                @foreach(array_slice($osDistribution, 0, 6) as $os => $qtd)
                    <div class="stat-item">
                        <div class="stat-header">
                            <div class="stat-label">{{ $os }}</div>
                            <div class="stat-val">{{ $qtd }} un.</div>
                        </div>
                        <div class="compliance-bar"><div class="compliance-fill" style="width: {{ ($totalDevices > 0 ? ($qtd/$totalDevices)*100 : 0) }}%"></div></div>
                    </div>
                @endforeach
            </div>
            <div class="dash-col">
                <div class="chart-title">Distribuição de Memória (RAM)</div>
                @foreach(array_slice($ramDistribution, 0, 6) as $ram => $qtd)
                    <div class="stat-item">
                        <div class="stat-header">
                            <div class="stat-label">{{ $ram }}</div>
                            <div class="stat-val">{{ $qtd }} un.</div>
                        </div>
                        <div class="compliance-bar"><div class="compliance-fill" style="width: {{ ($totalDevices > 0 ? ($qtd/$totalDevices)*100 : 0) }}%; background: #64748b;"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach($devices->chunk(30) as $chunkIndex => $deviceChunk)
    <div class="page-padding page-break">
        <div class="section-header">
            <div class="sh-col-1"><div class="brand">02. <span>MATRIZ DE INVENTÁRIO</span></div></div>
            <div class="sh-col-2"><div class="doc-id">Página {{ $chunkIndex + 1 }}</div></div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%">Hostname</th>
                    <th style="width: 14%">Endereço IP</th>
                    <th style="width: 15%">Utilizador</th>
                    <th style="width: 25%">Processador / SO</th>
                    <th style="width: 11%">RAM / Disco</th>
                    <th style="width: 10%" class="text-center">Status</th>
                    <th style="width: 10%" class="text-center">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deviceChunk as $device)
                    <tr>
                        <td class="font-bold" style="color: #0f172a;">{{ $device->hostname }}</td>
                        <td style="font-family: monospace;">
                            {{ $device->ip_address ?? 'N/A' }}<br>
                            <span style="font-family: sans-serif; color: #94a3b8; font-size: 8px;">{{ $device->city ?? 'Local não inf.' }}</span>
                        </td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($device->current_user ?? 'Sem Registo', 15) }}<br>
                            @if($device->is_vip_view) <span class="badge bg-warning" style="padding: 2px 4px; font-size: 6px;">VIP</span> @endif
                        </td>
                        <td>
                            <span style="color: #0f172a; font-weight: 600;">{{ \Illuminate\Support\Str::limit($device->cpu ?? 'NC', 30) }}</span><br>
                            <span style="color: #64748b; font-size: 8px;">{{ \Illuminate\Support\Str::limit($device->os_version ?? 'NC', 30) }}</span>
                        </td>
                        <td>
                            <strong style="color: #0ea5e9;">{{ $device->ram ?? 'NC' }}</strong><br>
                            <span style="color: #64748b; font-size: 8px;">{{ $device->storage ?? 'NC' }}</span>
                        </td>
                        <td class="text-center">
                            @if($device->is_blocked) 
                                <span class="badge bg-error">Bloq.</span> 
                            @else 
                                <span class="badge bg-success">Ativo</span> 
                            @endif
                        </td>
                        <td class="text-center">
                            @php $score = $device->compliance_score ?? 0; @endphp
                            <span class="score-box" style="color: {{ $score >= 80 ? '#059669' : ($device->is_vip_view ? '#d97706' : '#dc2626') }};">
                                {{ $score }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="page-padding">
        <div class="section-header">
            <div class="sh-col-1"><div class="brand">03. <span>SOFTWARE NÃO HOMOLOGADO</span></div></div>
            <div class="sh-col-2"><div class="doc-id">Incidentes de Segurança</div></div>
        </div>

        @if(count($allUnauthorizedApps) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%">Aplicação Detectada</th>
                        <th style="width: 10%" class="text-center">Ocorrências</th>
                        <th style="width: 60%">Hostnames Afetados (Foco de Risco)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allUnauthorizedApps as $appName => $hostnames)
                        <tr>
                            <td class="font-bold" style="color: #dc2626;">{{ $appName }}</td>
                            <td class="text-center font-bold" style="color: #0f172a; font-size: 11px;">{{ count($hostnames) }}</td>
                            <td style="font-family: monospace; color: #475569; line-height: 1.6; white-space: normal; word-wrap: break-word;">
                                {{ implode(', ', $hostnames) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #10b981; padding: 30px; margin-top: 20px;">
                <h3 style="color: #059669; font-size: 14px; margin-bottom: 5px;">Conformidade Perfeita</h3>
                <p style="color: #64748b; font-size: 11px;">Nenhuma aplicação irregular ou fora das diretrizes de segurança foi identificada nos dispositivos auditados.</p>
            </div>
        @endif
    </div>

</body>
</html>