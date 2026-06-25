<?php

namespace Tests\Unit;

use App\Http\Controllers\DashboardController;
use PHPUnit\Framework\TestCase;

class DashboardControllerMonthKeyTest extends TestCase
{
    public function test_month_key_and_label_are_consistent(): void
    {
        $controller = new DashboardController();
        $reflection = new \ReflectionClass($controller);

        $monthKeyMethod = $reflection->getMethod('monthKey');
        $monthKeyMethod->setAccessible(true);

        $monthLabelMethod = $reflection->getMethod('monthLabel');
        $monthLabelMethod->setAccessible(true);

        $this->assertSame('2026-Jan', $monthKeyMethod->invoke($controller, 2026, 1));
        $this->assertSame('2026 Jan', $monthLabelMethod->invoke($controller, 2026, 1));
    }
}
