<?php
declare(strict_types=1);

use Battle\Tactic;
use PHPUnit\Framework\TestCase;

final class TacticTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
        $this->Tactic = new Tactic;
	}

	// TES METHODES DE TEST ---------------------
    public function test_next_case_direction_right(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B2', 'R'),
            'B3'
        );
    }

    public function test_next_case_direction_left(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B2', 'L'),
            'B1'
        );
    }

    public function test_next_case_direction_left_end(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B10', 'L'),
            'B9'
        );
    }

    public function test_next_case_direction_bottom(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B2', 'B'),
            'C2'
        );
    }

    public function test_next_case_direction_top(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B2', 'T'),
            'A2'
        );
    }

    
    public function test_next_case_direction_right_false(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B10', 'R'),
            false
        );
    }

    public function test_next_case_direction_left_false(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('B1', 'L'),
            false
        );
    }

    public function test_next_case_direction_bottom_false(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('J2', 'B'),
            false
        );
    }

    public function test_next_case_direction_top_false(): void
    {
        $this->assertEquals(
            $this->Tactic->nextCase('A2', 'T'),
            false
        );
    }

}