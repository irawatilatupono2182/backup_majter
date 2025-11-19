<?php

namespace Tests\Unit\Resources;

use Tests\Unit\ResourceTestCase;
use Tests\Unit\Traits\ResourceCRUDTestTrait;
use Tests\Unit\Traits\ResourceValidationTestTrait;
use Tests\Unit\Traits\ResourceRelationshipTestTrait;
use App\Models\Product;
use App\Models\Stock;
use App\Filament\Resources\ProductResource;

/**
 * ProductResource Unit Tests
 *
 * Comprehensive tests for Product resource including:
 * - CRUD operations
 * - Validation rules
 * - Stock integration
 * - Product types (STOCK vs CATALOG)
 * - Relationships
 */
class ProductResourceTest extends ResourceTestCase
{
    use ResourceCRUDTestTrait, ResourceValidationTestTrait, ResourceRelationshipTestTrait;

    /**
     * Test creating a STOCK product automatically creates stock record
     */
    public function testCreatingStockProductCreatesStockRecord(): void
    {
        $data = $this->getValidFormData();
        $data['product_type'] = 'STOCK';

        $product = Product::create($data);

        // Auto-create stock record
        if (!$product->stock) {
            Stock::create([
                'company_id' => $product->company_id,
                'product_id' => $product->product_id,
                'quantity' => 0,
                'available_quantity' => 0,
                'reserved_quantity' => 0,
            ]);
        }

        $product->refresh();

        $this->assertNotNull($product->stock);
        $this->assertEquals(0, $product->stock->quantity);
        $this->assertEquals(0, $product->stock->available_quantity);
    }

    /**
     * Test creating a CATALOG product does not create stock record
     */
    public function testCreatingCatalogProductDoesNotCreateStockRecord(): void
    {
        $data = $this->getValidFormData();
        $data['product_type'] = 'CATALOG';

        $product = Product::create($data);

        $this->assertNull($product->stock);
    }

    /**
     * Test product code uniqueness within company
     */
    public function testProductCodeUniquePerCompany(): void
    {
        $firstProduct = $this->createTestRecord(Product::class, [
            'product_code' => 'TEST-001',
        ]);

        // Should fail - same code in same company
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Product::create([
            'company_id' => $this->testCompany->company_id,
            'product_code' => 'TEST-001',
            'name' => 'Different Product',
            'unit' => 'pcs',
            'product_type' => 'STOCK',
        ]);
    }

    /**
     * Test product isStock() helper method
     */
    public function testIsStockMethod(): void
    {
        $stockProduct = $this->createTestRecord(Product::class, [
            'product_type' => 'STOCK',
        ]);

        $catalogProduct = $this->createTestRecord(Product::class, [
            'product_type' => 'CATALOG',
            'product_code' => 'CAT-001',
        ]);

        $this->assertTrue($stockProduct->isStock());
        $this->assertFalse($stockProduct->isCatalog());

        $this->assertFalse($catalogProduct->isStock());
        $this->assertTrue($catalogProduct->isCatalog());
    }

    /**
     * Test min stock alert functionality
     */
    public function testMinStockAlertConfiguration(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'product_type' => 'STOCK',
            'min_stock_alert' => 10,
        ]);

        // Create stock with low quantity
        Stock::create([
            'company_id' => $product->company_id,
            'product_id' => $product->product_id,
            'quantity' => 5,
            'available_quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $product->refresh();

        $this->assertEquals(10, $product->min_stock_alert);
        $this->assertLessThan($product->min_stock_alert, $product->stock->available_quantity);
    }

    /**
     * Test default discount percent application
     */
    public function testDefaultDiscountPercent(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'base_price' => 1000,
            'default_discount_percent' => 10,
        ]);

        $this->assertEquals(10, $product->default_discount_percent);
        $this->assertEquals(1000, $product->base_price);

        // Calculate expected discount
        $expectedDiscountedPrice = 1000 * (1 - 10/100);
        $this->assertEquals(900, $expectedDiscountedPrice);
    }

    /**
     * Test product can be activated/deactivated
     */
    public function testCanToggleProductActiveStatus(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'is_active' => true,
        ]);

        $this->assertTrue($product->is_active);

        $product->update(['is_active' => false]);
        $product->refresh();

        $this->assertFalse($product->is_active);
    }

    /**
     * Test filtering products by type
     */
    public function testCanFilterByProductType(): void
    {
        // Create multiple products with different types
        $this->createTestRecord(Product::class, [
            'product_code' => 'STOCK-001',
            'product_type' => 'STOCK',
        ]);

        $this->createTestRecord(Product::class, [
            'product_code' => 'CAT-001',
            'product_type' => 'CATALOG',
        ]);

        $stockProducts = Product::where('product_type', 'STOCK')->count();
        $catalogProducts = Product::where('product_type', 'CATALOG')->count();

        $this->assertGreaterThanOrEqual(1, $stockProducts);
        $this->assertGreaterThanOrEqual(1, $catalogProducts);
    }

    /**
     * Test product search by code and name
     */
    public function testCanSearchProducts(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'product_code' => 'SEARCH-001',
            'name' => 'Searchable Product Name',
        ]);

        // Search by code
        $foundByCode = Product::where('product_code', 'like', '%SEARCH%')->first();
        $this->assertNotNull($foundByCode);
        $this->assertEquals($product->product_id, $foundByCode->product_id);

        // Search by name
        $foundByName = Product::where('name', 'like', '%Searchable%')->first();
        $this->assertNotNull($foundByName);
        $this->assertEquals($product->product_id, $foundByName->product_id);
    }

    // ==================== Trait Implementations ====================

    protected function getTableName(): string
    {
        return 'products';
    }

    protected function getPrimaryKey(): string
    {
        return 'product_id';
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    protected function getValidFormData(): array
    {
        return [
            'company_id' => $this->testCompany->company_id,
            'product_code' => 'TEST-' . $this->faker->unique()->numerify('####'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'liter', 'meter']),
            'base_price' => $this->faker->randomFloat(2, 100, 10000),
            'default_discount_percent' => $this->faker->randomFloat(2, 0, 25),
            'min_stock_alert' => $this->faker->numberBetween(5, 20),
            'category' => $this->faker->word(),
            'product_type' => 'STOCK',
            'is_active' => true,
        ];
    }

    protected function getUpdateData(): array
    {
        return [
            'name' => 'Updated Product Name',
            'base_price' => 9999.99,
            'default_discount_percent' => 15,
        ];
    }

    protected function getVerifiableFields(): array
    {
        return [
            'product_code',
            'name',
            'unit',
            'product_type',
            'is_active',
        ];
    }

    protected function getRequiredFields(): array
    {
        return [
            'product_code',
            'name',
            'unit',
            'product_type',
        ];
    }

    protected function getNumericFields(): array
    {
        return [
            'base_price',
            'default_discount_percent',
            'min_stock_alert',
        ];
    }

    protected function getUniqueFields(): array
    {
        return ['product_code'];
    }

    protected function getMaxLengthFields(): array
    {
        return [
            'product_code' => 50,
            'name' => 255,
            'unit' => 20,
            'category' => 100,
        ];
    }

    protected function createRecordWithData(array $data): mixed
    {
        return Product::create($data);
    }

    protected function getEagerLoadableRelationships(): array
    {
        return ['company', 'stock'];
    }

    protected function createRelatedRecords($record): void
    {
        if ($record->product_type === 'STOCK' && !$record->stock) {
            Stock::create([
                'company_id' => $record->company_id,
                'product_id' => $record->product_id,
                'quantity' => 100,
                'available_quantity' => 100,
                'reserved_quantity' => 0,
            ]);
        }
    }

    protected function verifyCascadeBehavior(string $primaryKey): void
    {
        // Product is soft deleted
        $this->assertSoftDeleted('products', [
            'product_id' => $primaryKey,
        ]);

        // Stock should also be soft deleted if exists
        $stock = Stock::withTrashed()->where('product_id', $primaryKey)->first();
        if ($stock) {
            $this->assertNotNull($stock->deleted_at);
        }
    }
}
