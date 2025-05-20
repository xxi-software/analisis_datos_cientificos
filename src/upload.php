<?php
/**
 * Formulario para subir datasets CSV, JSON o Excel
 */

require_once __DIR__ . '/../vendor/autoload.php';

use League\Csv\Reader;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileType = mime_content_type($fileTmpPath);

    // Solo procesar si es CSV
    if ($fileType === 'text/plain' || $fileType === 'text/csv' || pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) === 'csv') {
        try {
            $csv = Reader::createFromPath($fileTmpPath, 'r');
            $csv->setHeaderOffset(0); // Primera fila como encabezado

            $records = iterator_to_array($csv->getRecords());
            echo "<table border='1'>";
            // Encabezados
            $headers = $csv->getHeader();
            if (!empty($records)) {
                echo "<tr>";
                foreach ($headers as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                // Filas
                foreach ($records as $row) {
                    echo "<tr>";
                    foreach ($headers as $header) {
                        echo "<td>" . htmlspecialchars($row[$header] ?? '') . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "Error al procesar el archivo CSV: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Por favor, sube un archivo CSV válido.";
    }
}
?>

<div>
  <form action="http://localhost/php/analisis_datos_cientificos/src/data.php" method="post"
    enctype="multipart/form-data">
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload">
  </form>
</div>