<?php

namespace App\Services;

use RuntimeException;

class PythonCpSatPlanningGenerator
{
    public function generate(array $planningInput, array $options = []): array
    {
        $pythonBinary = env('PYTHON_BIN', 'python');
        $scriptPath = base_path('python/cp_sat_planner.py');

        if (!is_file($scriptPath)) {
            throw new RuntimeException('Script CP-SAT Python introuvable.');
        }

        $tempDir = storage_path('app/cp_sat');
        if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
            throw new RuntimeException('Impossible de créer le répertoire temporaire CP-SAT.');
        }

        $inputPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('cp_sat_input_', true) . '.json';
        $outputPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('cp_sat_output_', true) . '.json';

        file_put_contents($inputPath, json_encode([
            'planning_input' => $planningInput,
            'options' => [
                'max_time_in_seconds' => (float) ($options['max_time_in_seconds'] ?? 20),
                'num_search_workers' => (int) ($options['num_search_workers'] ?? 8),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $command = sprintf(
            '%s %s %s %s',
            escapeshellarg($pythonBinary),
            escapeshellarg($scriptPath),
            escapeshellarg($inputPath),
            escapeshellarg($outputPath)
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, base_path());

        if (!is_resource($process)) {
            @unlink($inputPath);
            throw new RuntimeException('Impossible de démarrer le solveur Python CP-SAT.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            @unlink($inputPath);
            @unlink($outputPath);

            $message = trim($stderr) ?: trim($stdout) ?: 'Le solveur CP-SAT Python a échoué.';
            throw new RuntimeException($message);
        }

        if (!is_file($outputPath)) {
            @unlink($inputPath);
            throw new RuntimeException('Le solveur CP-SAT Python n’a produit aucun résultat.');
        }

        $result = json_decode(file_get_contents($outputPath), true);

        @unlink($inputPath);
        @unlink($outputPath);

        if (!is_array($result)) {
            throw new RuntimeException('Résultat CP-SAT invalide.');
        }

        return $result;
    }
}
