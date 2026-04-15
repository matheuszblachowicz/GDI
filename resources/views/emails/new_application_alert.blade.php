<div>
    <!-- Be present above all else. - Naval Ravikant -->
</div>
<!DOCTYPE html>
<html lang="pt-PT" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Alerta de Segurança PlatID</title>  
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f4f7f6; }
        
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; margin: auto !important; border-radius: 0 !important; }
            .content-padding { padding: 25px 20px !important; }
            .header-padding { padding: 25px 20px !important; }
        }
    </style>
</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <center style="width: 100%; background-color: #f4f7f6;">
        
        <div style="max-width: 600px; margin: 0 auto; padding-top: 40px;"></div>

        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            
            <tr>
                <td class="header-padding" style="background-color: #dc2626; padding: 35px 40px; text-align: center;">
                    <h1 style="color: #ffffff; font-size: 26px; font-weight: 800; font-family: 'Segoe UI', sans-serif; letter-spacing: 1px; margin: 0;">
                        Alerta de <span style="color: #fca5a5;">Segurança</span>
                    </h1>
                </td>
            </tr>

            <tr>
                <td class="content-padding" style="padding: 40px; text-align: left; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 16px; line-height: 1.7; color: #334155;">
                    
                    <p style="margin-top: 0; color: #1e293b; font-weight: 600;">O sistema PlatID detectou a instalação de um novo software na rede.</p>
                    
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 20px; margin-bottom: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <tr>
                            <td style="padding: 15px 20px;">
                                <p style="margin: 0 0 10px 0; font-size: 14px;"><strong style="color: #475569;">Estação de Trabalho:</strong> <span style="color: #0f172a;">{{ $hostname }}</span></p>
                                <p style="margin: 0 0 10px 0; font-size: 14px;"><strong style="color: #475569;">Usuário Logado:</strong> <span style="color: #0f172a;">{{ $username }}</span></p>
                                <p style="margin: 0; font-size: 14px;"><strong style="color: #475569;">Aplicação Instalada:</strong> <span style="color: #dc2626; font-weight: bold;">{{ $appName }}</span></p>
                            </td>
                        </tr>
                    </table>

                    <h3 style="color: #0f172a; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;">Análise da Inteligência Artificial</h3>
                    <div style="color: #334155; font-size: 15px; line-height: 1.8; background-color: #eff6ff; padding: 20px; border-left: 4px solid #3b82f6; border-radius: 4px;">
                        {!! nl2br(e($aiAnalysis)) !!}
                    </div>

                    <hr style="border: none; border-top: 1px solid #cbd5e1; margin-top: 40px; margin-bottom: 25px;">
                    
                    <p style="font-size: 14px; color: #64748b; margin: 0; font-weight: 500;">
                        Caso suspeite tratar-se de um software malicioso ou não homologado, verifique a maquina e caso necessario aplique a politica de bloqueio.
                    </p>
                </td>
            </tr>
            
            <tr>
                <td style="background-color: #0f172a; padding: 25px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                    <p style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 13px; line-height: 1.5; color: #94a3b8; margin: 0;">
                        Este é um e-mail automático gerado pelo sistema de auditoria e segurança <strong>PlatID</strong>.<br>
                        Por favor, não responda a esta mensagem.
                    </p>
                </td>
            </tr>

        </table>

        <div style="max-width: 600px; margin: 0 auto; padding-bottom: 40px;"></div>
    </center>
</body>
</html>