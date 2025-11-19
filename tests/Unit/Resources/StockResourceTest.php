<?php

namespace Tests\Unit\Resources;

use Tests\Unit\ResourceTestCase;
use Tests\Unit\Traits\ResourceCRUDTestTrait;
use Tests\Unit\Traits\ResourceValidationTestTrait;
use Tests\Unit\Traits\ResourceRelationshipTestTrait;
use App\Models\Stock;
use App\Models\Product;

/**
 * StockResource Unit Tests
 *
 * Tests for Stock resource including:
 * - Stock quantity management
 * - Available vs Reserved quantity
 * - Low stock alerts
 * - Stock movements tracking
 */
class StockResourceTest extends ResourceTestCase
{
    use ResourceCRUDTestTrait, ResourceValidationTestTrait, ResourceRelationshipTestTrait;

    /**
     * Test creating stock for a product
     */
    public function testCanCreateStockForProduct(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'product_type' => 'STOCK',
        ]);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $this->assertNotNull($stock);
        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(100, $stock->available_quantity);
        $this->assertEquals(0, $stock->reserved_quantity);
    }

    /**
     * Test available quantity calculation
     */
    public function testAvailableQuantityCalculation(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'product_type' => 'STOCK',
        ]);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 80,
            'reserved_quantity' => 20,
        ]);

        // Verify calculation: quantity = available + reserved
        $this->assertEquals(
            $stock->quantity,
            $stock->available_quantity + $stock->reserved_quantity
        );
    }

    /**
     * Test low stock detection
     */
    public function testLowStockDetection(): void
    {
        $product = $this->createTestRecord(Product::class, [
            'product_type' => 'STOCK',
            'min_stock_alert' => 10,
        ]);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 5,
            'available_quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $product->refresh();

        $isLowStock = $stock->available_quantity <= $product->min_stock_alert;
        $this->assertTrue($isLowStock);
    }

    /**
     * Test stock can be reserved
     */
    public function testCanReserveStock(): void
    {
        $product = $this->createTestRecord(Product::class);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // Reserve 30 units
        $reserveQuantity = 30;
        $stock->update([
            'available_quantity' => $stock->available_quantity - $reserveQuantity,
            'reserved_quantity' => $stock->reserved_quantity + $reserveQuantity,
        ]);

        $stock->refresh();

        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(70, $stock->available_quantity);
        $this->assertEquals(30, $stock->reserved_quantity);
    }

    /**
     * Test stock can be released from reservation
     */
    public function testCanReleaseReservedStock(): void
    {
        $product = $this->createTestRecord(Product::class);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 70,
            'reserved_quantity' => 30,
        ]);

        // Release 15 units
        $releaseQuantity = 15;
        $stock->update([
            'available_quantity' => $stock->available_quantity + $releaseQuantity,
            'reserved_quantity' => $stock->reserved_quantity - $releaseQuantity,
        ]);

        $stock->refresh();

        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(85, $stock->available_quantity);
        $this->assertEquals(15, $stock->reserved_quantity);
    }

    /**
     * Test cannot reserve more than available
     */
    public function testCannotReserveMoreThanAvailable(): void
    {
        $product = $this->createTestRecord(Product::class);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 50,
            'reserved_quantity' => 50,
        ]);

        // Try to reserve more than available
        $attemptReserve = 60;
        $canReserve = $stock->available_quantity >= $attemptReserve;

        $this->assertFalse($canReserve);
    }

    /**
     * Test stock adjustment increases quantity
     */
    public function testStockAdjustmentIncrease(): void
    {
        $product = $this->createTestRecord(Product::class);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // Add 50 units
        $addQuantity = 50;
        $stock->update([
            'quantity' => $stock->quantity + $addQuantity,
            'available_quantity' => $stock->available_quantity + $addQuantity,
        ]);

        $stock->refresh();

        $this->assertEquals(150, $stock->quantity);
        $this->assertEquals(150, $stock->available_quantity);
    }

    /**
     * Test stock adjustment decreases quantity
     */
    public function testStockAdjustmentDecrease(): void
    {
        $product = $this->createTestRecord(Product::class);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // Remove 25 units
        $removeQuantity = 25;
        $stock->update([
            'quantity' => $stock->quantity - $removeQuantity,
            'available_quantity' => $stock->available_quantity - $removeQuantity,
        ]);

        $stock->refresh();

        $this->assertEquals(75, $stock->quantity);
        $this->assertEquals(75, $stock->available_quantity);
    }

    /**
     * Test stock relationships
     */
    public function testStockHasProductRelationship(): void
    {
        $product = $this->createTestRecord(Product::class);

        $stock = Stock::create([
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        $this->assertNotNull($stock->product);
        $this->assertEquals($product->product_id, $stock->product->product_id);
        $this->assertEquals($product->name, $stock->product->name);
    }

    // ==================== Trait Implementations ====================

    protected function getTableName(): string
    {
        return 'stocks';
    }

    protected function getPrimaryKey(): string
    {
        return 'stock_id';
    }

    protected function getModelClass(): string
    {
        return Stock::class;
    }

    protected function getValidFormData(): array
    {
        $product = $this->createTestRecord(Product::class, [
            'product_type' => 'STOCK',
        ]);

        return [
            'company_id' => $this->testCompany->company_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
        ];
    }

    protected function getUpdateData(): array
    {
        return [
            'quantity' => 150,
            'available_quantity' => 120,
            'reserved_quantity' => 30,
        ];
    }

    protected function getVerifiableFields(): array
    {
        return [
            'quantity',
            'available_quantity',
            'reserved_quantity',
        ];
    }

    protected function getRequiredFields(): array
    {
        return [
            'product_id',
            'quantity',
            'available_quantity',
            'reserved_quantity',
        ];
    }

    protected function getNumericFields(): array
    {
        return [
            'quantity',
            'available_quantity',
            'reserved_quantity',
        ];
    }

    protected function getUniqueFields(): array
    {
        return [];
    }

    protected function getMaxLengthFields(): array
    {
        return [];
    }

    protected function createRecordWithData(array $data): mixed
    {
        return Stock::create($data);
    }

    protected function getEagerLoadableRelationships(): array
    {
        return ['company', 'product'];
    }

    protected function createRelatedRecords($record): void
    {
        // Stock doesn't have child records to create
    }

    protected function verifyCascadeBehavior(string $primaryKey): void
    {
        $this->assertSoftDeleted('stocks', [
            'stock_id' => $primaryKey,
        ]);
    }
}
