<?php

use App\Filament\Imports\UserImporter;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->importer = new UserImporter();
});

it('normalizes GPA with Arabic-Indic numerals', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // Arabic-Indic numerals: ٣.٤٥ should become 3.45
    $result = $method->invoke($this->importer, '٣.٤٥');
    expect($result)->toBe('3.45');
});

it('normalizes GPA with Eastern Arabic-Indic numerals', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // Eastern Arabic-Indic numerals: ۳.۴۵ should become 3.45
    $result = $method->invoke($this->importer, '۳.۴۵');
    expect($result)->toBe('3.45');
});

it('normalizes GPA with comma decimal separator', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // Comma as decimal separator: 3,45 should become 3.45
    $result = $method->invoke($this->importer, '3,45');
    expect($result)->toBe('3.45');
});

it('normalizes GPA with mixed Arabic numerals and comma', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // Mixed: ٣,٤٥ should become 3.45
    $result = $method->invoke($this->importer, '٣,٤٥');
    expect($result)->toBe('3.45');
});

it('normalizes GPA with whitespace', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // With whitespace: " 3.45 " should become "3.45"
    $result = $method->invoke($this->importer, ' 3.45 ');
    expect($result)->toBe('3.45');
});

it('handles null GPA values', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    expect($method->invoke($this->importer, null))->toBeNull();
    expect($method->invoke($this->importer, ''))->toBeNull();
});

it('normalizes standard Western numerals', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // Standard format should remain unchanged
    $result = $method->invoke($this->importer, '3.45');
    expect($result)->toBe('3.45');
});

it('removes non-numeric characters except decimal point', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    // With extra characters: "3.45abc" should become "3.45"
    $result = $method->invoke($this->importer, '3.45abc');
    expect($result)->toBe('3.45');
});

it('creates user with normalized GPA during import', function () {
    // Create a UserImporter instance with mocked data
    $importer = new class extends UserImporter
    {
        public array $data = [
            'name' => 'أحمد محمد',
            'student_id' => '430748574',
            'gpa' => '٣,٧٥', // Arabic numerals with comma
        ];
    };

    $user = $importer->resolveRecord();

    expect($user->name)->toBe('أحمد محمد')
        ->and($user->student_id)->toBe('430748574')
        ->and($user->email)->toBe('s430748574@uqu.edu.sa')
        ->and($user->gpa)->toBe('3.75');
});

it('handles all Arabic numeral combinations', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeGpa');
    $method->setAccessible(true);

    $testCases = [
        '٠.٠٠' => '0.00',
        '١.٢٣' => '1.23',
        '٢.٥٦' => '2.56',
        '٣.٧٨' => '3.78',
        '٤.٠٠' => '4.00',
    ];

    foreach ($testCases as $input => $expected) {
        expect($method->invoke($this->importer, $input))->toBe($expected);
    }
});
