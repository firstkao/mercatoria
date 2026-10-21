<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_homepage_shows_the_mercatoria_introduction(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Jelajahi Game Favoritmu');
        $response->assertSee('Merch Baru');
        $response->assertDontSee('Setup berhasil!');
    }
}
