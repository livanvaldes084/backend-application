<?php

namespace Tests\Feature\Rules;

use Tests\Facades\UserFactory;
use App\Models\Rule;
use App\User;
use Tests\TestCase;

/**
 * Class ListTest
 * @package Tests\Feature\Rules
 */
class ListTest extends TestCase
{
    private const URI = 'v1/rules/list';

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

        $this->user = UserFactory::asUser()->withTokens()->create();
        $this->admin = UserFactory::asAdmin()->withTokens()->create();
    }

    public function test_list()
    {
        $response = $this->actingAs($this->admin)->getJson(self::URI);
        $response->assertOk();
        $response->assertJson(Rule::all()->toArray());
    }

    public function test_unauthorized()
    {
        $response = $this->getJson(self::URI);
        $response->assertUnauthorized();
    }

    public function test_forbidden()
    {
        $response = $this->actingAs($this->user)->getJson(self::URI);
        $response->assertForbidden();
    }
}
