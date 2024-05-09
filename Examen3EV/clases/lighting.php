<?php

require_once 'conexion.php';

class Lighting extends Conexion {
    private $currentFilter;
    public function __construct()
    {
        session_start();
        parent::connect();
        if (isset($_POST["filter"])) {
            $this->currentFilter =  $_POST["filter"];
            $_SESSION["currentFilter"] = $_POST["filter"];
        } elseif (isset($_SESSION["currentFilter"])) {
            $this->currentFilter = $_SESSION["currentFilter"];
        } else {
            $this->currentFilter = "all";
            $_SESSION["currentFilter"] = $this->currentFilter;
        }
    }

    public function importLamps($file)
    {
        try {
            $this->conn->begin_transaction();
            $sqlDelete = "DELETE FROM lamps";
            $result = mysqli_query($this->conn, $sqlDelete);
            if (!$result) {
                throw new Exception("Error executing query: ". mysqli_error($this->conn));
            }

            $rowsDeleted = mysqli_affected_rows($this->conn);
            echo "Filas borradas ". $rowsDeleted. "<br>";
            $lampId = "";
            $lampName = "";
            $lampModel = "";
            $lampzone = "";
            $lampOn = "";
            $stmtInsert = $this->conn->prepare("INSERT INTO lamps VALUES(?,?,?,?,?)");
            $stmtInsert->bind_param("isiii", $lampId, $lampName, $lampModel, $lampzone, $lampOn);

            $gestor = fopen($file, "r");
            $linesCount = 0;
            while (($data = fgetcsv($gestor))!== false) {
                $lampId = $data[0];
                $lampName = $data[1];
                $lampModel = $this->getModelId($data[2]);
                $lampzone = $this->getZoneId($data[3]);
                $lampOn = ($data[4] == 'on')? 1 : 0;

                $stmtInsert->execute();
                $linesCount++;
            }
            fclose($gestor);
            echo "Filas importadas con éxito ". $linesCount. "<br>";
            $this->conn->commit();
        } catch (Exception $e) {
            echo 'Falló la importación: '. $e->getMessage();
        }
    }

    private function getModelId($modelPartNumber)
    {
        $sql = "SELECT model_id FROM lamp_models WHERE model_part_number = '$modelPartNumber'";
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: ". mysqli_error($this->conn));
        }
    
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        return $row["model_id"];
    }

    private function getZoneId($zoneName)
    {
        $sql = "SELECT zone_id FROM zones WHERE zone_name = '$zoneName'";
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: ". mysqli_error($this->conn));
        }

        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        return $row["zone_id"];
    }

    private function getAllLamps()
    {
        try {
            $filter = $this->currentFilter!= "all"? "WHERE zones.zone_id = ". $this->currentFilter : "";
    
            $sqlAll = "SELECT lamps.lamp_id, lamps.lamp_name, lamp_on, lamp_models.model_part_number,lamp_models.model_wattage, zones.zone_name FROM lamps INNER JOIN lamp_models ON lamps.lamp_model=lamp_models.model_id INNER JOIN zones ON lamps.lamp_zone = zones.zone_id ". $filter. " ORDER BY lamps.lamp_id;";
    
            $result = $this->conn->query($sqlAll);
            if (!$result) {
                throw new Exception("Error en la consulta: ". mysqli_error($this->conn));
            }
    
            $lamps = [];
            while ($lamp = mysqli_fetch_assoc($result)) {
                array_push($lamps, new Lamp(
                    $lamp["lamp_id"],
                    $lamp["lamp_name"],
                    ($lamp["lamp_on"] == 1? true : false),
                    $lamp["model_part_number"],
                    $lamp["model_wattage"],
                    $lamp["zone_name"]
                ));
            }
    
            mysqli_free_result($result);
    
            return $lamps;
        } catch (Exception $e) {
            echo 'Error al obtener lamps: '. $e->getMessage();
        }
    }

    public function changeStatus($id, $status)
    {
        try {
            $stmtInsert = $this->conn->prepare("UPDATE lamps SET lamp_on = ? WHERE lamp_id = ?");
            $stmtInsert->bindParam(1, $status, PDO::PARAM_BOOL);
            $stmtInsert->bindParam(2, $id, PDO::PARAM_INT);

            $stmtInsert->execute();
            $stmtInsert->debugDumpParams();
            return $stmtInsert->rowCount();
        } catch (Exception $e) {
            echo 'Falló la actualización: ' . $e->getMessage();
        }
    }

    public function drawLampsList()
    {
        $lamps = $this->getAllLamps();
        $output = "";
        foreach ($lamps as $lamp) {
            $state = $lamp->getLampOn() ? "on" : "off";
            $changeState = $lamp->getLampOn() ? "off" : "on";
            $output .= "<div class='element $state'>";
            $output .= "<h4><a href='changestatus.php?id=" . $lamp->getLampId() . "&status=$changeState'><img src='img/bulb-icon-$state.png'></a> " . $lamp->getLampName() . "</h4>";
            $output .= "<h1>" . $lamp->getModelWattage() . " W.</h1>";
            $output .= "<h4>" . $lamp->getZoneName() . "</h4>";
            $output .= "</div>";
        }
        return $output;
    }

    public function drawZonesOptions()
    {
        $selectedZone =  $this->currentFilter;
        $sql = "SELECT * FROM zones";
        $result = $this->conn->query($sql);
        $zones = $result->fetch_all(MYSQLI_ASSOC);
        $output = "<option value='all'>All</option>";
        foreach ($zones as $zone) {
            $selected = ($zone["zone_id"] == $selectedZone)? "selected='selected'" : "";
            $output.= "<option value='". $zone["zone_id"]. "' $selected>". $zone["zone_name"]. "</option>";
        }
        return $output;
    }

}