<?php

class AuthController {
    public static function login() {
        $fields = validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::login($fields['email'], $fields['password'])) {
            return Redirect::toRoute('home');
        } else {
            return Redirect::back()->withInput()->withErrors([
                'email' => [
                    'Incorrect email or password'
                ]
            ]);
        }
    }

    public static function logout() {
        Auth::logout();

        return Redirect::toRoute('auth.login')
            ->with('message', 'You have logged out successfully');
    }
}
