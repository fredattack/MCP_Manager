<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * @group utils
 * @group unit
 */
class StringUtilsTest extends TestCase
{
    /**
     * Test the string reversal function.
     */
    public function test_string_reversal()
    {
        // Create a simple function to reverse a string
        $reverseString = function (string $input): string {
            return strrev($input);
        };

        // Test with a simple string
        $input = 'Hello, World!';
        $expected = '!dlroW ,olleH';

        $result = $reverseString($input);

        $this->assertEquals($expected, $result);
    }
}
