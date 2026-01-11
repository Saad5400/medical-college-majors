<?php

use App\Filament\Imports\UserImporter;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

beforeEach(function () {
    $import = Import::create([
        'user_id' => User::factory()->create()->id,
        'file_name' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'importer' => UserImporter::class,
        'total_rows' => 0,
    ]);
    $this->importer = new UserImporter($import, [], []);
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
    $import = Import::create([
        'user_id' => User::factory()->create()->id,
        'file_name' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'importer' => UserImporter::class,
        'total_rows' => 0,
    ]);

    // Create a UserImporter instance with mocked data
    $importer = new class($import, [], []) extends UserImporter
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

it('normalizes student ID with Arabic-Indic numerals', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // Arabic-Indic numerals: ٤٣٠٧٤٨٥٧٤ should become 430748574
    $result = $method->invoke($this->importer, '٤٣٠٧٤٨٥٧٤');
    expect($result)->toBe('430748574');
});

it('normalizes student ID with Eastern Arabic-Indic numerals', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // Eastern Arabic-Indic numerals: ۴۳۰۷۴۸۵۷۴ should become 430748574
    $result = $method->invoke($this->importer, '۴۳۰۷۴۸۵۷۴');
    expect($result)->toBe('430748574');
});

it('normalizes student ID with whitespace', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // With whitespace: " 430748574 " should become "430748574"
    $result = $method->invoke($this->importer, ' 430748574 ');
    expect($result)->toBe('430748574');
});

it('removes non-numeric characters from student ID', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // With extra characters: "430-748-574" should become "430748574"
    $result = $method->invoke($this->importer, '430-748-574');
    expect($result)->toBe('430748574');
});

it('generates correct email from student ID', function () {
    $import = Import::create([
        'user_id' => User::factory()->create()->id,
        'file_name' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'importer' => UserImporter::class,
        'total_rows' => 0,
    ]);

    // Create a UserImporter instance with mocked data
    $importer = new class($import, [], []) extends UserImporter
    {
        public array $data = [
            'name' => 'أحمد محمد',
            'student_id' => '٤٣٠٧٤٨٥٧٤', // Arabic numerals
            'gpa' => '3.75',
        ];
    };

    $user = $importer->resolveRecord();

    expect($user->student_id)->toBe('430748574')
        ->and($user->email)->toBe('s430748574@uqu.edu.sa');
});

it('normalizes student ID in resolveRecord', function () {
    $import = Import::create([
        'user_id' => User::factory()->create()->id,
        'file_name' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'importer' => UserImporter::class,
        'total_rows' => 0,
    ]);

    // Create a UserImporter instance with mocked data containing Arabic numerals
    $importer = new class($import, [], []) extends UserImporter
    {
        public array $data = [
            'name' => 'محمد علي',
            'student_id' => '٤٤٤٤٤٤٤', // Arabic numerals: ٤٤٤٤٤٤٤
            'gpa' => '3.50',
        ];
    };

    $user = $importer->resolveRecord();

    expect($user->student_id)->toBe('4444444')
        ->and($user->email)->toBe('s4444444@uqu.edu.sa')
        ->and($user->name)->toBe('محمد علي')
        ->and($user->gpa)->toBe('3.50');
});

it('extracts student ID from full email format', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // Full email: s430748574@uqu.edu.sa should become 430748574
    $result = $method->invoke($this->importer, 's430748574@uqu.edu.sa');
    expect($result)->toBe('430748574');
});

it('extracts student ID from email with uppercase S prefix', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // Full email with uppercase: S430748574@uqu.edu.sa should become 430748574
    $result = $method->invoke($this->importer, 'S430748574@uqu.edu.sa');
    expect($result)->toBe('430748574');
});

it('removes lowercase s prefix from student ID', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // With s prefix: s430748574 should become 430748574
    $result = $method->invoke($this->importer, 's430748574');
    expect($result)->toBe('430748574');
});

it('removes uppercase S prefix from student ID', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // With S prefix: S430748574 should become 430748574
    $result = $method->invoke($this->importer, 'S430748574');
    expect($result)->toBe('430748574');
});

it('extracts student ID from email with Arabic numerals', function () {
    $reflection = new ReflectionClass($this->importer);
    $method = $reflection->getMethod('normalizeStudentId');
    $method->setAccessible(true);

    // Email with Arabic numerals: s٤٣٠٧٤٨٥٧٤@uqu.edu.sa should become 430748574
    $result = $method->invoke($this->importer, 's٤٣٠٧٤٨٥٧٤@uqu.edu.sa');
    expect($result)->toBe('430748574');
});

it('handles email format in resolveRecord', function () {
    $import = Import::create([
        'user_id' => User::factory()->create()->id,
        'file_name' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'importer' => UserImporter::class,
        'total_rows' => 0,
    ]);

    // Create a UserImporter instance with email format in student_id
    $importer = new class($import, [], []) extends UserImporter
    {
        public array $data = [
            'name' => 'سارة أحمد',
            'student_id' => 's5555555@uqu.edu.sa', // Full email format
            'gpa' => '3.80',
        ];
    };

    $user = $importer->resolveRecord();

    expect($user->student_id)->toBe('5555555')
        ->and($user->email)->toBe('s5555555@uqu.edu.sa')
        ->and($user->name)->toBe('سارة أحمد');
});
