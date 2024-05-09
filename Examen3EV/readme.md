class Lighting {
    private $currentFilter;

    public function __construct($currentFilter) {
        $this->currentFilter = $currentFilter;
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
}