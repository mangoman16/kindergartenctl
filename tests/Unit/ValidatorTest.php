<?php
/**
 * Validator Unit Tests
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Validator;

class ValidatorTest extends TestCase
{
    public function testRequiredFieldPasses(): void
    {
        $validator = new Validator(['name' => 'Test']);
        $result = $validator->validate(['name' => 'required']);

        $this->assertTrue($result);
        $this->assertEmpty($validator->errors());
    }

    public function testRequiredFieldFails(): void
    {
        $validator = new Validator(['name' => '']);
        $result = $validator->validate(['name' => 'required']);

        $this->assertFalse($result);
        $this->assertNotEmpty($validator->errors());
    }

    public function testEmailValidation(): void
    {
        $validator = new Validator(['email' => 'invalid']);
        $result = $validator->validate(['email' => 'email']);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->errors());

        $validator = new Validator(['email' => 'test@example.com']);
        $result = $validator->validate(['email' => 'email']);

        $this->assertTrue($result);
    }

    public function testMinLengthValidation(): void
    {
        $validator = new Validator(['password' => 'abc']);
        $result = $validator->validate(['password' => 'min:8']);

        $this->assertFalse($result);
        $this->assertArrayHasKey('password', $validator->errors());

        $validator = new Validator(['password' => 'abcdefgh']);
        $result = $validator->validate(['password' => 'min:8']);

        $this->assertTrue($result);
    }

    public function testMaxLengthValidation(): void
    {
        $validator = new Validator(['name' => str_repeat('a', 101)]);
        $result = $validator->validate(['name' => 'max:100']);

        $this->assertFalse($result);

        $validator = new Validator(['name' => str_repeat('a', 100)]);
        $result = $validator->validate(['name' => 'max:100']);

        $this->assertTrue($result);
    }

    public function testNumericValidation(): void
    {
        $validator = new Validator(['age' => 'not-a-number']);
        $result = $validator->validate(['age' => 'numeric']);

        $this->assertFalse($result);

        $validator = new Validator(['age' => '25']);
        $result = $validator->validate(['age' => 'numeric']);

        $this->assertTrue($result);
    }

    public function testIntegerValidation(): void
    {
        $validator = new Validator(['count' => '3.5']);
        $result = $validator->validate(['count' => 'integer']);

        $this->assertFalse($result);

        $validator = new Validator(['count' => '42']);
        $result = $validator->validate(['count' => 'integer']);

        $this->assertTrue($result);
    }

    public function testInValidation(): void
    {
        $validator = new Validator(['status' => 'unknown']);
        $result = $validator->validate(['status' => 'in:active,inactive']);

        $this->assertFalse($result);

        $validator = new Validator(['status' => 'active']);
        $result = $validator->validate(['status' => 'in:active,inactive']);

        $this->assertTrue($result);
    }

    public function testConfirmedValidation(): void
    {
        $validator = new Validator([
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ]);
        $result = $validator->validate(['password' => 'confirmed']);

        $this->assertFalse($result);

        $validator = new Validator([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $result = $validator->validate(['password' => 'confirmed']);

        $this->assertTrue($result);
    }

    public function testDateValidation(): void
    {
        $validator = new Validator(['date' => 'invalid-date']);
        $result = $validator->validate(['date' => 'date']);

        $this->assertFalse($result);

        $validator = new Validator(['date' => '2024-12-25']);
        $result = $validator->validate(['date' => 'date']);

        $this->assertTrue($result);
    }

    public function testMultipleRules(): void
    {
        $validator = new Validator(['email' => 'x']);
        $result = $validator->validate(['email' => 'required|email|min:5']);

        $this->assertFalse($result);
        // Should have errors for email format and min length
        $this->assertNotEmpty($validator->errors()['email']);
    }

    public function testStaticMakeMethod(): void
    {
        $validator = Validator::make(['name' => ''], ['name' => 'required']);

        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->passes());
    }

    public function testOptionalFieldsSkipped(): void
    {
        $validator = new Validator(['name' => 'Test']);
        $result = $validator->validate([
            'name' => 'required',
            'email' => 'email', // Optional - not provided, should not fail
        ]);

        $this->assertTrue($result);
    }
}
