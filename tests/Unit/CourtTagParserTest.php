<?php

use App\Services\Court\CourtTagParser;

it('splits a reply into its tagged sections', function () {
    $reply = "[STENOGRAMMA]\nAdvokat nutqi.\n\n[QARSHI_TOMON]\nE'tiroz.\n\n[SUDYA]\nSavol?";

    $sections = (new CourtTagParser)->parse($reply, ['STENOGRAMMA', 'QARSHI_TOMON', 'SUDYA']);

    expect($sections)->toBe([
        'stenogramma' => 'Advokat nutqi.',
        'qarshi_tomon' => "E'tiroz.",
        'sudya' => 'Savol?',
    ]);
});

it('removes role names and quotes the model repeats inside a section', function () {
    $reply = "[QARSHI_TOMON]\n«Qarshi tomon (Prokuror): Ayblov asosli.»\n[SUDYA]\n👩‍⚖️ Raislik qiluvchi: Davom eting.";

    $sections = (new CourtTagParser)->parse($reply, ['QARSHI_TOMON', 'SUDYA']);

    expect($sections)->toBe(['qarshi_tomon' => 'Ayblov asosli.', 'sudya' => 'Davom eting.']);
});

it('returns empty sections for tags missing from the reply', function () {
    $sections = (new CourtTagParser)->parse('Teglarsiz javob.', ['QARSHI_TOMON', 'SUDYA']);

    expect($sections)->toBe(['qarshi_tomon' => '', 'sudya' => '']);
});

it('matches tags regardless of their letter case', function () {
    $sections = (new CourtTagParser)->parse("[sudya]\nSavol?", ['SUDYA']);

    expect($sections)->toBe(['sudya' => 'Savol?']);
});
