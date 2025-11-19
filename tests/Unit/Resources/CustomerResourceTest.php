<?php

namespace Tests\Unit\Resources;

use Tests\Unit\ResourceTestCase;
use Tests\Unit\Traits\ResourceCRUDTestTrait;
use Tests\Unit\Traits\ResourceValidationTestTrait;
use Tests\Unit\Traits\ResourceRelationshipTestTrait;
use App\Models\Customer;

/**
 * CustomerResource Unit Tests
 *
 * Tests for Customer resource including:
 * - Customer management
 * - Contact information validation
 * - Customer status
 * - Address management
 */
class CustomerResourceTest extends ResourceTestCase
{
    use ResourceCRUDTestTrait, ResourceValidationTestTrait, ResourceRelationshipTestTrait;

    /**
     * Test creating a customer with valid data
     */
    public function testCanCreateCustomerWithValidData(): void
    {
        $data = $this->getValidFormData();

        $customer = Customer::create($data);

        $this->assertNotNull($customer);
        $this->assertEquals($data['name'], $customer->name);
        $this->assertEquals($data['email'], $customer->email);
    }

    /**
     * Test customer email format validation
     */
    public function testCustomerEmailMustBeValidFormat(): void
    {
        $data = $this->getValidFormData();
        $data['email'] = 'invalid-email-format';

        $this->expectException(\Illuminate\Database\QueryException::class);
        Customer::create($data);
    }

    /**
     * Test customer phone number is stored correctly
     */
    public function testCustomerPhoneNumberStorage(): void
    {
        $data = $this->getValidFormData();
        $data['phone'] = '+62 812 3456 7890';

        $customer = Customer::create($data);

        $this->assertEquals('+62 812 3456 7890', $customer->phone);
    }

    /**
     * Test customer can have address
     */
    public function testCustomerCanHaveAddress(): void
    {
        $data = $this->getValidFormData();
        $data['address'] = 'Jl. Test No. 123, Jakarta';

        $customer = Customer::create($data);

        $this->assertNotNull($customer->address);
        $this->assertEquals('Jl. Test No. 123, Jakarta', $customer->address);
    }

    /**
     * Test customer can be activated/deactivated
     */
    public function testCanToggleCustomerActiveStatus(): void
    {
        $customer = $this->createTestRecord(Customer::class, [
            'is_active' => true,
        ]);

        $this->assertTrue($customer->is_active);

        $customer->update(['is_active' => false]);
        $customer->refresh();

        $this->assertFalse($customer->is_active);
    }

    /**
     * Test can search customers by name
     */
    public function testCanSearchCustomersByName(): void
    {
        $customer = $this->createTestRecord(Customer::class, [
            'name' => 'PT. Test Company Indonesia',
        ]);

        $found = Customer::where('name', 'like', '%Test Company%')->first();

        $this->assertNotNull($found);
        $this->assertEquals($customer->customer_id, $found->customer_id);
    }

    // ==================== Trait Implementations ====================

    protected function getTableName(): string
    {
        return 'customers';
    }

    protected function getPrimaryKey(): string
    {
        return 'customer_id';
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }

    protected function getValidFormData(): array
    {
        return [
            'company_id' => $this->testCompany->company_id,
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'is_active' => true,
        ];
    }

    protected function getUpdateData(): array
    {
        return [
            'name' => 'Updated Customer Name',
            'phone' => '+62 811 9999 8888',
        ];
    }

    protected function getVerifiableFields(): array
    {
        return ['name', 'email', 'is_active'];
    }

    protected function getRequiredFields(): array
    {
        return ['name'];
    }

    protected function getNumericFields(): array
    {
        return [];
    }

    protected function getUniqueFields(): array
    {
        return [];
    }

    protected function getMaxLengthFields(): array
    {
        return [
            'name' => 255,
            'email' => 255,
        ];
    }

    protected function createRecordWithData(array $data): mixed
    {
        return Customer::create($data);
    }

    protected function getEagerLoadableRelationships(): array
    {
        return ['company'];
    }

    protected function createRelatedRecords($record): void
    {
        // No child records to create for customer
    }

    protected function verifyCascadeBehavior(string $primaryKey): void
    {
        $this->assertSoftDeleted('customers', [
            'customer_id' => $primaryKey,
        ]);
    }
}
