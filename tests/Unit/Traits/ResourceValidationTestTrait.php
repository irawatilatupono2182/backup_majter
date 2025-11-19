<?php

namespace Tests\Unit\Traits;

/**
 * Trait for testing resource validation rules
 * 
 * Provides methods to test form validation rules including:
 * - Required field validation
 * - Data type validation
 * - Unique constraint validation
 * - Custom validation rules
 */
trait ResourceValidationTestTrait
{
    /**
     * Test that required fields are validated
     */
    public function testRequiredFieldsValidation(): void
    {
        $requiredFields = $this->getRequiredFields();
        
        foreach ($requiredFields as $field) {
            $data = $this->getValidFormData();
            unset($data[$field]);
            
            $this->assertValidationFails($data, $field);
        }
    }

    /**
     * Test that numeric fields only accept numbers
     */
    public function testNumericFieldsValidation(): void
    {
        $numericFields = $this->getNumericFields();
        
        foreach ($numericFields as $field) {
            $data = $this->getValidFormData();
            $data[$field] = 'invalid_text';
            
            $this->assertValidationFails($data, $field);
        }
    }

    /**
     * Test that unique fields are validated
     */
    public function testUniqueFieldsValidation(): void
    {
        $uniqueFields = $this->getUniqueFields();
        
        foreach ($uniqueFields as $field) {
            // Create first record
            $firstRecord = $this->createTestRecord($this->getModelClass());
            
            // Try to create second record with same value
            $data = $this->getValidFormData();
            $data[$field] = $firstRecord->$field;
            
            $this->assertValidationFails($data, $field);
        }
    }

    /**
     * Test maximum length validation
     */
    public function testMaxLengthValidation(): void
    {
        $maxLengthFields = $this->getMaxLengthFields();
        
        foreach ($maxLengthFields as $field => $maxLength) {
            $data = $this->getValidFormData();
            $data[$field] = str_repeat('a', $maxLength + 1);
            
            $this->assertValidationFails($data, $field);
        }
    }

    /**
     * Assert that validation fails for given data and field
     */
    protected function assertValidationFails(array $data, string $field): void
    {
        try {
            $this->createRecordWithData($data);
            $this->fail("Expected validation to fail for field: {$field}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());
        }
    }

    /**
     * Get required fields for the resource
     */
    abstract protected function getRequiredFields(): array;

    /**
     * Get numeric fields for the resource
     */
    abstract protected function getNumericFields(): array;

    /**
     * Get unique fields for the resource
     */
    abstract protected function getUniqueFields(): array;

    /**
     * Get max length fields for the resource
     */
    abstract protected function getMaxLengthFields(): array;

    /**
     * Get valid form data for testing
     */
    abstract protected function getValidFormData(): array;

    /**
     * Get model class for the resource
     */
    abstract protected function getModelClass(): string;

    /**
     * Create record with given data
     */
    abstract protected function createRecordWithData(array $data): mixed;
}
