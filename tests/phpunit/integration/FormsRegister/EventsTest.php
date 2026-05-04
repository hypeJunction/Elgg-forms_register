<?php

namespace FormsRegister;

use Elgg\IntegrationTestCase;
use Elgg\Event;
use FormsRegister\Events;

class EventsTest extends IntegrationTestCase {

    /** @var array<string,mixed> */
    private $origSettings = [];

    /** @var mixed */
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
        foreach ($this->settingKeys as $k) {
            $this->origSettings[$k] = elgg_get_plugin_setting($k, 'forms_register');
        }
        foreach ($this->settingKeys as $k) {
            elgg_get_plugin_from_id('forms_register')->setSetting($k, '');
        }
    }

    public function down() {
        foreach ($this->origSettings as $k => $v) {
            elgg_get_plugin_from_id('forms_register')->setSetting($k, (string) $v);
        }
        foreach (['first_name', 'last_name', 'email', 'name', 'username', 'password', 'password2'] as $k) {
            set_input($k, null);
        }
    }

    /**
     * @return string
     */
    public function getPluginID(): string {
        return 'forms_register';
    }

    /**
     * @return Event
     */
    private function buildActionEvent(): Event {
        return new Event(elgg(), 'action', 'register', null, []);
    }

    /**
     * @return void
     */
    public function testGenerateUsernameReturnsLowercaseNonEmpty(): void {
        $username = Events::generateUsername('TestUser');
        $this->assertIsString($username);
        $this->assertNotEmpty($username);
        $this->assertEquals(strtolower($username), $username);
    }

    /**
     * @return void
     */
    public function testGenerateUsernameWithEmptyInputStillReturnsValidUsername(): void {
        $username = Events::generateUsername('');
        $this->assertIsString($username);
        $this->assertNotEmpty($username);
        $minlength = (int) (elgg_get_config('minusername') ?: 4);
        $this->assertGreaterThanOrEqual($minlength, strlen($username));
    }

    /**
     * @return void
     */
    public function testGenerateUsernameStripsInvalidCharacters(): void {
        $username = Events::generateUsername('foo bar#baz');
        $this->assertStringNotContainsString(' ', $username);
        $this->assertStringNotContainsString('#', $username);
    }

    /**
     * @return void
     */
    public function testGenerateUsernameUniquenessAgainstExistingUser(): void {
        $existing = $this->createUser();
        $taken = $existing->username;
        $generated = Events::generateUsername($taken);
        $this->assertNotSame($taken, $generated);
    }

    /**
     * @return void
     */
    public function testPrepareActionValuesAutoGeneratesNameFromEmail(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('autogen_name', '1');

        set_input('email', 'jdoe@example.com');
        set_input('name', '');
        set_input('username', 'jdoe' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', 'somePasswordXYZ!');

        $event = $this->buildActionEvent();
        $result = Events::prepareActionValues($event);

        $this->assertNotFalse($result);
        $this->assertSame('jdoe', get_input('name'));
    }

    /**
     * @return void
     */
    public function testPrepareActionValuesBuildsNameFromFirstLast(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('first_last_name', '1');

        set_input('first_name', 'Jane');
        set_input('last_name', 'Doe');
        set_input('email', 'jane@example.com');
        set_input('name', '');
        set_input('username', 'jane' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', 'somePasswordXYZ!');

        $event = $this->buildActionEvent();
        $result = Events::prepareActionValues($event);

        $this->assertNotFalse($result);
        $this->assertSame('Jane Doe', get_input('name'));
    }

    /**
     * @return void
     */
    public function testPrepareActionValuesRejectsMissingFirstLast(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('first_last_name', '1');

        set_input('first_name', '');
        set_input('last_name', '');
        set_input('email', 'nobody@example.com');
        set_input('name', '');

        $event = $this->buildActionEvent();
        $result = Events::prepareActionValues($event);

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testPrepareActionValuesAutogensUsernameFirstNameOnly(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('autogen_username', '1');
        elgg_get_plugin_from_id('forms_register')->setSetting('autogen_username_algo', 'first_name_only');

        set_input('first_name', 'Alice');
        set_input('email', 'alice@example.com');
        set_input('username', '');
        set_input('password', 'somePasswordXYZ!');
        set_input('name', 'Alice');

        $event = $this->buildActionEvent();
        Events::prepareActionValues($event);

        $u = (string) get_input('username');
        $this->assertNotEmpty($u);
        $this->assertStringStartsWith('alice', $u);
    }

    /**
     * @return void
     */
    public function testPrepareActionValuesAutogensPassword(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('autogen_password', '1');

        set_input('email', 'bob@example.com');
        set_input('name', 'Bob');
        set_input('username', 'bob' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', '');

        $event = $this->buildActionEvent();
        Events::prepareActionValues($event);

        $pw = (string) get_input('password');
        $pw2 = (string) get_input('password2');
        $this->assertNotEmpty($pw);
        $this->assertSame($pw, $pw2);
    }

    /**
     * @return void
     */
    public function testPrepareActionValuesHidePasswordRepeatCopiesPassword(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('hide_password_repeat', '1');

        set_input('email', 'c@example.com');
        set_input('name', 'Carol');
        set_input('username', 'carol' . substr(md5((string) mt_rand()), 0, 6));
        set_input('password', 'topSecretZZZ!1');
        set_input('password2', '');

        $event = $this->buildActionEvent();
        Events::prepareActionValues($event);

        $this->assertSame('topSecretZZZ!1', (string) get_input('password2'));
    }

    /**
     * @return void
     */
    public function testRegisterUserWritesFirstLastName(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('first_last_name', '1');

        $user = $this->createUser();
        set_input('first_name', 'Dan');
        set_input('last_name', 'Smith');

        $event = new Event(
            elgg(),
            'register',
            'user',
            true,
            ['user' => $user]
        );
        Events::registerUser($event);

        $this->assertSame('Dan', $user->first_name);
        $this->assertSame('Smith', $user->last_name);
    }

    /**
     * @return void
     */
    public function testRegisterUserSkipsWhenSettingOff(): void {
        elgg_get_plugin_from_id('forms_register')->setSetting('first_last_name', '');

        $user = $this->createUser();
        set_input('first_name', 'Eve');
        set_input('last_name', 'Nobody');

        $event = new Event(
            elgg(),
            'register',
            'user',
            true,
            ['user' => $user]
        );
        Events::registerUser($event);

        $this->assertEmpty($user->first_name);
    }
}
