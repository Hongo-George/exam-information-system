<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAnyoneCanVisitWelcomePage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
