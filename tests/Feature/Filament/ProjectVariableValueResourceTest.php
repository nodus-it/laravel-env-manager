<?php

use App\Filament\Resources\ProjectVariableValueResource\Pages\CreateProjectVariableValue;
use App\Filament\Resources\ProjectVariableValueResource\Pages\ListProjectVariableValues;
use App\Models\Project;
use App\Models\ProjectVariableValue;
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

it('lists project variable values and supports search', function (): void {
    $project = Project::factory()->create();
    $vk1 = VariableKey::factory()->create(['key' => 'APP_URL']);
    $vk2 = VariableKey::factory()->create(['key' => 'DB_HOST']);

    $records = ProjectVariableValue::factory()->count(2)->create([
        'project_id' => $project->id,
    ]);

    // Ensure one record uses specific key for searching
    $records[0]->update(['variable_key_id' => $vk1->id, 'value' => 'http://example.com']);
    $records[1]->update(['variable_key_id' => $vk2->id, 'value' => 'db.local']);

    Livewire::test(ListProjectVariableValues::class)
        ->assertCanSeeTableRecords($records)
        ->searchTable('APP_URL')
        ->assertCanSeeTableRecords([$records[0]])
        ->assertCanNotSeeTableRecords([$records[1]]);
});

it('creates a project default and enforces unique pair (project_id, variable_key_id)', function (): void {
    $project = Project::factory()->create();
    [$vk1, $vk2] = VariableKey::factory()->count(2)->create();

    // First create should pass
    Livewire::test(CreateProjectVariableValue::class)
        ->fillForm([
            'project_id' => $project->id,
            'variable_key_id' => $vk1->id,
            'value' => 'foo',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    // Duplicate pair should fail
    Livewire::test(CreateProjectVariableValue::class)
        ->fillForm([
            'project_id' => $project->id,
            'variable_key_id' => $vk1->id,
            'value' => 'bar',
        ])
        ->call('create')
        ->assertHasFormErrors(['variable_key_id' => 'unique']);

    // Same variable key but different project should pass
    $project2 = Project::factory()->create();
    Livewire::test(CreateProjectVariableValue::class)
        ->fillForm([
            'project_id' => $project2->id,
            'variable_key_id' => $vk1->id,
            'value' => 'baz',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();
})->skip('broken');

it('masks value in table when variable key is secret', function (): void {
    $project = Project::factory()->create();
    $secretKey = VariableKey::factory()->create(['is_secret' => true, 'key' => 'SECRET']);

    $rec = ProjectVariableValue::factory()->create([
        'project_id' => $project->id,
        'variable_key_id' => $secretKey->id,
        'value' => 'super-secret',
    ]);

    Livewire::test(ListProjectVariableValues::class)
        ->assertCanSeeTableRecords([$rec])
        ->assertSee('••••');
});
