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
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .otp-box {
            background-color: #f8f9fa;
            border: 2px dashed #4CAF50;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #4CAF50;
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

        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Reset Password - Sistem Manajemen Inventory</h2>
        </div>

        <div class="content">
            <p>Halo,</p>
            <p>Anda menerima email ini karena ada permintaan untuk mereset password akun Anda.</p>

            <p><strong>Kode OTP Anda adalah:</strong></p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div class="warning">
                <strong>⚠️ Perhatian:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Kode OTP ini berlaku selama <strong>60 menit</strong></li>
                    <li>Jangan bagikan kode ini kepada siapa pun</li>
                    <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                </ul>
            </div>

            <p>Masukkan kode di atas pada halaman verifikasi untuk melanjutkan proses reset password.</p>

            <p>Terima kasih,<br>
                <strong>Tim Sistem Manajemen Inventory</strong>
            </p>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; 2024 Sistem Manajemen Inventory. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
