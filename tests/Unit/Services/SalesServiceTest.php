<?php

namespace Tests\Unit\Services;

use App\Services\SalesService;
use Tests\TestCase;

class SalesServiceTest extends TestCase
{
    protected SalesService $salesService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesService = new SalesService();
    }

    public function test_calculates_vat_correctly(): void
    {
        $amount = 1000;
        $vat = $this->salesService->calculateVAT($amount);

        // Default VAT rate is 7.5%
        $this->assertEquals(75, $vat);
    }

    public function test_calculates_vat_with_custom_rate(): void
    {
        $amount = 1000;
        $vat = $this->salesService->calculateVAT($amount, 0.10); // 10%

        $this->assertEquals(100, $vat);
    }

    public function test_calculates_vat_rounds_correctly(): void
    {
        $amount = 999.99;
        $vat = $this->salesService->calculateVAT($amount);

        // 999.99 * 0.075 = 74.999... rounds to 75
        $this->assertEquals(75, $vat);
    }

    public function test_calculates_zero_vat_for_zero_amount(): void
    {
        $vat = $this->salesService->calculateVAT(0);

        $this->assertEquals(0, $vat);
    }

    public function test_vat_rate_constant_is_correct(): void
    {
        $this->assertEquals(0.075, SalesService::VAT_RATE);
    }

    public function test_currency_constant_is_naira(): void
    {
        $this->assertEquals('₦', SalesService::CURRENCY);
    }

    public function test_calculates_vat_for_large_amounts(): void
    {
        $amount = 1000000; // 1 million
        $vat = $this->salesService->calculateVAT($amount);

        $this->assertEquals(75000, $vat);
    }

    public function test_calculates_vat_for_decimal_amounts(): void
    {
        $amount = 150.50;
        $vat = $this->salesService->calculateVAT($amount);

        // 150.50 * 0.075 = 11.2875 rounds to 11.29
        $this->assertEquals(11.29, $vat);
    }
}
