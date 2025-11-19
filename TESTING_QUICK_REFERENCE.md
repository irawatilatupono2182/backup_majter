# Unit Testing Quick Reference Card

## 🚀 Quick Commands

```bash
# Run all tests
php artisan test

# Run with output
php artisan test --testdox

# Run specific test class
php artisan test --filter=ProductResourceTest

# Run specific method
php artisan test --filter=testCanCreateRecord

# Run with coverage
php artisan test --coverage

# Run and stop on failure
php artisan test --stop-on-failure

# Run in parallel
php artisan test --parallel
```

## 📝 Creating New Test

### 1. Create Test File

Location: `tests/Unit/Resources/YourResourceTest.php`

```php
<?php

namespace Tests\Unit\Resources;

use Tests\Unit\ResourceTestCase;
use Tests\Unit\Traits\{ResourceCRUDTestTrait, ResourceValidationTestTrait, ResourceRelationshipTestTrait};
use App\Models\YourModel;

class YourResourceTest extends ResourceTestCase
{
    use ResourceCRUDTestTrait, ResourceValidationTestTrait, ResourceRelationshipTestTrait;

    // Required implementations
    protected function getTableName(): string { return 'your_table'; }
    protected function getPrimaryKey(): string { return 'id'; }
    protected function getModelClass(): string { return YourModel::class; }
    protected function getValidFormData(): array { return [...]; }
    protected function getUpdateData(): array { return [...]; }
    protected function getVerifiableFields(): array { return [...]; }
    protected function getRequiredFields(): array { return [...]; }
    protected function getNumericFields(): array { return [...]; }
    protected function getUniqueFields(): array { return [...]; }
    protected function getMaxLengthFields(): array { return [...]; }
    protected function createRecordWithData(array $data): mixed { return YourModel::create($data); }
    protected function getEagerLoadableRelationships(): array { return [...]; }
    protected function createRelatedRecords($record): void { /* ... */ }
    protected function verifyCascadeBehavior(string $primaryKey): void { /* ... */ }
}
```

### 2. Add Custom Tests

```php
public function testYourSpecificBusinessLogic(): void
{
    // Arrange
    $data = $this->getValidFormData();

    // Act
    $result = $this->createRecordWithData($data);

    // Assert
    $this->assertNotNull($result);
    $this->assertEquals($expected, $result->field);
}
```

## 🧪 Common Assertions

```php
// Database assertions
$this->assertDatabaseHas('table', ['field' => 'value']);
$this->assertDatabaseMissing('table', ['field' => 'value']);
$this->assertSoftDeleted('table', ['id' => $id]);

// Model assertions
$this->assertModelExists(Model::class, ['field' => 'value']);
$this->assertModelNotExists(Model::class, ['field' => 'value']);

// Value assertions
$this->assertEquals($expected, $actual);
$this->assertNotEquals($notExpected, $actual);
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertNull($value);
$this->assertNotNull($value);

// Collection assertions
$this->assertCount($expectedCount, $collection);
$this->assertEmpty($collection);
$this->assertNotEmpty($collection);
$this->assertContains($needle, $haystack);

// Exception assertions
$this->expectException(ExceptionClass::class);
$this->expectExceptionMessage('Expected message');
```

## 🎯 Test Data Creation

```php
// Using base class methods
$record = $this->createTestRecord(Product::class, ['name' => 'Test']);

// Using TestDataFactory
use Tests\Unit\Helpers\TestDataFactory;

$company = TestDataFactory::createCompany();
$user = TestDataFactory::createUser($company, 'admin');
$product = TestDataFactory::createProduct($company);
$products = TestDataFactory::createProducts($company, 10);
$customer = TestDataFactory::createCustomer($company);

// Using factories directly
$product = Product::factory()->create(['name' => 'Test']);
$products = Product::factory()->count(5)->create();

// Using faker
$this->faker->name();
$this->faker->email();
$this->faker->phoneNumber();
$this->faker->address();
$this->faker->company();
$this->faker->randomFloat(2, 0, 1000);
$this->faker->randomElement(['A', 'B', 'C']);
$this->faker->numberBetween(1, 100);
```

## 🔍 Debugging Tests

```php
// Dump and die
dd($variable);

// Dump
dump($variable);

// Ray debugging (if installed)
ray($variable);

// Print to console
echo "Debug: $variable\n";

// Log to Laravel log
\Log::info('Test debug', ['var' => $variable]);

// Assert with custom message
$this->assertEquals($expected, $actual, 'Custom error message');
```

## 📊 Test Organization

### Arrange-Act-Assert Pattern

```php
public function testExample(): void
{
    // Arrange - Setup test data
    $product = $this->createTestRecord(Product::class);
    $quantity = 10;

    // Act - Execute the action
    $result = $product->decreaseStock($quantity);

    // Assert - Verify the result
    $this->assertTrue($result);
    $this->assertEquals(90, $product->fresh()->stock);
}
```

### Given-When-Then Pattern

```php
public function testExample(): void
{
    // Given a product with stock
    $product = $this->createTestRecord(Product::class, [
        'stock' => 100,
    ]);

    // When we decrease the stock
    $product->decreaseStock(10);

    // Then the stock should be updated
    $this->assertEquals(90, $product->fresh()->stock);
}
```

## 🛠️ Trait Methods Available

### From ResourceCRUDTestTrait
- `testCanCreateRecord()`
- `testCanViewRecord()`
- `testCanUpdateRecord()`
- `testCanSoftDeleteRecord()`
- `testCanRestoreDeletedRecord()`
- `testCanForceDeleteRecord()`
- `testCanBulkDeleteRecords()`

### From ResourceValidationTestTrait
- `testRequiredFieldsValidation()`
- `testNumericFieldsValidation()`
- `testUniqueFieldsValidation()`
- `testMaxLengthValidation()`

### From ResourceRelationshipTestTrait
- `testBelongsToCompany()`
- `testCanEagerLoadRelationships()`
- `testDeletingParentHandlesRelationships()`

## 📚 Useful Test Helpers

```php
// From ResourceTestCase
$this->testUser;              // Authenticated user
$this->testCompany;           // Test company
$this->createTestRecord();    // Create with factory
$this->assertModelExists();   // Assert model in DB
$this->assertModelNotExists(); // Assert model not in DB

// Authentication
$this->actingAs($user);       // Login as user
$this->assertAuthenticated(); // Assert user is logged in

// Session
session(['key' => 'value']);   // Set session
session('key');                // Get session
session()->forget('key');      // Remove session

// Time manipulation
$this->travel(5)->days();      // Travel 5 days forward
$this->travelBack();           // Go back to present
```

## ⚡ Performance Tips

```php
// Use factories efficiently
Product::factory()->count(100)->create(); // Fast
// vs
for ($i = 0; $i < 100; $i++) {
    Product::factory()->create(); // Slow
}

// Use database transactions
use RefreshDatabase; // Faster than migrations

// Run in parallel
php artisan test --parallel

// Run only changed tests
php artisan test --dirty

// Skip slow tests with groups
/** @group slow */
public function testSlowOperation() { }
// Run: php artisan test --exclude-group=slow
```

## 🎨 Best Practices Checklist

- ✅ One assertion concept per test
- ✅ Descriptive test names
- ✅ Follow AAA or GWT pattern
- ✅ Test edge cases
- ✅ Keep tests independent
- ✅ Use factories for data
- ✅ Mock external services
- ✅ Test both happy and sad paths
- ✅ Clean up after tests
- ✅ Document complex scenarios

## 🚨 Common Pitfalls

```php
// ❌ Don't test framework code
public function testFactoryWorks() // Laravel already tests this

// ❌ Don't test external libraries
public function testCarbonParseDate() // Carbon is tested

// ✅ Test your business logic
public function testProductDiscountCalculation()

// ❌ Don't use sleep() in tests
sleep(2);

// ✅ Use Carbon::setTestNow()
Carbon::setTestNow('2024-01-01 12:00:00');

// ❌ Don't depend on test order
// ✅ Make each test independent
```

## 📖 Additional Resources

- [TESTING_GUIDE.md](./TESTING_GUIDE.md) - Full testing guide
- [TESTING_IMPLEMENTATION_SUMMARY.md](./TESTING_IMPLEMENTATION_SUMMARY.md) - Implementation details
- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

---

**Keep this card handy when writing tests! 📌**
