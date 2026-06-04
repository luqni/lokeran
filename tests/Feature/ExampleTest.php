<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test custom 404 page rendering.
     */
    public function test_non_existent_route_returns_custom_404_page(): void
    {
        $response = $this->get('/non-existent-page-url-path');

        $response->assertStatus(404);
        $response->assertSee('Waduh! Lowongan Kosong');
        $response->assertSee('images/404.png');
    }

    /**
     * Test custom 500 page rendering.
     */
    public function test_server_error_returns_custom_500_page(): void
    {
        \Illuminate\Support\Facades\Route::get('/trigger-500-error', function () {
            abort(500);
        });

        $response = $this->get('/trigger-500-error');

        $response->assertStatus(500);
        $response->assertSee('Waduh! Ada Masalah Sistem');
        $response->assertSee('images/500.png');
    }
}
