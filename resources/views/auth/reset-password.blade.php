<!-- resources/views/auth/reset-password.blade.php -->
@extends('layouts.app')
@push('head')
    <link rel="icon" href="{{ asset('assets/images/logo-bl1.ico') }}" type="image/x-icon">
@endpush
@section('title', 'Reset Password - BI Fast Reconciliation')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative"
     style="background-image: url('/assets/images/bl-building.jpg'); background-size: cover; background-position: center;">
    <!-- Overlay efek gelap -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.5); z-index: 1;"></div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="row bg-white rounded-lg shadow-lg overflow-hidden flex-column flex-md-row" style="border-radius: 18px;">
                    <!-- Kiri: Branding Bank Lampung -->
                    <div class="col-12 col-md-5 d-flex flex-column align-items-center justify-content-center" style="background: #F3E5DB;">
                        <div class="w-100 text-center my-4">
                            <img src="{{ asset('assets/images/logo-bifast.png') }}" 
                                 alt="Bank Lampung Logo" 
                                 class="img-fluid"
                                 style="max-width: 220px; width: 80%; height: auto;" />
                        </div>
                    </div>
                    <!-- Kanan: Form Reset Password -->
                    <div class="col-12 col-md-7 p-5 d-flex flex-column justify-content-center">
                        <div class="mb-4">
                            <h3 class="fw-bold mb-2" style="color: #2d3748;">Reset Password</h3>
                            <p class="text-muted mb-0" style="color: #4a5568;">Enter your new password</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-envelope" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 ps-0" name="email" 
                                           value="{{ $email }}" readonly
                                           style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-lock" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" name="password" 
                                           placeholder="New Password" required
                                           style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text border-end-0" style="background: #f7fafc; border-color: #e2e8f0;">
                                        <i class="fas fa-lock" style="color: #a0aec0;"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" name="password_confirmation" 
                                           placeholder="Confirm Password" required
                                           style="background: #f7fafc; border-color: #e2e8f0; box-shadow: none;">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-lg py-3 fw-semibold"
                                        style="background: #4c51bf; border: none; border-radius: 8px; color: white;">
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div> <!-- end kanan -->
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
