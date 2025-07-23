<!-- resources/views/auth/registration-success.blade.php -->
@extends('layouts.app')

@section('title', 'Registration Success - BI Fast Reconciliation')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative"
     style="background-image: url('/assets/images/bl-building.jpg'); background-size: cover; background-position: center;">
    <!-- Overlay efek gelap -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index: 1;"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="bg-white rounded-lg shadow-lg p-5 text-center" style="border-radius: 18px;">
                
                    <div class="mb-4">
                        <i class="fas fa-check-circle" style="font-size: 3.5rem; color: #10b981;"></i>
                        <h3 class="fw-bold mb-3 mt-3" style="color: #2d3748;">Registration Successful!</h3>
                        <p class="text-muted mb-4">
                            Your registration has been submitted successfully.<br>
                            Please wait for master approval before you can log in to the system.
                        </p>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Please wait for approval</strong><br>
                            Your account is currently pending approval from the system master.
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('login') }}"
                           class="btn btn-lg py-3 fw-semibold"
                           style="background: #4c51bf; border: none; border-radius: 8px; color: white; text-decoration: none;">
                            Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn:hover {
        background: #434190 !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    @media (max-width: 768px){
        .rounded-lg {
            border-radius: 12px !important;
        }
        .p-5 {
            padding: 1.7rem !important;
        }
        .img-fluid {
            max-width: 110px !important;
        }
    }
</style>
@endsection
