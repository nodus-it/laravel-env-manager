<?php

use App\Filament\Resources\TeamResource\Pages\CreateTeam;
use App\Filament\Resources\TeamResource\Pages\EditTeam;
use App\Filament\Resources\TeamResource\Pages\ListTeams;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('default');
    $this->actingAs(User::factory()->create());
});

it('lists teams in the table and supports search and sort', function (): void {
    $owner = User::factory()->create();

    $teams = Team::factory()->count(3)->create([
        'owner_id' => $owner->id,
    ]);

    Livewire::test(ListTeams::class)
        ->assertCanSeeTableRecords($teams)
        ->sortTable('name')
        ->searchTable($teams->first()->name)
        ->assertCanSeeTableRecords($teams->take(1))
        ->assertCanNotSeeTableRecords($teams->skip(1));
});

it('can create a team via the create page', function (): void {
    $owner = User::factory()->create();

    Livewire::test(CreateTeam::class)
        ->fillForm([
            'name' => 'Core Team',
            'slug' => 'core-team',
            'owner_id' => $owner->id,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    expect(Team::query()->where('slug', 'core-team')->where('owner_id', $owner->id)->exists())->toBeTrue();
});

it('validates required fields on create', function (): void {
    Livewire::test(CreateTeam::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
            'owner_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'slug' => 'required',
            'owner_id' => 'required',
        ]);
});

it('can edit a team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create([
        'name' => 'Alpha',
        'slug' => 'alpha',
        'owner_id' => $owner->id,
    ]);

    $newOwner = User::factory()->create();

    Livewire::test(EditTeam::class, [
        'record' => $team->getKey(),
    ])
        ->fillForm([
            'name' => 'Beta',
            'slug' => 'beta',
            'owner_id' => $newOwner->id,
        ])
        ->call('save')
        ->assertNotified();

    $team->refresh();

    expect($team->name)->toBe('Beta');
    expect($team->slug)->toBe('beta');
    expect($team->owner_id)->toBe($newOwner->id);
});

it('can bulk delete teams from the list table', function (): void {
    $owner = User::factory()->create();
    $teams = Team::factory()->count(2)->create([
        'owner_id' => $owner->id,
    ]);

    Livewire::test(ListTeams::class)
        ->assertCanSeeTableRecords($teams)
        ->callTableBulkAction('delete', $teams)
        ->assertNotified();

    foreach ($teams as $t) {
        expect(Team::query()->whereKey($t->getKey())->exists())->toBeFalse();
    }
});
