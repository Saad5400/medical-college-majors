<?php

use App\Filament\Exports\UserExporter;
use App\Models\Major;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;

beforeEach(function () {
    $this->export = Export::create([
        'user_id' => User::factory()->create()->id,
        'file_name' => 'test.xlsx',
        'file_path' => 'exports/test.xlsx',
        'exporter' => UserExporter::class,
        'total_rows' => 0,
    ]);
});

it('sorts users by major_id (track_id)', function () {
    // Create majors with specific IDs
    $major1 = Major::factory()->create(['name' => 'Computer Science']);
    $major2 = Major::factory()->create(['name' => 'Mathematics']);
    $major3 = Major::factory()->create(['name' => 'Physics']);

    // Create users assigned to majors in random order
    $userInMajor3 = User::factory()->create([
        'name' => 'User in Major 3',
        'major_id' => $major3->id,
    ]);
    $userInMajor1 = User::factory()->create([
        'name' => 'User in Major 1',
        'major_id' => $major1->id,
    ]);
    $userInMajor2 = User::factory()->create([
        'name' => 'User in Major 2',
        'major_id' => $major2->id,
    ]);

    // Get the query from the exporter
    $query = User::query();
    $modifiedQuery = UserExporter::modifyQuery($query);

    // Execute the query and get the results
    $users = $modifiedQuery->get();

    // Verify users are sorted by major_id
    expect($users)->toHaveCount(4) // 3 created users + 1 from beforeEach
        ->and($users->first()->major_id)->toBeLessThanOrEqual($users->get(1)->major_id)
        ->and($users->get(1)->major_id)->toBeLessThanOrEqual($users->get(2)->major_id)
        ->and($users->get(2)->major_id)->toBeLessThanOrEqual($users->last()->major_id);

    // Verify the major relationship is eager loaded
    expect($users->first()->relationLoaded('major'))->toBeTrue();
});

it('eager loads major relationship to avoid N+1 queries', function () {
    $major = Major::factory()->create(['name' => 'Engineering']);
    User::factory()->count(3)->create(['major_id' => $major->id]);

    $query = User::query();
    $modifiedQuery = UserExporter::modifyQuery($query);
    $users = $modifiedQuery->get();

    // Check that the major relationship is eager loaded
    foreach ($users as $user) {
        expect($user->relationLoaded('major'))->toBeTrue();
    }
});

it('includes major name in export columns', function () {
    $columns = UserExporter::getColumns();

    $majorColumn = collect($columns)->first(fn ($column) => $column->getName() === 'major.name');

    expect($majorColumn)->not->toBeNull()
        ->and($majorColumn->getLabel())->toBe('المسار');
});
