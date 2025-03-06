<?php
namespace App\Service;

class DataExporterService
{
// src/Service/DataExporterService.php
public function exportToCsv(array $data, string $filename): void
{
    $file = fopen($filename, 'w');
    if (!empty($data)) {
        // En-têtes corrects pour Prophet
        fputcsv($file, ['ds', 'y']);
        foreach ($data as $row) {
            // Vérifier si la clé 'date' existe
            if (array_key_exists('date', $row) && array_key_exists('quantite', $row)) {
                fputcsv($file, [
                    'ds' => $row['date'],
                    'y' => $row['quantite'],
                ]);
            }
        }
    }
    fclose($file);
}}