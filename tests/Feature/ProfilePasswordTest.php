<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

test('authenticated user can open the change password page', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('profile.password'))
        ->assertOk()
        ->assertSee('Ubah Kata Sandi');
});

test('user can change their password', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create(['password' => 'old-password-123']);
    $user->assignRole('admin');

    $response = $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password'     => 'old-password-123',
            'password'             => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

    $response->assertRedirect(route('profile.password'));
    $response->assertSessionHas('success');

    expect(Hash::check('new-password-456', $user->fresh()->password))->toBeTrue();
});

test('wrong current password is rejected', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create(['password' => 'old-password-123']);
    $user->assignRole('admin');

    $response = $this->actingAs($user)
        ->from(route('profile.password'))
        ->put(route('profile.password.update'), [
            'current_password'     => 'wrong-password-xyz',
            'password'             => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

    $response->assertRedirect(route('profile.password'));
    $response->assertSessionHasErrors('current_password');

    expect(Hash::check('new-password-456', $user->fresh()->password))->toBeFalse();
});