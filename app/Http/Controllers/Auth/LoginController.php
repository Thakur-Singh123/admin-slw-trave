<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //Function for login
    public function login() {
        return view('auth.login');
    }

    //Function for submit login
    public function submit_login(Request $request) {
        //Validate input fields
        $request->validate(
            [
                'adm_email' => 'required|email',
                'adm_password' => 'required',
            ],
            [
                'adm_email.required' => 'Please enter your email.',
                'adm_email.email' => 'Please enter a valid email address.',
                'adm_password.required' => 'Please enter your password.',
            ]
        );

        //Get admin by email
        $admin = Admin::where('adm_email', $request->adm_email)->first();
        //Check if email exists or not
        if (!$admin) {
            return back()
                ->withInput($request->only('adm_email'))
                ->withErrors([
                    'adm_email' => 'This email is wrong. Please enter the correct email.',
                ]);
        }

        //Check account status
        if (strtolower(trim($admin->adm_status ?? '')) !== 'active') {
            return back()
                ->withInput($request->only('adm_email'))
                ->withErrors([
                    'adm_email' => 'Your account is not activated. Please contact administrator.',
                ]);
        }

        //Check password directly from database
        if ($request->adm_password !== $admin->adm_password) {
            return back()
                ->withInput($request->only('adm_email'))
                ->withErrors([
                    'adm_password' => 'Your password is wrong. Please enter the correct password.',
                ]);
        }

        //Login successful
        Auth::login($admin);
        $request->session()->regenerate();
        return redirect('admin/dashboard');
    }

    //Function for logout
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}