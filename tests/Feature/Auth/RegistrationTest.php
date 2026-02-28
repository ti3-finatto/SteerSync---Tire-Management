<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'cpf' => '123.456.789-01',
        'phone' => '(11) 99999-0000',
        'username' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('t_usuario', [
        'email' => 'test@example.com',
        'cpf' => '12345678901',
        'status' => 'ATIVO',
        'phone' => '(11) 99999-0000',
        'username' => 'testuser',
    ]);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration requires cpf with 11 digits after normalization', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'cpf-invalido@example.com',
        'cpf' => '123.456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['cpf']);
});
