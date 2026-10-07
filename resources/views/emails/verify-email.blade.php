<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .otp-box {
            background-color: #f8f9fa;
            border: 2px dashed #2563eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 12px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Verifikasi Email - Invora System</h2>
        </div>

        <div class="content">
            <p>Halo, {{ $userName }}</p>
            <p>Terima kasih telah mendaftar di <strong>Invora System</strong>. Untuk menyelesaikan pendaftaran, silakan masukkan kode verifikasi berikut:</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div class="info-box">
                <strong>Informasi:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Kode ini berlaku selama <strong>60 menit</strong></li>
                    <li>Jangan bagikan kode ini kepada siapa pun</li>
                    <li>Jika Anda tidak mendaftar, abaikan email ini</li>
                </ul>
            </div>

            <p>Masukkan kode di atas pada halaman verifikasi untuk mengaktifkan akun Anda.</p>

            <p>Terima kasih,<br>
                <strong>Tim Invora System</strong></p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Invora System - Toko Karomah. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
