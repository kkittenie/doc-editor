<?php

test('guests are redirected to the sign in page', function () {
    $this->get('/')->assertRedirect(route('signin'));
});

test('guests cannot access protected pages', function () {
    $this->get(route('documents.create'))->assertRedirect(route('signin'));
    $this->get(route('dashboard'))->assertRedirect(route('signin'));
});

test('the sign in page returns a successful response', function () {
    $this->get(route('signin'))->assertOk();
});
