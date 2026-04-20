<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Auditoria - {{ $device->hostname }}</title>
    <style>
        /* === CONFIGURAÇÕES GERAIS === */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page { margin: 0px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .content-wrap { padding: 40px; }
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
        .text-center { text-align: center; } 
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; } 
        .text-red { color: #ef4444; }
        
        /* === CAPA ESTRUTURADA === */
        .cover-page { background-color: #0f172a; color: white; padding: 0 60px; height: 1050px; overflow: hidden; box-sizing: border-box; }
        .cover-content { padding-top: 250px; }
        .cover-header { border-left: 8px solid #3b82f6; padding-left: 30px; margin-bottom: 200px; }
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
        
        .data-table { width: 100%; border-collapse: collapse; background-color: #ffffff; border: 1px solid #cbd5e1; margin-bottom: 20px; }
        .data-table th { background-color: #1e293b; color: #ffffff; font-size: 8px; text-transform: uppercase; padding: 8px; text-align: left; }
        .data-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 8px; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; color: #ffffff; text-align: center; }
        .bg-emerald { background-color: #10b981; } .bg-red { background-color: #ef4444; } .bg-amber { background-color: #f59e0b; }
        
        /* Utilitários Extras para Single */
        .hardware-grid { display: table; width: 100%; margin-bottom: 20px; border-spacing: 10px 0; margin-left: -10px; }
        .hardware-col { display: table-cell; width: 50%; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; }
        .hw-item { margin-bottom: 10px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 5px; }
        .hw-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .hw-label { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; display: block; }
        .hw-value { font-size: 12px; color: #0f172a; font-weight: bold; margin-top: 2px; }
    </style>
</head>
<body>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(750, 570, "Pág. {PAGE_NUM} / {PAGE_COUNT}", $fontMetrics->get_font("helvetica", "bold"), 8, array(0.4, 0.4, 0.4));
        }
    </script>

    <div class="cover-page page-break">
        <div class="cover-content">
            <div class="cover-header">
                <h3>Confidencial - Uso Interno</h3>
                <h1>Auditoria de Dispositivo<br>e Inventário de Hardware</h1>
                <h2>Hostname: {{ $device->hostname }}</h2>
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
                            <span class="label">Identificação do Ativo</span>
                            <span class="value">{{ $device->current_user ?? ($device->username ?? 'Utilizador Desconhecido') }}</span>
                            <span class="sub-value">
                                @if($device->is_blocked) 
                                    <span style="color: #ef4444; font-weight: bold;">Bloqueado</span>
                                @else 
                                    <span style="color: #10b981; font-weight: bold;">Ativo</span>
                                @endif
                                &bull; {{ $device->ip_address ?? 'IP Indisponível' }}
                            </span>
                        </td>
                        <td width="35%" style="padding-left: 20px;">
                            <span class="label">Status de Compliance</span>
                            <span class="value" style="color: {{ $complianceLevel >= 90 ? '#10b981' : ($complianceLevel >= 70 ? '#3b82f6' : '#ef4444') }};">{{ $complianceLevel }}% Conformidade</span>
                            <span class="sub-value" style="color: #94a3b8;">Baseado nas políticas GDI</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="page-break" style="background: #ffffff; height: 1050px; box-sizing: border-box;">
        <div class="page-header">
            <table><tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Sumário Executivo - {{ $device->hostname }}</td></tr></table>
        </div>
        
        <div class="content-wrap">
            <h2 class="toc-title">Índice do Relatório Individual</h2>
            <table class="toc-table">
                <tr><td width="90%"><strong>1. Informações de Hardware e Sistema</strong><br><span style="font-size: 11px;">Especificações técnicas do equipamento e sistema operativo.</span></td><td width="10%" class="text-right"><span style="color: #3b82f6; font-weight: bold;">Sec. 1</span></td></tr>
                <tr><td><strong>2. Conformidade e Software Irregular</strong><br><span style="font-size: 11px;">Lista de aplicações instaladas que não estão homologadas.</span></td><td class="text-right"><span style="color: #3b82f6; font-weight: bold;">Sec. 2</span></td></tr>
            </table>
        </div>
    </div>

    <div class="page-header">
        <table><tr><td class="brand">GDI <span>Security Audit</span></td><td class="doc-title">Relatório Detalhado &bull; {{ now()->format('d/m/Y') }}</td></tr></table>
    </div>

    <div class="content-wrap">
        
        <h2 class="section-title">1. Informações de Hardware e Sistema</h2>
        
        <div class="hardware-grid">
            <div class="hardware-col">
                <div class="hw-item">
                    <span class="hw-label">Sistema Operacional</span>
                    <span class="hw-value">{{ $device->os_version ?? 'Não informado' }}</span>
                </div>
                <div class="hw-item">
                    <span class="hw-label">Processador (CPU)</span>
                    <span class="hw-value">{{ $device->cpu ?? 'Não informado' }}</span>
                </div>
                <div class="hw-item">
                    <span class="hw-label">Memória RAM Instalada</span>
                    <span class="hw-value">{{ $device->ram ?? 'Não informado' }}</span>
                </div>
            </div>
            <div class="hardware-col">
                <div class="hw-item">
                    <span class="hw-label">Armazenamento Principal (Disco)</span>
                    <span class="hw-value">
                        {{ $device->storage ?? 'Não informado' }} 
                        @if($device->disk_type && $device->disk_type !== 'Unspecified')
                            <span style="color: #3b82f6;">({{ $device->disk_type }})</span>
                        @endif
                    </span>
                </div>
                <div class="hw-item">
                    <span class="hw-label">Endereço IP Local</span>
                    <span class="hw-value">{{ $device->ip_address ?? 'Não informado' }}</span>
                </div>
                <div class="hw-item">
                    <span class="hw-label">Status VIP / Privilégios</span>
                    <span class="hw-value">
                        @if($device->is_vip)
                            <span class="badge bg-amber">Usuário VIP</span>
                        @else
                            Usuário Padrão
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <h2 class="section-title" style="margin-top: 20px;">2. Conformidade e Software Irregular</h2>
        
        @if($unauthorizedApps->count() > 0)
            <table class="data-table">
                <thead>
                    <tr><th width="70%">Nome da Aplicação Não Homologada</th><th width="30%">Versão Detectada</th></tr>
                </thead>
                <tbody>
                    @foreach($unauthorizedApps as $app)
                        <tr class="avoid-break">
                            <td class="font-bold text-red">{{ $app->name }}</td>
                            <td style="font-family: monospace; color: #475569;">{{ $app->version ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background: #ecfdf5; border: 1px solid #10b981; padding: 20px; text-align: center; color: #047857; font-weight: bold; border-radius: 6px;">
                Nenhum software irregular identificado neste dispositivo. Compliance Perfeito.
            </div>
        @endif

    </div>
</body>
</html>