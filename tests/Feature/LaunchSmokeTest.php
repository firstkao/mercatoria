<?php

namespace Tests\Feature;

use Tests\TestCase;

class LaunchSmokeTest extends TestCase
{
    public function test_public_release_routes_and_crawler_files_are_available(): void
    {
        $this->get('/up')->assertOk();
        $this->get(route('home'))->assertOk();
        $this->get(route('catalog.index'))->assertRedirect(route('login'));
        $this->get(route('register'))->assertOk();
        $this->get(route('login'))->assertOk();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);

        $this->assertFileExists(public_path('robots.txt'));
        $this->assertStringContainsString(
            'Sitemap: https://mercatoria.id/sitemap.xml',
            file_get_contents(public_path('robots.txt')),
        );
    }
}
