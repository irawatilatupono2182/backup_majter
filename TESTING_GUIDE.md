# Unit Testing Guide - Adam Jaya ERP

## 📋 Overview

Panduan lengkap untuk testing Filament Resources di aplikasi Adam Jaya ERP. Testing suite ini dirancang dengan prinsip **modular**, **scalable**, dan **maintainable**.

## 🏗️ Struktur Testing

```
tests/
├── Unit/
│   ├── ResourceTestCase.php          # Base class untuk semua resource tests
│   ├── Traits/
│   │   ├── ResourceCRUDTestTrait.php          # CRUD operations testing
│   │   ├── ResourceValidationTestTrait.php     # Validation rules testing
│   │   └── ResourceRelationshipTestTrait.php   # Relationships testing
│   ├── Resources/
│   │   ├── ProductResourceTest.php
│   │   ├── StockResourceTest.php
│   │   ├── CustomerResourceTest.php
│   │   ├── InvoiceResourceTest.php
│   │   └── ... (semua resource tests)
│   └── Helpers/
│       └── TestDataFactory.php        # Helper untuk create test data
```

## 🎯 Testing Philosophy

### 1. **Modular Design**
- Setiap trait fokus pada aspek tertentu (CRUD, Validation, Relationships)
- Mudah di-reuse dan di-extend
- Single Responsibility Principle

### 2. **Comprehensive Coverage**
- CRUD Operations (Create, Read, Update, Delete, Restore)
- Validation Rules (Required, Type, Unique, Max Length)
- Relationships (BelongsTo, HasOne, HasMany)
- Business Logic (Stock calculation, Discount, etc.)

### 3. **Easy to Understand**
- Naming convention yang jelas
- Method names yang self-documenting
- Comments untuk logic yang kompleks

## 🚀 Quick Start

### Installation

Pastikan dependencies sudah terinstall:

```bash
composer install
```

### Running Tests

**Run semua tests:**
```bash
php artisan test
```

**Run specific test class:**
```bash
php artisan test --filter=ProductResourceTest
```

**Run specific test method:**
```bash
php artisan test --filter=testCanCreateRecord
```

**Run dengan coverage:**
```bash
php artisan test --coverage
```

## 📝 Membuat Test Baru

### Step 1: Create Test Class

```php
<?php

namespace Tests\Unit\Resources;

use Tests\Unit\ResourceTestCase;
use Tests\Unit\Traits\ResourceCRUDTestTrait;
use Tests\Unit\Traits\ResourceValidationTestTrait;
use Tests\Unit\Traits\ResourceRelationshipTestTrait;
use App\Models\YourModel;

class YourResourceTest extends ResourceTestCase
{
    use ResourceCRUDTestTrait;
    use ResourceValidationTestTrait;
    use ResourceRelationshipTestTrait;

    // Implement required abstract methods dari traits
    protected function getTableName(): string
    {
        return 'your_table';
    }

    protected function getPrimaryKey(): string
    {
        return 'your_id';
    }

    protected function getModelClass(): string
    {
        return YourModel::class;
    }

    // ... implement other abstract methods
}
```

### Step 2: Implement Required Methods

Setiap trait memerlukan beberapa abstract methods:

**ResourceCRUDTestTrait:**
- `getTableName()`: Nama table database
- `getPrimaryKey()`: Nama primary key column
- `getVerifiableFields()`: Fields yang di-verify setelah create
- `getUpdateData()`: Data untuk test update

**ResourceValidationTestTrait:**
- `getRequiredFields()`: Array field yang required
- `getNumericFields()`: Array field yang harus numeric
- `getUniqueFields()`: Array field yang harus unique
- `getMaxLengthFields()`: Array field dengan max length
- `getValidFormData()`: Sample valid data
- `getModelClass()`: Model class name

**ResourceRelationshipTestTrait:**
- `getEagerLoadableRelationships()`: Array relationships yang bisa di-eager load
- `createRelatedRecords()`: Create related records untuk testing
- `verifyCascadeBehavior()`: Verify cascade delete behavior

## 📚 Testing Patterns

### Pattern 1: CRUD Testing

```php
public function testCanCreateRecord(): void
{
    $data = $this->getValidFormData();

    $record = $this->createRecordWithData($data);

    $this->assertNotNull($record);
    $this->assertDatabaseHas($this->getTableName(), [
        $this->getPrimaryKey() => $record->{$this->getPrimaryKey()},
    ]);
}
```

### Pattern 2: Validation Testing

```php
public function testRequiredFieldsValidation(): void
{
    $requiredFields = $this->getRequiredFields();

    foreach ($requiredFields as $field) {
        $data = $this->getValidFormData();
        unset($data[$field]);

        $this->assertValidationFails($data, $field);
    }
}
```

### Pattern 3: Relationship Testing

```php
public function testBelongsToCompany(): void
{
    $record = $this->createTestRecord($this->getModelClass());

    $this->assertNotNull($record->company);
    $this->assertEquals(
        $this->testCompany->company_id,
        $record->company->company_id
    );
}
```

### Pattern 4: Business Logic Testing

```php
public function testStockCalculation(): void
{
    $product = $this->createTestRecord(Product::class, [
        'base_price' => 1000,
        'default_discount_percent' => 10,
    ]);

    $expectedPrice = 1000 * (1 - 10/100);

    $this->assertEquals(900, $expectedPrice);
}
```

## 🔧 Test Helpers

### TestDataFactory

Helper class untuk create test data dengan mudah:

```php
use Tests\Unit\Helpers\TestDataFactory;

// Create test company
$company = TestDataFactory::createCompany();

// Create test user with role
$admin = TestDataFactory::createUser($company, 'super_admin');

// Create multiple products
$products = TestDataFactory::createProducts($company, 10);

// Create customer
$customer = TestDataFactory::createCustomer($company);
```

### ResourceTestCase Base Methods

```php
// Create test record dengan factory
$product = $this->createTestRecord(Product::class, [
    'name' => 'Custom Name',
]);

// Assert model exists in database
$this->assertModelExists(Product::class, [
    'product_code' => 'TEST-001',
]);

// Assert model doesn't exist
$this->assertModelNotExists(Product::class, [
    'product_code' => 'DELETED-001',
]);

// Get form data for testing
$formData = $this->getResourceFormData(ProductResource::class);
```

## 📊 Test Coverage Goals

Target coverage untuk setiap resource:

- ✅ **CRUD Operations**: 100%
- ✅ **Validation Rules**: 100%
- ✅ **Relationships**: 100%
- ✅ **Business Logic**: 100%
- ✅ **Edge Cases**: 90%+

## 🐛 Common Issues & Solutions

### Issue 1: Database Transactions

**Problem**: Tests tidak clean up data
**Solution**: ResourceTestCase sudah menggunakan `RefreshDatabase` trait

### Issue 2: Foreign Key Constraints

**Problem**: Error saat create record karena missing foreign keys
**Solution**: Gunakan `createTestRecord()` atau `TestDataFactory`

```php
// ✅ Good - auto handle company_id
$product = $this->createTestRecord(Product::class);

// ❌ Bad - missing company_id
$product = Product::create(['name' => 'Test']);
```

### Issue 3: Unique Constraints

**Problem**: Test fail karena duplicate data
**Solution**: Gunakan faker atau unique identifiers

```php
'product_code' => 'TEST-' . $this->faker->unique()->numerify('####'),
'email' => 'test' . uniqid() . '@example.com',
```

## 🔍 Best Practices

### 1. Test Naming

```php
// ✅ Good - descriptive
public function testCanCreateProductWithValidData(): void

// ❌ Bad - unclear
public function testProduct(): void
```

### 2. Assertions

```php
// ✅ Good - specific assertion
$this->assertEquals(100, $stock->quantity);

// ❌ Bad - generic assertion
$this->assertTrue($stock->quantity > 0);
```

### 3. Test Isolation

```php
// ✅ Good - each test is independent
public function testA(): void { /* creates own data */ }
public function testB(): void { /* creates own data */ }

// ❌ Bad - tests depend on each other
public function testA(): void { $this->sharedData = ... }
public function testB(): void { use $this->sharedData }
```

### 4. Data Factories

```php
// ✅ Good - use factories
$product = Product::factory()->create();

// ❌ Bad - hardcoded data
$product = Product::create([
    'field1' => 'value1',
    'field2' => 'value2',
    // ... many fields
]);
```

## 📈 Next Steps

1. **Run existing tests** untuk verify setup
2. **Add tests** untuk resources yang belum ada
3. **Improve coverage** untuk existing tests
4. **Document edge cases** yang ditemukan
5. **Refactor** tests yang repetitive

## 🤝 Contributing

Saat menambah test baru:

1. Follow existing patterns dan naming conventions
2. Implement semua required trait methods
3. Add specific business logic tests
4. Document complex test scenarios
5. Ensure tests are isolated dan repeatable

## 📞 Support

Jika ada pertanyaan atau menemukan issue:

1. Check dokumentasi ini dulu
2. Review existing tests untuk reference
3. Check trait implementations untuk understand behavior
4. Create issue dengan detail error dan steps to reproduce

---

**Happy Testing! 🎉**
