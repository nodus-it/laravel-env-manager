<?php

use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Filament\Resources\ProjectResource\Pages\ViewProject;
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

it('can create a project via the create modal on the list page', function (): void {
    $form = [
        'name' => 'Env Manager',
        'slug' => 'env-manager',
        'repo_url' => 'https://github.com/nodus-it/env-manager',
        'description' => 'Test description',
    ];

    Livewire::test(ListProjects::class)
        ->callAction('create', $form)
        ->assertNotified();

    expect(Project::query()->where('slug', 'env-manager')->exists())->toBeTrue();
});

it('validates required fields on create modal', function (): void {
    Livewire::test(ListProjects::class)
        ->callAction('create', [
            'name' => '',
            'slug' => '',
        ])
        ->assertHasActionErrors([
            'name' => 'required',
            'slug' => 'required',
        ]);
});

it('can edit a project via the edit modal on the view page', function (): void {
    $project = Project::factory()->create([
        'name' => 'Old',
        'slug' => 'old',
    ]);

    Livewire::test(ViewProject::class, [
        'record' => $project->getKey(),
    ])
        ->callAction('edit', [
            'name' => 'New Name',
            'slug' => 'new-slug',
        ])
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
