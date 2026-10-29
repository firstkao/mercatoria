<?php

use App\Http\Middleware\DetectBotCrawler;
use App\Models\User;
use App\Models\VisitorTracking;
use Illuminate\Http\Request;

test('bot dikenal tidak ditrack ke visitor_tracking', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])
        ->get('/')->assertOk();

    expect(VisitorTracking::count())->toBe(0);
});

test('bot whitelist mengenali daftar prd 8.8', function () {
    foreach (['GPTBot', 'facebookexternalhit', 'Twitterbot', 'ClaudeBot', 'bingbot'] as $agent) {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => $agent]);

        expect(app(DetectBotCrawler::class)->isCrawler($request))
            ->toBeTrue($agent.' seharusnya dikenali');
    }

    $human = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0)']);
    expect(app(DetectBotCrawler::class)->isCrawler($human))->toBeFalse();
});

test('request biasa tetap ditrack', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0)'])
        ->get('/')->assertOk();

    expect(VisitorTracking::count())->toBe(1);
});
