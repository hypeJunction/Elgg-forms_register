<?php

namespace FormsRegister;

use Elgg\IntegrationTestCase;

/**
 * Smoke tests for the public validation endpoints.
 *
 * These tests invoke the underlying Elgg services the action files call
 * (assertValidUsername and get_user_by_username) rather than dispatching
 * the action directly, because IntegrationTestCase has no executeAction().
 */
class ValidationActionsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return 'forms_register';
    }

    public function testAssertValidUsernameAcceptsValid(): void {
        $threw = false;
        try {
            elgg()->accounts->assertValidUsername('valid_name_' . substr(md5((string) mt_rand()), 0, 6));
        } catch (\Elgg\Exceptions\Configuration\RegistrationException $e) {
            $threw = true;
        }
        $this->assertFalse($threw, 'assertValidUsername should not throw for a valid username');
    }

    public function testAssertValidUsernameRejectsTooShort(): void {
        $this->expectException(\Elgg\Exceptions\Configuration\RegistrationException::class);
        elgg()->accounts->assertValidUsername('ab');
    }

    public function testGetUserByUsernameReturnsNullForAvailable(): void {
        $random = 'nonexistent_' . substr(md5((string) mt_rand()), 0, 10);
        $this->assertFalse(get_user_by_username($random));
    }

    public function testGetUserByUsernameFindsExisting(): void {
        $user = $this->createUser();
        $found = get_user_by_username($user->username);
        $this->assertNotNull($found);
        $this->assertSame($user->guid, $found->guid);
    }

    public function testRegisterFormViewRenders(): void {
        $html = \elgg_view_form('register', [], [
            'friend_guid' => 0,
            'invitecode' => '',
        ]);
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
    }

    public function testPluginSettingsViewRenders(): void {
        $plugin = \elgg_get_plugin_from_id('forms_register');
        if (!$plugin) {
            $this->markTestSkipped('forms_register plugin entity not present');
        }
        $html = \elgg_view('plugins/forms_register/settings', ['entity' => $plugin]);
        $this->assertIsString($html);
        $this->assertStringContainsString('min_password_strength', $html);
        $this->assertStringContainsString('autogen_username', $html);
    }

    public function testBootstrapHookRegistrationsArePresent(): void {
        $hooks = \_elgg_services()->hooks;
        $handlers = $hooks->getAllHandlers();
        // action,register is registered by elgg-plugin.php
        $this->assertArrayHasKey('action', $handlers);
        $this->assertArrayHasKey('register', $handlers['action']);
    }
}
