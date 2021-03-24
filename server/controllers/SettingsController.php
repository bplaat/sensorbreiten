<?php

class SettingsController {
    public static function settings() {
        Auth::updateSession();

        $activeSessions = Users::activeSessions(Auth::user())->fetchAll();

        return view('settings', [ 'activeSessions' => $activeSessions ]);
    }

    public static function changeDetails() {
        $fields = validate([
            'firstname' => 'required|min:2|max:191',
            'lastname' => 'required|min:2|max:191',
            'email' => 'required|email|max:191|unique_except:users,email,' . Auth::user()->email
        ]);

        Users::update(Auth::user(), [
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'email' => $fields['email']
        ]);

        return Redirect::toRoute('settings')
            ->with('message', 'Your user details have changed');
    }

    public static function changePassword() {
        $fields = validate([
            'current_password' => [
                'required',
                function ($key, $niceKey, $value, $fail) {
                    if (!password_verify($value, Auth::user()->password)) {
                        $fail('The ' . $niceKey . ' field must contain your current password');
                    }
                }
            ],
            'password' => 'required|min:6|confirmed'
        ]);

        Users::update(Auth::user(), [
            'password' => password_hash($fields['password'], PASSWORD_DEFAULT)
        ]);

        return Redirect::toRoute('settings')
            ->with('message', 'Your password has changed');
    }

    public static function revokeSession(object $session) {
        if ($session->session == Auth::session()) {
            return AuthController::logout();
        } else {
            Auth::revokeSession($session->session);

            return Redirect::toRoute('settings')
                ->with('message', 'You have revoked a session');
        }
    }
}
