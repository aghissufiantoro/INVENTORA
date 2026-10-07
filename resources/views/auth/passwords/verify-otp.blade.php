<!DOCTYPE html>
<html>

<head>
    <title>Verifikasi OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .otp-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
        }

        .countdown {
            font-size: 14px;
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Verifikasi Kode OTP</h5>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <p class="text-muted">
                            Kode OTP telah dikirim ke <strong>{{ session('email') }}</strong>
                        </p>
                        <p class="text-muted small">Kode berlaku selama <span class="countdown"
                                id="countdown">60:00</span></p>

                        <form method="POST" action="{{ route('password.verify.otp') }}">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('email') }}">

                            <div class="mb-3">
                                <label for="otp_code" class="form-label">Masukkan Kode OTP (6 Digit)</label>
                                <input type="text"
                                    class="form-control otp-input @error('otp_code') is-invalid @enderror"
                                    id="otp_code" name="otp_code" maxlength="6" pattern="[0-9]{6}"
                                    placeholder="000000" required autofocus>
                                @error('otp_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                Verifikasi OTP
                            </button>
                        </form>

                        <form method="POST" action="{{ route('password.resend.otp') }}" id="resendForm">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('email') }}">
                            <button type="submit" class="btn btn-outline-secondary w-100" id="resendBtn">
                                Kirim Ulang Kode OTP
                            </button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="{{ route('password.request') }}" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Ubah Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format input OTP (hanya angka)
        document.getElementById('otp_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Countdown timer 60 menit
        let timeLeft = 3600; // 60 menit dalam detik
        const countdownElement = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');

        const timer = setInterval(function() {
            timeLeft--;

            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;

            countdownElement.textContent =
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');

            if (timeLeft <= 0) {
                clearInterval(timer);
                countdownElement.textContent = 'Kode telah kadaluarsa';
                resendBtn.disabled = false;
            }
        }, 1000);
    </script>
</body>

</html>
