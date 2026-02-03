<!-- resources/views/auth/verify-otp.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Enter OTP</h4>
                </div>
                <div class="card-body">

                    <!-- Display validation errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Display status message -->
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- OTP verification form -->
                    <form method="POST" action="{{ route('auth.otp.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP</label>
                            <input type="text" name="otp" id="otp" class="form-control" placeholder="Enter 6-digit OTP" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                    </form>

                    <!-- Optional: Resend OTP link -->
                    <form method="POST" action="{{ route('auth.otp.send') }}" class="mt-3 text-center">
                        @csrf
                        <button type="submit" class="btn btn-link">Resend OTP</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>