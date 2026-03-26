<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório - {{ $device->hostname }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        
        /* Quebras de Página */
        .page-break { page-break-after: always; }
        
        /* CAPA */
        .cover { text-align: center; margin-top: 150px; }
        .cover img { max-width: 200px; margin-bottom: 50px; }
        .cover h1 { font-size: 32px; color: #1e3a8a; margin-bottom: 10px; }
        .cover h2 { font-size: 24px; color: #4b5563; margin-bottom: 50px; }
        .cover .meta { font-size: 14px; color: #6b7280; margin-top: 200px; }

        /* Títulos Padrão */
        h2.section-title { border-bottom: 2px solid #1e3a8a; padding-bottom: 5px; color: #1e3a8a; margin-top: 30px; }
        
        /* Tabelas */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #d1d5db; padding: 10px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        
        /* Sumário */
        .toc-list { list-style: none; padding: 0; font-size: 16px; line-height: 2.5; }
        .toc-list li { border-bottom: 1px dotted #ccc; display: flex; justify-content: space-between; }
        .toc-list li span.page { float: right; font-weight: bold; }

        /* Badges */
        .badge { padding: 3px 8px; border-radius: 12px; font-size: 10px; color: white; font-weight: bold; }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #ef4444; }
    </style>
</head>
<body>

    <script type="text/php">
        if (isset($pdf)) {
            $x = 500;
            $y = 810;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("helvetica", "bold");
            $size = 9;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            
            // Desenha a numeração em todas as páginas
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

    <div class="cover page-break">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo Empresa">
        @endif
        <h1>Relatório de Auditoria de Dispositivo</h1>
        <h2>Hostname: {{ $device->hostname }}</h2>
        
        <div class="meta">
            <p><strong>Gerado por:</strong> Sistema GDI</p>
            <p><strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="page-break">
        <h2 class="section-title">Sumário</h2>
        <ul class="toc-list">
            <li>1. Informações Gerais do Dispositivo <span class="page">Pág. 3</span></li>
            <li>2. Softwares Não Autorizados Detectados <span class="page">Pág. 3</span></li>
            <li>3. Histórico de Atividades <span class="page">Pág. 4</span></li>
        </ul>
    </div>

    <h2 class="section-title">1. Informações Gerais do Dispositivo</h2>
    <table>
        <tr>
            <th width="25%">Hostname</th>
            <td width="25%">{{ $device->hostname }}</td>
            <th width="25%">Endereço IP</th>
            <td width="25%">{{ $device->ip_address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Usuário Atual</th>
            <td>{{ $device->current_user ?? 'Sem Registo' }} @if($isVip) (VIP) @endif</td>
            <th>Localização</th>
            <td>{{ $device->city ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if($device->is_blocked) <span class="badge bg-red">Bloqueado</span> @else <span class="badge bg-green">Ativo</span> @endif
            </td>
            <th>Compliance</th>
            <td><strong>{{ $complianceLevel }}%</strong></td>
        </tr>
    </table>

    <h2 class="section-title" style="margin-top: 40px;">2. Softwares Não Autorizados Detectados</h2>
    @if($unauthorizedApps->count() > 0 && !$isVip)
        <table>
            <thead>
                <tr>
                    <th width="70%">Nome do Software</th>
                    <th width="30%">Versão</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unauthorizedApps as $app)
                    <tr>
                        <td>{{ $app->name }}</td>
                        <td>{{ $app->version ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>A máquina encontra-se 100% em compliance com as regras de software da empresa ou o utilizador é VIP.</p>
    @endif

    <div class="page-break"></div>

    <h2 class="section-title">3. Histórico de Atividades (Últimos 100)</h2>
    <table>
        <thead>
            <tr>
                <th width="20%">Data / Hora</th>
                <th width="20%">Usuário</th>
                <th width="60%">Ação / Processo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activityLogs as $log)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($log->event_at)->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->username }}</td>
                    <td>{{ $log->process_name }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center;">Nenhum registo encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>