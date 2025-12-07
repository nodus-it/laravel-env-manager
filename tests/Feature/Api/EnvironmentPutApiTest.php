<?php

use App\Enums\VariableKeySource;
use App\Models\Environment;
use App\Models\Project;
use App\Models\VariableKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function actingAsEnvironment(Environment $environment, array $abilities = ['env.read', 'env.write']): void
{
    $token = $environment->createToken('test', $abilities);
    Sanctum::actingAs($environment, $abilities, 'sanctum');
}

it('can set an environment-level value', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();
    $key = VariableKey::factory()->state([
        'key' => 'APP_NAME',
        'type' => 'string',
        'is_secret' => false,
        'default_value' => 'My App',
    ])->create();

    actingAsEnvironment($environment);

    $payload = [
        'key' => 'APP_NAME',
        'value' => 'Env App',
        'source' => VariableKeySource::Environment->value,
        'show_secrets' => 1,
    ];

    $response = $this->putJson('/api/environment', $payload);

    $response->assertSuccessful();

    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('success', true)
        ->whereType('message', 'string')
        ->has('data', fn (AssertableJson $data) => $data
            ->where('slug', $environment->slug)
            ->has('keys', fn (AssertableJson $keys) => $keys
                ->where('0.key', 'APP_NAME')
                ->etc()
            )
            ->etc()
        )
    );

    $keys = collect($response->json('data.keys'))->keyBy('key');

    expect($keys['APP_NAME']['source'])->toBe('environment');
    expect($keys['APP_NAME']['value'])->toBe('Env App');
});

it('can set a project default value', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();
    VariableKey::factory()->state([
        'key' => 'TIMEZONE',
        'type' => 'string',
        'is_secret' => false,
        'default_value' => 'UTC',
    ])->create();

    actingAsEnvironment($environment);

    $response = $this->putJson('/api/environment', [
        'key' => 'TIMEZONE',
        'value' => 'Europe/Berlin',
        'source' => VariableKeySource::Project->value,
        'show_secrets' => 1,
    ]);

    $response->assertSuccessful();

    $keys = collect($response->json('data.keys'))->keyBy('key');

    expect($keys['TIMEZONE']['source'])->toBe('project');
    expect($keys['TIMEZONE']['value'])->toBe('Europe/Berlin');
});

it('can set a global default value on variable key', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();
    VariableKey::factory()->state([
        'key' => 'API_URL',
        'type' => 'string',
        'is_secret' => false,
        'default_value' => null,
    ])->create();

    actingAsEnvironment($environment);

    $response = $this->putJson('/api/environment', [
        'key' => 'API_URL',
        'value' => 'https://example.test',
        'source' => VariableKeySource::VariableKey->value,
        'show_secrets' => 1,
    ]);

    $response->assertSuccessful();

    $keys = collect($response->json('data.keys'))->keyBy('key');

    expect($keys['API_URL']['source'])->toBe('variable_key');
    expect($keys['API_URL']['value'])->toBe('https://example.test');
});

it('masks secret values by default after setting', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();
    VariableKey::factory()->state([
        'key' => 'SECRET_TOKEN',
        'type' => 'string',
        'is_secret' => true,
        'default_value' => null,
    ])->create();

    actingAsEnvironment($environment);

    $response = $this->putJson('/api/environment', [
        'key' => 'SECRET_TOKEN',
        'value' => 's3cr3t',
        'source' => VariableKeySource::Environment->value,
    ]);

    $response->assertSuccessful();

    $keys = collect($response->json('data.keys'))->keyBy('key');

    expect($keys['SECRET_TOKEN']['value'])->toBe('••••');
});

it('can set multiple keys at once using the keys array', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();

    VariableKey::factory()->state([
        'key' => 'NAME',
        'type' => 'string',
        'is_secret' => false,
        'default_value' => 'DefaultName',
    ])->create();

    VariableKey::factory()->state([
        'key' => 'TZ',
        'type' => 'string',
        'is_secret' => false,
        'default_value' => 'UTC',
    ])->create();

    VariableKey::factory()->state([
        'key' => 'API_TOKEN',
        'type' => 'string',
        'is_secret' => true,
        'default_value' => null,
    ])->create();

    actingAsEnvironment($environment);

    $payload = [
        'keys' => [
            ['key' => 'NAME', 'value' => 'EnvName', 'source' => VariableKeySource::Environment->value],
            ['key' => 'TZ', 'value' => 'Europe/Berlin', 'source' => VariableKeySource::Project->value],
            ['key' => 'API_TOKEN', 'value' => 'secret-123', 'source' => VariableKeySource::Environment->value],
        ],
        // Reveal to assert actual values
        'show_secrets' => 1,
    ];

    $response = $this->putJson('/api/environment', $payload);

    $response->assertSuccessful();

    $keys = collect($response->json('data.keys'))->keyBy('key');

    expect($keys['NAME']['source'])->toBe('environment');
    expect($keys['NAME']['value'])->toBe('EnvName');

    expect($keys['TZ']['source'])->toBe('project');
    expect($keys['TZ']['value'])->toBe('Europe/Berlin');

    expect($keys['API_TOKEN']['source'])->toBe('environment');
    expect($keys['API_TOKEN']['value'])->toBe('secret-123');
});

it('masks secrets in bulk by default', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();

    VariableKey::factory()->state([
        'key' => 'API_TOKEN',
        'type' => 'string',
        'is_secret' => true,
        'default_value' => null,
    ])->create();

    actingAsEnvironment($environment);

    $payload = [
        'keys' => [
            ['key' => 'API_TOKEN', 'value' => 'masked-please', 'source' => VariableKeySource::Environment->value],
        ],
    ];

    $response = $this->putJson('/api/environment', $payload);

    $response->assertSuccessful();

    $keys = collect($response->json('data.keys'))->keyBy('key');

    expect($keys['API_TOKEN']['value'])->toBe('••••');
});
