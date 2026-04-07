<?php

namespace Tests\Unit;

use App\Models\Diplome;
use App\Services\PlanningPreparationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PlanningPreparationServiceTest extends TestCase
{
    public function test_it_resolves_jury_size_for_supported_diplomas(): void
    {
        $service = new PlanningPreparationService();

        $this->assertSame(2, $service->resolveRequiredJurySizeForDiploma(new Diplome(['nom' => 'LICENCE NATIONALE'])));
        $this->assertSame(3, $service->resolveRequiredJurySizeForDiploma(new Diplome(['nom' => 'MASTER'])));
        $this->assertSame(3, $service->resolveRequiredJurySizeForDiploma(new Diplome(['nom' => 'INGENIEUR INFORMATIQUE'])));
    }

    public function test_it_rejects_unsupported_diplomas(): void
    {
        $service = new PlanningPreparationService();

        $this->expectException(InvalidArgumentException::class);

        $service->resolveRequiredJurySizeForDiploma(new Diplome(['nom' => 'CYCLE PREPARATOIRE INTEGRE']));
    }
}
