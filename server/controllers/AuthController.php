<?php

class AuthController {
    // Api auth login route
    public static function login() {
        $fields = validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::login($fields['email'], $fields['password'])) {
            return Redirect::route('home');
        } else {
            return Redirect::route('auth.login')->withInput()->withErrors([
                'email' => [
                    'Incorrect email or password'
                ]
            ]);
        }
    }

    // Api auth logout route
    public static function logout() {
        Auth::logout();

        return Redirect::route('auth.login')
            ->with('message', 'You have logged out successfully');
    }
}
