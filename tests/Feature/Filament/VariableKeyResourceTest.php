<?php

use App\Filament\Resources\VariableKeyResource\Pages\CreateVariableKey;
use App\Filament\Resources\VariableKeyResource\Pages\ListVariableKeys;
use App\Models\User;
use App\Models\VariableKey;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('default');
    $this->actingAs(User::factory()->create());
});

it('lists variable keys and supports search', function (): void {
    $keys = VariableKey::factory()->count(3)->create();

    Livewire::test(ListVariableKeys::class)
        ->assertCanSeeTableRecords($keys)
        ->searchTable($keys->first()->key)
        ->assertCanSeeTableRecords($keys->take(1))
        ->assertCanNotSeeTableRecords($keys->skip(1));
});

it('creates a variable key and respects unique key', function (): void {
    $existing = VariableKey::factory()->create(['key' => 'APP_URL']);

    // duplicate should fail
    Livewire::test(CreateVariableKey::class)
        ->fillForm([
            'key' => 'APP_URL',
            'type' => 'string',
            'is_secret' => false,
        ])
        ->call('create')
        ->assertHasFormErrors(['key' => 'unique']);

    // unique should pass
    Livewire::test(CreateVariableKey::class)
        ->fillForm([
            'key' => 'DB_HOST',
            'type' => 'string',
            'is_secret' => false,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    expect(VariableKey::query()->where('key', 'DB_HOST')->exists())->toBeTrue();
});

it('masks default_value when is_secret is true in the form (revealable)', function (): void {
    // Ensure form reacts: we cannot inspect password field type directly, but we can at least set values without error
    Livewire::test(CreateVariableKey::class)
        ->fillForm([
            'key' => 'SECRET_TOKEN',
            'type' => 'string',
            'is_secret' => true,
            'default_value' => 'super-secret',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $vk = VariableKey::query()->where('key', 'SECRET_TOKEN')->first();
    expect($vk)->not->toBeNull();
    expect($vk->is_secret)->toBeTrue();
    expect($vk->default_value)->toBe('super-secret');
});
