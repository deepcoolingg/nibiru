<?php

test('the application redirects from home route', function () {
    $response = $this->get('/');

    $response->assertRedirect();
});
