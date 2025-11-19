<?php

namespace Tests\Unit\Traits;

/**
 * Trait for testing CRUD operations on resources
 * 
 * Provides standard tests for:
 * - Creating records
 * - Reading/viewing records
 * - Updating records
 * - Deleting records (soft delete)
 * - Restoring deleted records
 */
trait ResourceCRUDTestTrait
{
    /**
     * Test creating a new record
     */
    public function testCanCreateRecord(): void
    {
        $data = $this->getValidFormData();
        
        $record = $this->createRecordWithData($data);
        
        $this->assertNotNull($record);
        $this->assertDatabaseHas($this->getTableName(), [
            $this->getPrimaryKey() => $record->{$this->getPrimaryKey()},
        ]);
        
        // Verify all fields were saved correctly
        foreach ($this->getVerifiableFields() as $field) {
            if (isset($data[$field])) {
                $this->assertEquals($data[$field], $record->$field);
            }
        }
    }

    /**
     * Test reading/viewing a record
     */
    public function testCanViewRecord(): void
    {
        $record = $this->createTestRecord($this->getModelClass());
        
        $retrieved = $this->getModelClass()::find($record->{$this->getPrimaryKey()});
        
        $this->assertNotNull($retrieved);
        $this->assertEquals($record->{$this->getPrimaryKey()}, $retrieved->{$this->getPrimaryKey()});
    }

    /**
     * Test updating a record
     */
    public function testCanUpdateRecord(): void
    {
        $record = $this->createTestRecord($this->getModelClass());
        $updatedData = $this->getUpdateData();
        
        $record->update($updatedData);
        $record->refresh();
        
        foreach ($updatedData as $field => $value) {
            $this->assertEquals($value, $record->$field);
        }
        
        $this->assertDatabaseHas($this->getTableName(), array_merge([
            $this->getPrimaryKey() => $record->{$this->getPrimaryKey()},
        ], $updatedData));
    }

    /**
     * Test soft deleting a record
     */
    public function testCanSoftDeleteRecord(): void
    {
        $record = $this->createTestRecord($this->getModelClass());
        $primaryKey = $record->{$this->getPrimaryKey()};
        
        $record->delete();
        
        // Record should exist but be soft deleted
        $this->assertSoftDeleted($this->getTableName(), [
            $this->getPrimaryKey() => $primaryKey,
        ]);
        
        // Verify can't be retrieved normally
        $retrieved = $this->getModelClass()::find($primaryKey);
        $this->assertNull($retrieved);
        
        // Verify can be retrieved with trashed
        $trashedRecord = $this->getModelClass()::withTrashed()->find($primaryKey);
        $this->assertNotNull($trashedRecord);
        $this->assertNotNull($trashedRecord->deleted_at);
    }

    /**
     * Test restoring a soft deleted record
     */
    public function testCanRestoreDeletedRecord(): void
    {
        $record = $this->createTestRecord($this->getModelClass());
        $primaryKey = $record->{$this->getPrimaryKey()};
        
        // Delete the record
        $record->delete();
        $this->assertSoftDeleted($this->getTableName(), [
            $this->getPrimaryKey() => $primaryKey,
        ]);
        
        // Restore the record
        $record->restore();
        
        // Verify record is restored
        $this->assertDatabaseHas($this->getTableName(), [
            $this->getPrimaryKey() => $primaryKey,
            'deleted_at' => null,
        ]);
        
        $retrieved = $this->getModelClass()::find($primaryKey);
        $this->assertNotNull($retrieved);
        $this->assertNull($retrieved->deleted_at);
    }

    /**
     * Test force deleting a record (permanent delete)
     */
    public function testCanForceDeleteRecord(): void
    {
        $record = $this->createTestRecord($this->getModelClass());
        $primaryKey = $record->{$this->getPrimaryKey()};
        
        $record->forceDelete();
        
        // Record should not exist at all
        $this->assertDatabaseMissing($this->getTableName(), [
            $this->getPrimaryKey() => $primaryKey,
        ]);
        
        $retrieved = $this->getModelClass()::withTrashed()->find($primaryKey);
        $this->assertNull($retrieved);
    }

    /**
     * Test bulk delete operation
     */
    public function testCanBulkDeleteRecords(): void
    {
        $records = collect();
        for ($i = 0; $i < 3; $i++) {
            $records->push($this->createTestRecord($this->getModelClass()));
        }
        
        $primaryKeys = $records->pluck($this->getPrimaryKey())->toArray();
        
        // Bulk delete
        $this->getModelClass()::whereIn($this->getPrimaryKey(), $primaryKeys)->delete();
        
        // Verify all are soft deleted
        foreach ($primaryKeys as $key) {
            $this->assertSoftDeleted($this->getTableName(), [
                $this->getPrimaryKey() => $key,
            ]);
        }
    }

    /**
     * Get table name for the model
     */
    abstract protected function getTableName(): string;

    /**
     * Get primary key field name
     */
    abstract protected function getPrimaryKey(): string;

    /**
     * Get fields to verify after creation
     */
    abstract protected function getVerifiableFields(): array;

    /**
     * Get data for update test
     */
    abstract protected function getUpdateData(): array;
}
