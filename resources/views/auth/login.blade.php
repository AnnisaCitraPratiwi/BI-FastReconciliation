<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')

@section('title', 'Login - BI Fast Reconciliation')
@push('head')
    <link rel="icon" href="{{ asset('assets/images/logo-bl1.png') }}" type="image/png">
@endpush
@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative" 
     style="background-image: url('/assets/images/bl-building.jpg'); background-size: cover; background-position: center;">
    <!-- Overlay untuk memberikan efek gelap pada background -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="row bg-white rounded-lg shadow-lg overflow-hidden flex-column flex-md-row" style="border-radius: 18px;">
                    <!-- Left Side - Logo -->
                    <div class="col-12 col-md-5 d-flex flex-column align-items-center justify-content-center" style="background: #F3E5DB;">
                        <div class="w-100 text-center my-4">
                            <img src="{{ asset('assets/images/logo-bifast.png') }}"
                                 alt="Bank Lampung Logo"
                                 class="img-fluid"
                                 style="max-width: 220px; width: 80%; height: auto;" />
                        </div>
                    </div>
                    <!-- Right Side - Login Form -->
                    <div class="col-12 col-md-7 p-5 d-flex flex-column justify-content-center">
                        <div class="text-end mb-4">
                            <span class="text-muted small">Don't have an account?</span>
                            <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: #2d3748;">Sign up</a>
                        </div>

                        <div class="mb-4">
                            <h3 class="fw-bold mb-1" style="color: #2d3748;">Login</h3>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold" style="color: #4a5568;">E-Mail</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-envelope" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                        placeholder="Enter your email" value="{{ old('email') }}" required
                                        style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold" style="color: #4a5568;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-lock" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" 
                                        placeholder="Enter your password" required
                                        style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="text-end mb-4">
                                <a href="{{ route('forgot.password') }}" class="text-decoration-none small" style="color: #4a5568;">Forgot Password ?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg py-3 fw-semibold"
                                        style="background: #4c51bf; border: none; border-radius: 8px; color: white;">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .input-group-text {
        border-radius: 8px 0 0 8px !important;
    }
    .form-control {
        border-radius: 0 8px 8px 0 !important;
    }
    .form-control:focus {
        border-color: #4c51bf !important;
        box-shadow: 0 0 0 0.2rem rgba(76, 81, 191, 0.22) !important;
    }
    .btn:hover, .btn:focus {
        background: #434190 !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    /* Responsive tweak for login card */
    @media (max-width: 768px) {
        .min-vh-100 > .container {
            padding-top: 5vw;
            padding-bottom: 5vw;
        }
        .img-fluid {
            max-width: 150px;
        }
        .rounded-lg {
            border-radius: 11px !important; 
        }
        .p-5 {
            padding: 1.5rem !important;
        }
    }
</style>
@endsection
