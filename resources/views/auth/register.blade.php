<!-- resources/views/auth/register.blade.php -->
@extends('layouts.app')
@push('head')
    <link rel="icon" href="{{ asset('assets/images/logo-bl1.ico') }}" type="image/x-icon">
@endpush
@section('title','Registration - BI Fast Reconciliation')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative"
     style="background-image: url('/assets/images/bl-building.jpg'); background-size: cover; background-position: center;">
    <!-- Overlay gelap -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index: 1;"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="row bg-white shadow-lg overflow-hidden flex-column flex-md-row rounded-lg" style="border-radius: 18px;">
                    <!-- Kiri: Logo Bank Lampung -->
                    <div class="col-12 col-md-5 d-flex flex-column align-items-center justify-content-center" style="background: #F3E5DB;">
                        <div class="w-100 text-center my-4">
                            <img src="{{ asset('assets/images/logo-bifast.png') }}" 
                                 alt="Bank Lampung Logo" 
                                 class="img-fluid"
                                 style="max-width: 220px; width: 80%; height: auto;"/>
                        </div>
                    </div>
                    
                    <!-- Kanan: Form Registrasi -->
                    <div class="col-12 col-md-7 p-5 d-flex flex-column justify-content-center">
                        <div class="text-end mb-4">
                            <span class="text-muted small">Already have an account?</span>
                            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: #2d3748;">Sign in</a>
                        </div>
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1" style="color: #2d3748;">Registration</h3>
                        </div>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold" style="color: #4a5568;">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-user" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" 
                                           placeholder="Enter your desired username" value="{{ old('name') }}" required
                                           style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold" style="color: #4a5568;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-envelope" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                           placeholder="Enter your email address" value="{{ old('email') }}" required
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

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold" style="color: #4a5568;">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-lock" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password_confirmation" name="password_confirmation" 
                                           placeholder="Confirm your password" required
                                           style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg py-3 fw-semibold"
                                        style="background: #4c51bf; border: none; border-radius: 8px; color: white;">
                                    Register
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
    .form-control, .form-select {
        border-radius: 0 8px 8px 0 !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4c51bf !important;
        box-shadow: 0 0 0 0.2rem rgba(76, 81, 191, 0.25) !important;
    }
    .btn:hover {
        background: #434190 !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    @media (max-width: 768px) {
        .min-vh-100 > .container {
            padding-top: 7vw;
            padding-bottom: 7vw;
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
