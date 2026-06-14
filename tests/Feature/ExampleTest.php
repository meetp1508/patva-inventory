<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Root redirects to the dashboard for authenticated users; unauthenticated
        // users are bounced to login by the dashboard route's auth middleware.
        $response->assertRedirect(route('dashboard'));
    }
}
