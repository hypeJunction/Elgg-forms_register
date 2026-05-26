<?php

namespace FormsRegister;

use Elgg\IntegrationTestCase;

class ValidationActionsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    /**
     * @return string
     */
    public function getPluginID(): string {
        return 'forms_register';
    }

    /**
     * @return void
     */
    public function testAssertValidUsernameAcceptsValid(): void {
        $threw = false;
        try {
            elgg()->accounts->assertValidUsername('valid_name_' . substr(md5((string) mt_rand()), 0, 6));
        } catch (\Elgg\Exceptions\Configuration\RegistrationException $e) {
            $threw = true;
        }
        $this->assertFalse($threw, 'assertValidUsername should not throw for a valid username');
    }

    /**
     * @return void
     */
    public function testAssertValidUsernameRejectsTooShort(): void {
        $this->expectException(\Elgg\Exceptions\Configuration\RegistrationException::class);
        elgg()->accounts->assertValidUsername('ab');
    }

    /**
     * @return void
     */
    public function testGetUserByUsernameReturnsNullForAvailable(): void {
        $random = 'nonexistent_' . substr(md5((string) mt_rand()), 0, 10);
        $this->assertNull(\elgg_get_user_by_username($random));
    }

    /**
     * @return void
     */
    public function testGetUserByUsernameFindsExisting(): void {
        $user = $this->createUser();
        $found = \elgg_get_user_by_username($user->username);
        $this->assertNotNull($found);
        $this->assertSame($user->guid, $found->guid);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testBootstrapEventRegistrationsArePresent(): void {
        $events = \_elgg_services()->events;
        $handlers = $events->getAllHandlers();
        $this->assertArrayHasKey('action', $handlers);
        $this->assertArrayHasKey('register', $handlers['action']);
    }
}
