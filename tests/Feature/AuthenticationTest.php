<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /** @group auth */
    public function testGuestCanVisitLoginPage()
    {
        $this->withoutExceptionHandling();

        $response = $this->get(route('login'));

        $response->assertOk();

        $response->assertViewIs('auth.login');
    }

    /** @group auth */
    public function testAGuestWithCorrectCredentialsCanLogin()
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => 'abc@test.xyz',
            'phone' => $this->faker->randomElement(['1', '7']) . $this->faker->numberBetween(10000000, 99999999),
            'password' => Hash::make('elephant69')
        ];

        User::create($payload);

        $response = $this->post(route('login'), $this->getData());
        
        $this->assertAuthenticated();

        $this->assertAuthenticatedAs(User::first());

        $response->assertRedirect(route('dashboard'));
    }

    /** @group auth */
    public function testBothEmailAddPsswordAreRequiredForAUthentication()
    {
        $requiredFields = ['email', 'password'];

        foreach ($requiredFields as $field) {

            $response = $this->post(route('login'), array_merge(
                $this->getData(),
                [$field => null]
            ));

            $response->assertSessionHasErrors([$field]);

        }        
    }

    /** @group auth */
    public function testInValidCredentialAreBlockedByLogin()
    {
        $response = $this->post(route('login'), $this->getData());

        $response->assertSessionHasErrors(['email']);
    }

    private function getData() : array
    {
        return [
            'email' => 'abc@test.xyz',
            'password' => 'elephant69',
        ];
    }
}
