<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * Base Test Case for Filament Resource Testing
 * 
 * Provides common functionality for testing Filament resources including:
 * - User authentication setup
 * - Company context management
 * - Common assertions for resource operations
 * - Database transaction handling
 */
abstract class ResourceTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $testUser;
    protected Company $testCompany;

    /**
     * Setup the test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test company
        $this->testCompany = Company::factory()->create([
            'company_name' => 'Test Company',
            'is_active' => true,
        ]);

        // Create test user with super admin role
        $this->testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign super admin role
        $this->testUser->assignRole('super_admin');

        // Login and set company context
        $this->actingAs($this->testUser);
        session(['selected_company_id' => $this->testCompany->company_id]);
    }

    /**
     * Assert that a resource table query returns expected count
     */
    protected function assertResourceCount(string $resourceClass, int $expectedCount): void
    {
        $this->assertDatabaseCount($resourceClass::getModel()::make()->getTable(), $expectedCount);
    }

    /**
     * Assert that a model exists in database with specific attributes
     */
    protected function assertModelExists(string $modelClass, array $attributes): void
    {
        $this->assertDatabaseHas((new $modelClass)->getTable(), $attributes);
    }

    /**
     * Assert that a model does not exist in database
     */
    protected function assertModelNotExists(string $modelClass, array $attributes): void
    {
        $this->assertDatabaseMissing((new $modelClass)->getTable(), $attributes);
    }

    /**
     * Create a test record for the given model
     */
    protected function createTestRecord(string $modelClass, array $attributes = []): mixed
    {
        return $modelClass::factory()->create(array_merge([
            'company_id' => $this->testCompany->company_id,
        ], $attributes));
    }

    /**
     * Get resource form data for testing
     */
    protected function getResourceFormData(string $resourceClass): array
    {
        $model = $resourceClass::getModel();
        return $model::factory()->make([
            'company_id' => $this->testCompany->company_id,
        ])->toArray();
    }
}
