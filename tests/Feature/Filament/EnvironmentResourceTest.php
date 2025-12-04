<?php

use App\Models\Environment;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('default');
    $this->actingAs(User::factory()->create());
});

it('lists environments in the table and supports search and sort', function (): void {
    $project = Project::factory()->create();

    $envs = Environment::factory()->count(3)->for($project)->create();

    // Ensure deterministic unique names for search behavior
    $envs[0]->update(['name' => 'Alpha', 'slug' => 'alpha']);
    $envs[1]->update(['name' => 'Beta', 'slug' => 'beta']);
    $envs[2]->update(['name' => 'Gamma', 'slug' => 'gamma']);

    Livewire::test(ListEnvironments::class)
        ->assertCanSeeTableRecords($envs)
        ->sortTable('name')
        ->searchTable($envs->first()->name)
        ->assertCanSeeTableRecords($envs->take(1))
        ->assertCanNotSeeTableRecords($envs->skip(1));
});

it('can create an environment via the create page', function (): void {
    $project = Project::factory()->create();

    $form = [
        'project_id' => $project->id,
        'name' => 'Production',
        'slug' => 'prod',
        'order' => 1,
        'type' => 'production',
        'is_default' => true,
    ];

    Livewire::test(CreateEnvironment::class)
        ->fillForm($form)
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    $created = Environment::query()->where('project_id', $project->id)->where('slug', 'prod')->first();

    expect($created)->not->toBeNull();
    expect($created->is_default)->toBeTrue();
});

it('validates required fields on create', function (): void {
    Livewire::test(CreateEnvironment::class)
        ->fillForm([
            'project_id' => null,
            'name' => '',
            'slug' => '',
            'type' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'project_id' => 'required',
            'name' => 'required',
            'slug' => 'required',
            'type' => 'required',
        ]);
});

it('validates slug uniqueness per project', function (): void {
    [$p1, $p2] = Project::factory()->count(2)->create();

    Environment::factory()->for($p1)->create(['slug' => 'same']);

    // Same slug in same project should fail
    Livewire::test(CreateEnvironment::class)
        ->fillForm([
            'project_id' => $p1->id,
            'name' => 'Duplicate',
            'slug' => 'same',
            'order' => 0,
            'type' => 'custom',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'slug' => 'unique',
        ]);

    // Same slug in different project should pass
    Livewire::test(CreateEnvironment::class)
        ->fillForm([
            'project_id' => $p2->id,
            'name' => 'Duplicate OK',
            'slug' => 'same',
            'order' => 0,
            'type' => 'custom',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();
});

it('can edit an environment and toggle default, enforcing single default per project', function (): void {
    $project = Project::factory()->create();
    $envA = Environment::factory()->for($project)->create([
        'name' => 'A',
        'slug' => 'a',
        'type' => 'staging',
        'is_default' => true,
    ]);
    $envB = Environment::factory()->for($project)->create([
        'name' => 'B',
        'slug' => 'b',
        'type' => 'testing',
        'is_default' => false,
    ]);

    Livewire::test(EditEnvironment::class, [
        'record' => $envB->getKey(),
    ])
        ->fillForm([
            'name' => 'B2',
            'slug' => 'b2',
            'type' => 'production',
            'is_default' => true,
        ])
        ->call('save')
        ->assertNotified();

    $envA->refresh();
    $envB->refresh();

    expect($envB->name)->toBe('B2');
    expect($envB->slug)->toBe('b2');
    expect($envB->type)->toBe('production');
    expect($envB->is_default)->toBeTrue();
    expect($envA->is_default)->toBeFalse();
});

it('can bulk delete environments from the list table', function (): void {
    $project = Project::factory()->create();
    $envs = Environment::factory()->count(2)->for($project)->create();

    Livewire::test(ListEnvironments::class)
        ->assertCanSeeTableRecords($envs)
        ->callTableBulkAction('delete', $envs)
        ->assertNotified();

    foreach ($envs as $e) {
        expect(Environment::query()->whereKey($e->getKey())->exists())->toBeFalse();
    }
});

it('filters environments by type and default flag', function (): void {
    $project = Project::factory()->create();
    $prod = Environment::factory()->for($project)->create(['name' => 'Prod', 'slug' => 'prod', 'type' => 'production', 'is_default' => true]);
    $stag = Environment::factory()->for($project)->create(['name' => 'Stage', 'slug' => 'stage', 'type' => 'staging', 'is_default' => false]);
    $test = Environment::factory()->for($project)->create(['name' => 'Test', 'slug' => 'test', 'type' => 'testing', 'is_default' => false]);

    // Filter by type=staging
    Livewire::test(ListEnvironments::class)
        ->filterTable('type', 'staging')
        ->assertCanSeeTableRecords([$stag])
        ->assertCanNotSeeTableRecords([$prod, $test]);

    // Filter by is_default = true
    Livewire::test(ListEnvironments::class)
        ->filterTable('is_default', 'true')
        ->assertCanSeeTableRecords([$prod])
        ->assertCanNotSeeTableRecords([$stag, $test]);
});
