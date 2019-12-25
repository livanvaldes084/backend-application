<?php

namespace Tests\Feature\Auth;

use Tests\Facades\UserFactory;
use App\User;
use Tests\TestCase;

/**
 * Class LoginTest
 * @package Tests\Feature\Auth
 */
class LoginTest extends TestCase
{
    private const URI = 'auth/login';

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $loginData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::create();
        $this->loginData = [
            'email' => $this->user->email,
            'password' => $this->user->full_name
        ];
    }

    public function test_success()
    {
        $response = $this->postJson(self::URI, $this->loginData);
        $response->assertSuccess();

        $token = $this->user->tokens()->first()->token;
        $response->assertJson(['access_token' => $token]);
    }

    public function test_wrong_credentials()
    {
        $this->loginData['password'] = 'wrong_password';
        $response = $this->postJson(self::URI, $this->loginData);
        $response->assertUnauthorized();
    }

    public function test_disabled_user()
    {
        $this->user->active = false;
        $this->user->save();
        $response = $this->postJson(self::URI, $this->loginData);
        $response->assertError(403);
    }

    public function test_soft_deleted_user()
    {
        $this->user->delete();
        $response = $this->postJson(self::URI, $this->loginData);
        $response->assertUnauthorized();
    }

    public function test_without_params()
    {
        $response = $this->postJson(self::URI);
        $response->assertError(400);
    }

    // TODO Captcha Tests
}
