<?php

namespace FormsRegister;

use Elgg\IntegrationTestCase;
use Elgg\HooksRegistrationService\Hook;
use FormsRegister\Hooks;

/**
 * Integration tests for FormsRegister\Hooks.
 */
class HooksTest extends IntegrationTestCase {

    /** @var array<string,mixed> */
    private $origSettings = [];

    private $settingKeys = [
        'first_last_name',
        'autogen_name',
        'autogen_username',
        'autogen_username_algo',
        'autogen_password',
        'hide_password_repeat',
        'min_password_strength',
    ];

    public function up() {
        // Snapshot plugin settings so tests don't pollute config
        foreach ($this->settingKeys as $k) {
            $this->origSettings[$k] = elgg_get_plugin_setting($k, 'forms_register');
        }
        // Default: clear every setting
        foreach ($this->settingKeys as $k) {
            elgg_set_plugin_setting($k, '', 'forms_register');
        }
    }

    public function down() {
        foreach ($this->origSettings as $k => $v) {
            elgg_set_plugin_setting($k, (string) $v, 'forms_register');
        }
        // Clean inputs
        foreach (['first_name', 'last_name', 'email', 'name', 'username', 'password', 'password2'] as $k) {
            set_input($k, null);
        }
    }

    public function getPluginID(): string {
        return 'forms_register';
    }

    private function buildActionHook(): Hook {
        return new Hook(
            elgg()->public_container,
            'action',
            'register',
            null,
            []
        );
    }

    public function testGenerateUsernameReturnsLowercaseNonEmpty(): void {
        $username = Hooks::generateUsername('TestUser');
        $this->assertIsString($username);
        $this->assertNotEmpty($username);
        $this->assertEquals(strtolower($username), $username);
    }

    public function testGenerateUsernameWithEmptyInputStillReturnsValidUsername(): void {
        $username = Hooks::generateUsername('');
        $this->assertIsString($username);
        $this->assertNotEmpty($username);
        // Should satisfy minusername length
        $minlength = (int) (elgg_get_config('minusername') ?: 4);
        $this->assertGreaterThanOrEqual($minlength, strlen($username));
    }

    public function testGenerateUsernameStripsInvalidCharacters(): void {
        $username = Hooks::generateUsername('foo bar#baz');
        // invalid chars replaced by '.'; lowercased
        $this->assertStringNotContainsString(' ', $username);
        $this->assertStringNotContainsString('#', $username);
    }

    public function testGenerateUsernameUniquenessAgainstExistingUser(): void {
        $existing = $this->createUser();
        $taken = $existing->username;
        $generated = Hooks::generateUsername($taken);
        $this->assertNotSame($taken, $generated);
    }

    public function testPrepareActionValuesAutoGeneratesNameFromEmail(): void {
        elgg_set_plugin_setting('autogen_name', '1', 'forms_register');

        set_input('email', 'jdoe@example.com');
        set_input('name', '');
        set_input('username', 'jdoe' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', 'somePasswordXYZ!');

        $hook = $this->buildActionHook();
        $result = Hooks::prepareActionValues($hook);

        $this->assertNotFalse($result);
        $this->assertSame('jdoe', get_input('name'));
    }

    public function testPrepareActionValuesBuildsNameFromFirstLast(): void {
        elgg_set_plugin_setting('first_last_name', '1', 'forms_register');

        set_input('first_name', 'Jane');
        set_input('last_name', 'Doe');
        set_input('email', 'jane@example.com');
        set_input('name', '');
        set_input('username', 'jane' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', 'somePasswordXYZ!');

        $hook = $this->buildActionHook();
        $result = Hooks::prepareActionValues($hook);

        $this->assertNotFalse($result);
        $this->assertSame('Jane Doe', get_input('name'));
    }

    public function testPrepareActionValuesRejectsMissingFirstLast(): void {
        elgg_set_plugin_setting('first_last_name', '1', 'forms_register');

        set_input('first_name', '');
        set_input('last_name', '');
        set_input('email', 'nobody@example.com');
        set_input('name', '');

        $hook = $this->buildActionHook();
        $result = Hooks::prepareActionValues($hook);

        $this->assertFalse($result);
    }

    public function testPrepareActionValuesAutogensUsernameFirstNameOnly(): void {
        elgg_set_plugin_setting('autogen_username', '1', 'forms_register');
        elgg_set_plugin_setting('autogen_username_algo', 'first_name_only', 'forms_register');

        set_input('first_name', 'Alice');
        set_input('email', 'alice@example.com');
        set_input('username', '');
        set_input('password', 'somePasswordXYZ!');
        set_input('name', 'Alice');

        $hook = $this->buildActionHook();
        Hooks::prepareActionValues($hook);

        $u = (string) get_input('username');
        $this->assertNotEmpty($u);
        $this->assertStringStartsWith('alice', $u);
    }

    public function testPrepareActionValuesAutogensPassword(): void {
        elgg_set_plugin_setting('autogen_password', '1', 'forms_register');

        set_input('email', 'bob@example.com');
        set_input('name', 'Bob');
        set_input('username', 'bob' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', '');

        $hook = $this->buildActionHook();
        Hooks::prepareActionValues($hook);

        $pw = (string) get_input('password');
        $pw2 = (string) get_input('password2');
        $this->assertNotEmpty($pw);
        $this->assertSame($pw, $pw2);
    }

    public function testPrepareActionValuesHidePasswordRepeatCopiesPassword(): void {
        elgg_set_plugin_setting('hide_password_repeat', '1', 'forms_register');

        set_input('email', 'c@example.com');
        set_input('name', 'Carol');
        set_input('username', 'carol' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', 'topSecretZZZ!1');
        set_input('password2', '');

        $hook = $this->buildActionHook();
        Hooks::prepareActionValues($hook);

        $this->assertSame('topSecretZZZ!1', (string) get_input('password2'));
    }

    public function testRegisterUserWritesFirstLastName(): void {
        elgg_set_plugin_setting('first_last_name', '1', 'forms_register');

        $user = $this->createUser();
        set_input('first_name', 'Dan');
        set_input('last_name', 'Smith');

        $hook = new Hook(
            elgg()->public_container,
            'register',
            'user',
            true,
            ['user' => $user]
        );
        Hooks::registerUser($hook);

        $this->assertSame('Dan', $user->first_name);
        $this->assertSame('Smith', $user->last_name);
    }

    public function testRegisterUserSkipsWhenSettingOff(): void {
        elgg_set_plugin_setting('first_last_name', '', 'forms_register');

        $user = $this->createUser();
        set_input('first_name', 'Eve');
        set_input('last_name', 'Nobody');

        $hook = new Hook(
            elgg()->public_container,
            'register',
            'user',
            true,
            ['user' => $user]
        );
        Hooks::registerUser($hook);

        $this->assertEmpty($user->first_name);
    }
}
