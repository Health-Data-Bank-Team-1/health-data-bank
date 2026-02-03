<!-- resources/views/auth/verify-otp.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4 class="mb-0">Two-Factor Authentication</h4>
                    <small class="text-muted">Enter the OTP sent to your email</small>
                </div>

                <div class="card-body">

                    <!-- Display Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Display Status Message -->
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- OTP Form -->
                    <form method="POST" action="{{ route('auth.otp.verify') }}">
                        @csrf

                        <!-- Keep user email hidden in session -->
                        <input type="hidden" name="email" value="{{ session('2fa:user_email') }}">

                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP</label>
                            <input type="text" name="otp" id="otp" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" required autofocus>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                    </form>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Didn't receive OTP? 
                            <form method="POST" action="{{ route('auth.otp.resend') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">Resend</button>
                            </form>
                        </small>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>