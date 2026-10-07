<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔐 Reset Password</h2>
        </div>
        <div class="content">
            <p>Halo {{ $name ?? 'User' }},</p>
            <p>Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah untuk melanjutkan:</p>
            
            <center>
                <a href="{{ url('reset-password/' . $token) }}" class="button">
                    Reset Password Sekarang
                </a>
            </center>
            
            <div class="info-box">
                <strong>⚠️ Penting:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Link ini akan kadaluarsa dalam <strong>60 menit</strong></li>
                    <li>Jangan bagikan link ini kepada siapapun</li>
                    <li>Jika bukan Anda yang meminta, abaikan email ini</li>
                </ul>
            </div>
            
            <p>Terima kasih,<br><strong>Tim {{ config('app.name') }}</strong></p>
        </div>
        <div class="footer">
            <p>Jika tombol tidak berfungsi, copy dan paste URL berikut ke browser:</p>
            <p style="word-break: break-all; color: #667eea;">{{ url('reset-password/' . $token) }}</p>
            <p style="margin-top: 15px;">Email otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>