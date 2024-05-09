<?php

// Carga automática de clases a través del autoloader
require_once "autoloader.php";

// Crear una instancia de la clase Lighting
$cartera = new Lighting();

// Importar datos de lamps
$cartera->importLamps("lighting.csv");
echo "¡Datos de clientes importados con éxito!\n";

?>


