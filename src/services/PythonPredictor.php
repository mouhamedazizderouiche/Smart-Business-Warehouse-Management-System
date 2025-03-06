<?php
namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PythonPredictor
{
    private string $pythonScriptPath;

    public function __construct(string $pythonScriptPath)
    {
        $this->pythonScriptPath = $pythonScriptPath;
    }

    public function predict(array $stockData): array
    {
        // Convertir les données en JSON
        $inputData = json_encode($stockData);

        // Exécuter le script Python
        $process = new Process(['python3', $this->pythonScriptPath, $inputData]);
        $process->run();

        // Vérifier si l'exécution a réussi
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Récupérer la sortie du script Python
        $output = $process->getOutput();
        return json_decode($output, true);
    }
}