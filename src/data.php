<?php
// src/data.php

// Verifica si se recibió un archivo CSV por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        $header = fgetcsv($handle); // Lee la cabecera

        echo "<table border='1'>";
        // Encabezados
        echo "<tr>";
        foreach ($header as $col) {
            echo "<th>" . htmlspecialchars($col) . "</th>";
        }
        echo "</tr>";

        // Filas
        while (($row = fgetcsv($handle)) !== false) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        fclose($handle);
        exit;
    } else {
        echo "No se pudo abrir el archivo CSV.";
        exit;
    }
} else {
    echo "No se recibió ningún archivo CSV.";
    exit;
}