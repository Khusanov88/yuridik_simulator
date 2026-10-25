<?php

it('renders the court hall with its assets and the CSRF token', function () {
    $response = $this->get(route('court-hall'));

    $response->assertOk()
        ->assertSee('<meta name="csrf-token" content="', false)
        ->assertSee(asset('css/court-hall.css'), false)
        ->assertSee(asset('js/api.js'), false)
        ->assertSee(asset('js/court-hall.js'), false)
        ->assertSee('YURIDIK SIMULYATOR');
});

it('gives the page script the URL of every court hall endpoint', function () {
    $response = $this->get(route('court-hall'));

    $response->assertOk()->assertSee(json_encode([
        'courtTurn' => route('api.court-hall.turn'),
        'courtCase' => route('api.court-hall.cases.store'),
        'courtHint' => route('api.court-hall.hint'),
        'courtVerdict' => route('api.court-hall.verdict'),
        'advisor' => route('api.advisor'),
    ]), false);
});

it('permanently redirects the old bot.php address to the court hall', function () {
    $response = $this->get('/bot.php');

    $response->assertMovedPermanently()->assertRedirect(route('court-hall'));
});
