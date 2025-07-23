@component('mail::message')
# Reset Password

Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.

@component('mail::button', ['url' => $actionUrl])
Reset Password
@endcomponent

Link reset password ini akan kedaluwarsa dalam {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} menit.

Jika Anda tidak meminta reset password, tidak ada tindakan lebih lanjut yang diperlukan.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
