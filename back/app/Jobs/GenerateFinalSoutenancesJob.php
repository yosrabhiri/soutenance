<?php

namespace App\Jobs;

use App\Models\PeriodeSoutenance;
use App\Services\SoutenanceGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateFinalSoutenancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(
        protected int $periodeId,
        protected array $options = []
    ) {
    }

    public function handle(SoutenanceGenerationService $generationService): void
    {
        $periode = PeriodeSoutenance::findOrFail($this->periodeId);

        $periode->update([
            'generation_status' => 'running',
            'generation_started_at' => now(),
            'generation_finished_at' => null,
            'generation_error' => null,
            'generation_algorithm' => $this->options['algorithm'] ?? 'cp_sat',
        ]);

        $result = $generationService->generateAndPersist($periode, $this->options);

        $periode->update([
            'generation_status' => 'completed',
            'generation_finished_at' => now(),
            'generation_error' => null,
            'generation_meta' => $result,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $periode = PeriodeSoutenance::find($this->periodeId);

        if (!$periode) {
            return;
        }

        $periode->update([
            'generation_status' => 'failed',
            'generation_finished_at' => now(),
            'generation_error' => $exception->getMessage(),
        ]);
    }
}
