<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the Filament login page', function (): void {
    $response = $this->get('/default');

    $response->assertRedirect('/default/login');
});

it('renders the Filament dashboard for authenticated users', function (): void {
    $user = User::factory()->create();

    Filament::setCurrentPanel('default');

    $this->actingAs($user);

    $response = $this->get('/default');

    $response->assertSuccessful();
});
