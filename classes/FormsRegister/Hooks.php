<?php

namespace FormsRegister;

use Elgg\Hook;

class Hooks
{
    /**
     * Validates and prepares values for 'register' action
     *
     * @param \Elgg\Hook $hook 'action', 'register'
     * @return bool|null
     */
    public static function prepareActionValues(Hook $hook)
    {

        \elgg_make_sticky_form('register');

        $first_name = \get_input('first_name');
        $last_name = \get_input('last_name');
        $email = \get_input('email', '');
        $name = \get_input('name');
        $username = \get_input('username');
        $password = \get_input('password');

        list($email_username) = explode('@', $email);

        if (\elgg_get_plugin_setting('first_last_name', 'forms_register') && !$name) {
            if (!$first_name || !$last_name) {
                \elgg_register_error_message(\elgg_echo('actions:register:error:first_last_name'));
                return false;
            }
            \set_input('name', "$first_name $last_name");
        } elseif (\elgg_get_plugin_setting('autogen_name', 'forms_register') && !$name) {
            \set_input('name', $email_username);
        }

        if (\elgg_get_plugin_setting('autogen_username', 'forms_register') && !$username) {
            $algo = \elgg_get_plugin_setting('autogen_username_algo', 'forms_register', 'first_name_only');
            switch ($algo) {
                case 'first_name_only':
                    $username = $first_name ?: $email_username;
                    break;
                case 'full_name':
                    $username = $first_name && $last_name ? "$first_name.$last_name" : $email_username;
                    break;
                case 'email':
                    $username = $email_username;
                    break;
                case 'alnum':
                    $username = '';
                    break;
            }

            $username = self::generateUsername($username);
            \set_input('username', $username);
        }

        if (\elgg_get_plugin_setting('autogen_password', 'forms_register')) {
            $password = \elgg_generate_password();
            \set_input('password', $password);
            \set_input('password2', $password);
        } else {
            if ($min_strength = \elgg_get_plugin_setting('min_password_strength', 'forms_register')) {
                $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
                $strength = $zxcvbn->passwordStrength($password);
                if ($strength < $min_strength) {
                    \elgg_register_error_message(\elgg_echo('actions:register:error:password_strength'));
                    return false;
                }
            }
            if (\elgg_get_plugin_setting('hide_password_repeat', 'forms_register')) {
                \set_input('password2', $password);
            }
        }
    }

    /**
     * Saves additional input values on user registration
     *
     * @param \Elgg\Hook $hook 'register', 'user'
     * @return void
     */
    public static function registerUser(Hook $hook)
    {

        $user = $hook->getParam('user');

        if (\elgg_get_plugin_setting('first_last_name', 'forms_register')) {
            $user->first_name = \get_input('first_name');
            $user->last_name = \get_input('last_name');
        }
    }

    /**
     * Generates a unique available and valid username
     *
     * @param string $username Username prefix
     * @return string
     */
    public static function generateUsername(string $username = ''): string
    {

        $available = false;

        $username = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
        $blacklist = '/[\x{0080}-\x{009f}\x{00a0}\x{2000}-\x{200f}\x{2028}-\x{202f}\x{3000}\x{e000}-\x{f8ff}]/u';
        $blacklist2 = [' ', '\'', '/', '\\', '"', '*', '&', '?', '#', '%', '^', '(', ')', '{', '}', '[', ']', '~', '?', '<', '>', ';', '|', "\u{00ac}", '`', '@', '-', '+', '='];
        $username = preg_replace($blacklist, '', $username);
        $username = str_replace($blacklist2, '.', $username);

        $minlength = \elgg_get_config('minusername') ?: 4;

        if ($username) {
            $fill = $minlength - strlen($username);
        } else {
            $fill = 8;
        }

        $algo = \elgg_get_plugin_setting('autogen_username_algo', 'forms_register', 'first_name_only');
        if ($algo == 'full_name' && $fill <= 0) {
            $separator = '.';
        } else {
            $separator = '';
        }

        if ($fill > 0) {
            $suffix = \_elgg_services()->crypto->getRandomString($fill);
            $username = "$username$separator$suffix";
        }

        $iterator = 0;
        while (!$available) {
            if ($iterator > 0) {
                $username = "$username$separator$iterator";
            }
            $user = \get_user_by_username($username);
            $available = !$user;
            try {
                if ($available) {
                    \elgg()->accounts->assertValidUsername($username);
                }
            } catch (\Elgg\Exceptions\Configuration\RegistrationException $e) {
                $available = false;
                if ($iterator >= 100) {
                    // too many failed attempts
                    $username = \_elgg_services()->crypto->getRandomString(8);
                }
            }
            $iterator++;
        }

        return strtolower($username);
    }
}
