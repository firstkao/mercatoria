<?php

test('respon membawa header x-protected-image dan aset shield tersedia', function () {
    $this->get('/')->assertOk()->assertHeader('X-Protected-Image', '1');

    $js = (string) file_get_contents(base_path('resources/js/image-protection.js'));

    expect($js)->toContain('contextmenu')
        ->toContain('draggable')
        ->toContain('dragstart');
});
