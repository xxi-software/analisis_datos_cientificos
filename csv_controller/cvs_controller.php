<?php
require_once __DIR__ . '/../vendor/autoload.php';

use League\Csv\Reader;

class CSVController
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  public function processCSV($filePath, $fileName)
  {
    // Verifica si el archivo existe
    if (!file_exists($filePath)) {
      echo "El archivo no existe.";
      exit;
    }

    // Crea un lector CSV
    $csv = Reader::createFromPath($filePath, 'r');
    $csv->setHeaderOffset(0); // Establece la primera fila como encabezado

    // Obtener los encabezados
    $headers = $csv->getHeader();
    // Obtener los registros
    $records = iterator_to_array($csv->getRecords());

    // Itera sobre los registros y los inserta en la base de datos
    $this->insertData($records, $headers, $fileName);
    echo "Datos importados exitosamente.";
  }

  public function uploadCSV($file)
  {
    // Verifica si se recibió un archivo CSV por POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($file)) {
      // Verifica si el archivo fue subido sin errores
      if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "Error al subir el archivo: " . $file['error'];
        exit;
      }
      // Verifica si el archivo es un CSV
      $fileType = mime_content_type($file['tmp_name']);
      if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
        echo "El archivo no es un CSV válido.";
        exit;
      }
      // Verifica el tamaño del archivo
      $fileSize = $file['size'];
      if ($fileSize > 2 * 1024 * 1024) { // 2 MB
        echo "El archivo es demasiado grande. El tamaño máximo permitido es de 2 MB.";
        exit;
      }
      // Verifica la extensión del archivo
      $fileName = $file['name'];
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

      // Procesa el archivo CSV
      $this->processCSV($file['tmp_name'], $fileName);
    } else {
      echo "No se ha recibido ningún archivo.";
    }
  }

  private function insertData($data, $headers, $fileName)
  {
    // Declarar variables para almacenar los datos
    $headersJson = json_encode($headers);
    $dataJson = json_encode($data);
    $name = $fileName;
    // Prepara la consulta SQL para insertar los datos
    $stmt = $this->conn->prepare("INSERT INTO csv_files (name, headers, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $headersJson, $dataJson);
    $stmt->execute();
    $stmt->close();
  }
}

// Uso del controlador
$conn = new mysqli("localhost", "root", "", "data_csv");
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}
$csvController = new CSVController($conn);
if (isset($_FILES['file'])) {
  $csvController->uploadCSV($_FILES['file']);
} else {
  echo "No se ha recibido ningún archivo.";
}