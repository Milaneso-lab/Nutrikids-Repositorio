<?php

namespace Tests\Unit;

use App\Services\Nutricion\AntropometriaService;
use PHPUnit\Framework\TestCase;

class AntropometriaServiceTest extends TestCase
{
    private AntropometriaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AntropometriaService();
    }

    public function test_calculate_imc_with_talla_en_centimetros(): void
    {
        $imc = $this->service->calculateImc(40.0, 140.0);

        $this->assertSame(20.41, $imc);
    }

    public function test_calculate_imc_with_talla_en_metros(): void
    {
        $imc = $this->service->calculateImc(40.0, 1.4);

        $this->assertSame(20.41, $imc);
    }

    public function test_normalize_decimal_acepta_coma(): void
    {
        $this->assertSame(12.5, $this->service->normalizeDecimal('12,5 kg'));
    }

    public function test_classify_imc_pediatrico(): void
    {
        $this->assertSame('normal', $this->service->classifyImc(17.0, 10));
        $this->assertSame('obesidad', $this->service->classifyImc(26.0, 12));
    }
}
