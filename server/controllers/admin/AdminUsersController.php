<?php

class AdminUsersController {
    // Admin users index route
    public static function index() {
        // Get all the users
        $users = Users::all();

        // Return the admin users index view
        return view('admin.users.index', [ 'users' => $users ]);
    }

    // Admin users store route
    public static function store() {
        // Validate input
        $fields = validate([
            'firstname' => 'required|min:2|max:48',
            'lastname' => 'required|min:2|max:48',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|int|number_between:' . Users::ROLE_NORMAL . ',' . Users::ROLE_ADMIN
        ]);

        // Create user
        $user = Users::create([
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'email' => $fields['email'],
            'password' => password_hash($fields['password'], PASSWORD_DEFAULT),
            'role' => $fields['role']
        ]);

        // Go to admin users show page
        return Redirect::route('admin.users.show', $user);
    }

    // Admin users show route
    public static function show($user) {
        return view('admin.users.show', [ 'user' => $user ]);
    }

    // Admin users edit route
    public static function edit($user) {
        return view('admin.users.edit', [ 'user' => $user ]);
    }

    // Admin users update route
    public static function update($user) {
        // Validate input
        $fields = validate([
            'firstname' => 'required|min:2|max:48',
            'lastname' => 'required|min:2|max:48',
            'email' => 'required|email|max:255|unique_except:users,email,' . $user->email,
            'role' => 'required|int|number_between:' . Users::ROLE_NORMAL . ',' . Users::ROLE_ADMIN
        ]);

        // Update user
        Users::update($user, [
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'email' => $fields['email'],
            'role' => $fields['role'],
        ]);

        // When password is not empty update it
        if (request('password') != null) {
            $fields = validate([
                'password' => 'required|min:6|confirmed'
            ]);

            // Update user password
            Users::update($user, [
                'password' => $fields['password']
            ]);
        }

        // Go to admin users show page
        return Redirect::route('admin.users.show', $user);
    }

    // Admin users delete route
    public static function delete($user) {
        // Delete user
        Users::delete($user);

        // Go back to admin users index page
        return Redirect::route('admin.users.index');
    }
}
