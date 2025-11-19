<?php

namespace Tests\Unit\Traits;

use App\Models\Stock;

/**
 * Trait for testing resource relationships
 *
 * Provides methods to test:
 * - BelongsTo relationships
 * - HasOne relationships
 * - HasMany relationships
 * - Relationship constraints
 */
trait ResourceRelationshipTestTrait
{
    /**
     * Test that model has correct company relationship
     */
    public function testBelongsToCompany(): void
    {
        $record = $this->createTestRecord($this->getModelClass());

        $this->assertNotNull($record->company);
        $this->assertEquals($this->testCompany->company_id, $record->company->company_id);
    }

    /**
     * Test that relationship is properly loaded
     */
    public function testCanEagerLoadRelationships(): void
    {
        $record = $this->createTestRecord($this->getModelClass());

        $relationships = $this->getEagerLoadableRelationships();

        $loadedRecord = $this->getModelClass()::with($relationships)
            ->find($record->{$this->getPrimaryKey()});

        foreach ($relationships as $relation) {
            $this->assertTrue($loadedRecord->relationLoaded($relation));
        }
    }

    /**
     * Test that deleting parent cascades correctly
     */
    public function testDeletingParentHandlesRelationships(): void
    {
        $record = $this->createTestRecord($this->getModelClass());
        $primaryKey = $record->{$this->getPrimaryKey()};

        // Create related records if applicable
        $this->createRelatedRecords($record);

        // Delete parent
        $record->delete();

        // Verify cascade behavior
        $this->verifyCascadeBehavior($primaryKey);
    }

    /**
     * Get relationships that can be eager loaded
     */
    abstract protected function getEagerLoadableRelationships(): array;

    /**
     * Create related records for testing
     */
    abstract protected function createRelatedRecords($record): void;

    /**
     * Verify cascade behavior after deletion
     */
    abstract protected function verifyCascadeBehavior(string $primaryKey): void;
}
