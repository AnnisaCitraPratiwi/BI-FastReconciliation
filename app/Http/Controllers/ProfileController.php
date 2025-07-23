<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function updatePhoto(Request $request)
{
    $request->validate([
        'profile_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $user = Auth::user();

    // Hapus foto lama jika ada
    if ($user->profile_photo && \Storage::disk('public')->exists('profile_photos/' . $user->profile_photo)) {
        \Storage::disk('public')->delete('profile_photos/' . $user->profile_photo);
    }

    // Simpan foto baru
    $file = $request->file('profile_photo');
    $filename = uniqid().'_'.$file->getClientOriginalName();
    $file->storeAs('profile_photos', $filename, 'public');

    $user->profile_photo = $filename;
    $user->save();

    return back()->with('success', 'Profile photo updated!');
}

}
