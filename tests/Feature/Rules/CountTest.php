<?php

namespace Tests\Feature\Rules;

use Tests\Factories\UserFactory;
use App\Models\Rule;
use App\User;
use Tests\TestCase;

/**
 * Class CountTest
 * @package Tests\Feature\Rules
 */
class CountTest extends TestCase
{
    private const URI = 'v1/rules/count';

    /**
     * @var User
     */
    private $admin;

    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = app(UserFactory::class)
            ->withTokens()
            ->asUser()
            ->create();

        $this->admin = app(UserFactory::class)
            ->withTokens()
            ->asAdmin()
            ->create();
    }

    public function test_count()
    {
        $response = $this->actingAs($this->admin)->getJson(self::URI);
        $response->assertOk();
        $response->assertJson(['total' => Rule::count()]);
    }

    public function test_unauthorized()
    {
        $response = $this->getJson(self::URI);
        $response->assertApiError(401);
    }

    public function test_forbidden()
    {
        $response = $this->actingAs($this->user)->getJson(self::URI);
        $response->assertApiError(403, True);
    }
}
