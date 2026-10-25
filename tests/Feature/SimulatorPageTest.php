<?php

it('renders the simulator with its assets and the CSRF token', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('<meta name="csrf-token" content="', false)
        ->assertSee(asset('css/simulator.css'), false)
        ->assertSee(asset('js/simulator.js'), false)
        ->assertSee('Oʻzbekiston huquqiy maʼlumotlari boʻyicha AI-yordamchi');
});

it('gives the page script the URL of every AI endpoint', function () {
    $response = $this->get(route('home'));

    $response->assertOk()->assertSee(json_encode([
        'assistantChat' => route('api.assistant.chat'),
        'courtTurn' => route('api.court.turn'),
        'courtCase' => route('api.court.cases.store'),
        'courtHint' => route('api.court.hint'),
        'courtVerdict' => route('api.court.verdict'),
        'newsRefresh' => route('api.news.refresh'),
    ]), false);
});
