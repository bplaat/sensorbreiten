<?php

// The validation function
function validate(array $values): array {
    $fields = [];
    $errors = [];

    foreach ($values as $key => $rules) {
        if (!is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $value = request($key);
        $fields[$key] = $value;
        $niceKey = str_replace('_', ' ', $key);

        foreach ($rules as $rule) {
            if (is_callable($rule)) {
                $rule($key, $niceKey, $value, function ($errorString) use (&$errors, $key) {
                    $errors[$key][] = $errorString;
                });
            } else {
                $ruleParts = explode(':', $rule);
                $ruleType = $ruleParts[0];
                $ruleArgs = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

                // String size rules
                if ($ruleType == 'required' && !(strlen($value) >= 0)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field is required';
                }
                if ($ruleType == 'min' && !(strlen($value) >= $ruleArgs[0])) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be at least ' . $ruleArgs[0] . ' characters long';
                }
                if ($ruleType == 'max' && !(strlen($value) <= $ruleArgs[0])) {
                    $errors[$key][] = 'The ' . $niceKey . ' field can be a maximum of ' . $ruleArgs[0] . ' characters';
                }
                if ($ruleType == 'size' && !(strlen($value) == $ruleArgs[0])) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be ' . $ruleArgs[0] . ' characters long';
                }

                // Type rules
                if ($ruleType == 'int' && !(is_numeric($value) != false && $value == round($value))) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be a rounded number';
                }
                if ($ruleType == 'float' && !(is_numeric($value) != false)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be a number';
                }
                if ($ruleType == 'email' && !(filter_var($value, FILTER_VALIDATE_EMAIL) != false)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be an email address';
                }
                if ($ruleType == 'url' && !(filter_var($value, FILTER_VALIDATE_URL) != false)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be an url';
                }
                if ($ruleType == 'date' && !(strtotime($value) != false)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be an date';
                }

                // Number rules
                if ($ruleType == 'number_min' && !($value >= $ruleArgs[0])) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be at least ' . $ruleArgs[0] . ' or higher';
                }
                if ($ruleType == 'number_max' && !($value <= $ruleArgs[0])) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be a maximum of ' . $ruleArgs[0] . ' or lower';
                }
                if ($ruleType == 'number_between' && !($value >= $ruleArgs[0] && $value <= $ruleArgs[1])) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be between ' . $ruleArgs[0] . ' and ' . $ruleArgs[1];
                }

                // String rules
                if ($ruleType == 'same' && !($value == request($ruleArgs[0]))) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be the same as the ' . $ruleArgs[0] . ' field';
                }
                if ($ruleType == 'different' && !($value != request($ruleArgs[0]))) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be different as the ' . $ruleArgs[0] . ' field';
                }
                if ($ruleType == 'confirmed' && !($value == request($key . '_confirmation'))) {
                    $errors[$key][] = 'The ' . $niceKey . ' fields must be the same';
                }

                // Database rules
                if ($ruleType == 'exists' && !(($ruleArgs[0] . '::select')([ ($ruleArgs[1] ?? $key) => $value ])->rowCount() == 1)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must refer to something that exists';
                }
                if ($ruleType == 'exists_except') {
                    $query = ($ruleArgs[0] . '::select')([ $ruleArgs[1] => $value ]);
                    if (!($query->rowCount() == 1 || $query->fetch()->{$ruleArgs[1]} == $ruleArgs[2])) {
                        $errors[$key][] = 'The ' . $niceKey . ' field must refer to something that exists';
                    }
                }

                if ($ruleType == 'unique' && !(($ruleArgs[0] . '::select')([ ($ruleArgs[1] ?? $key) => $value ])->rowCount() == 0)) {
                    $errors[$key][] = 'The ' . $niceKey . ' field must be unique';
                }
                if ($ruleType == 'unique_except') {
                    $query = ($ruleArgs[0] . '::select')([ $ruleArgs[1] => $value ]);
                    if (!($query->rowCount() == 0 || $query->fetch()->{$ruleArgs[1]} == $ruleArgs[2])) {
                        $errors[$key][] = 'The ' . $niceKey . ' field must be unique';
                    }
                }
            }
        }
    }

    if (count($errors) > 0) {
        Redirect::back()->withInput()->withErrors($errors)->run();
    }

    return $fields;
}

// Start session
Session::start(config('session.cookie_name'));
