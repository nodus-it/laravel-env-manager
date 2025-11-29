<?php

use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
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

it('lists projects in the table and supports search and sort', function (): void {
    $projects = Project::factory()->count(3)->create();

    Livewire::test(ListProjects::class)
        ->assertCanSeeTableRecords($projects)
        ->sortTable('name')
        ->searchTable($projects->first()->name)
        ->assertCanSeeTableRecords($projects->take(1))
        ->assertCanNotSeeTableRecords($projects->skip(1));
});

it('can create a project via the create page', function (): void {
    $form = [
        'name' => 'Env Manager',
        'slug' => 'env-manager',
        'repo_url' => 'https://github.com/nodus-it/env-manager',
        'description' => 'Test description',
    ];

    Livewire::test(CreateProject::class)
        ->fillForm($form)
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    expect(Project::query()->where('slug', 'env-manager')->exists())->toBeTrue();
});

it('validates required fields on create', function (): void {
    Livewire::test(CreateProject::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'slug' => 'required',
        ]);
});

it('can edit a project', function (): void {
    $project = Project::factory()->create([
        'name' => 'Old',
        'slug' => 'old',
    ]);

    Livewire::test(EditProject::class, [
        'record' => $project->getKey(),
    ])
        ->fillForm([
            'name' => 'New Name',
            'slug' => 'new-slug',
        ])
        ->call('save')
        ->assertNotified();

    $project->refresh();

    expect($project->name)->toBe('New Name');
    expect($project->slug)->toBe('new-slug');
});

it('can bulk delete projects from the list table', function (): void {
    $projects = Project::factory()->count(2)->create();

    Livewire::test(ListProjects::class)
        ->assertCanSeeTableRecords($projects)
        ->callTableBulkAction('delete', $projects)
        ->assertNotified();

    foreach ($projects as $p) {
        expect(Project::query()->whereKey($p->getKey())->exists())->toBeFalse();
    }
});
