<?php
require_once __DIR__ . '/../vendor/autoload.php';

use League\Csv\Reader;
// src/data.php


// Verifica si se recibió un archivo CSV por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  // Verifica si el archivo fue subido sin errores
  if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo "Error al subir el archivo: " . $_FILES['file']['error'];
    exit;
  }
  // Verifica si el archivo es un CSV
  $fileType = mime_content_type($_FILES['file']['tmp_name']);
  if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
    echo "El archivo no es un CSV válido.";
    exit;
  }
  // Verifica el tamaño del archivo
  $fileSize = $_FILES['file']['size'];
  if ($fileSize > 2 * 1024 * 1024) { // 2 MB
    echo "El archivo es demasiado grande. El tamaño máximo permitido es de 2 MB.";
    exit;
  }
  // Verifica la extensión del archivo
  $fileName = $_FILES['file']['name'];
  $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
  if ($fileExtension !== 'csv') {
    echo "El archivo no tiene la extensión .csv.";
    exit;
  }
  // Verifica el nombre del archivo
  $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME);
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $fileBaseName)) {
    echo "El nombre del archivo no es válido. Solo se permiten letras, números y guiones bajos.";
    exit;
  }
  // Verifica el contenido del archivo    
  $csv = Reader::createFromPath($_FILES['file']['tmp_name'], 'r');
  $csv->setHeaderOffset(0); // Establece la primera fila como encabezado
  $records = iterator_to_array($csv->getRecords());
  // Guarda el archivo CSV en formato json para más adelante
  $jsonFileName = $fileBaseName . '.json';
  $jsonFilePath = __DIR__ . '/../uploads/' . $jsonFileName;

  // Convierte los registros a JSON
  $jsonData = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  // Verifica si la conversión fue exitosa
  if ($jsonData === false) {
    echo "Error al convertir los datos a JSON.";
    exit;
  }

  // Guarda el JSON en un archivo
  if (file_put_contents($jsonFilePath, $jsonData) === false) {
    echo "Error al guardar el archivo JSON.";
    exit;
  }
  // Guarda el archivo CSV en el servidor
  $destinationDir = __DIR__ . '/../uploads/';
  if (!is_dir($destinationDir)) {
    mkdir($destinationDir, 0777, true);
  }
  // Mueve el archivo subido a la carpeta de destino
  $fileName = $fileBaseName . '_' . date('YmdHis') . '.csv';
  $filePath = $destinationDir . $fileName;
  if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
    echo "Error al mover el archivo subido.";
    exit;
  }

  // Procesa el archivo CSV y genera un gráfico
  // Aquí puedes agregar el código para procesar los datos y generar el gráfico
  echo json_encode($records);
  exit;
} else {
  echo "No se recibió ningún archivo CSV.";
  exit;
}
