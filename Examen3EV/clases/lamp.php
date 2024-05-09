<?php

class Lamp {
    private $lampId;
    private $lampName;
    private $lampOn;
    private $modelPartNumber;
    private $modelWattage;
    private $zoneName;

    public function __construct($lampId, $lampName, $lampOn, $modelPartNumber, $modelWattage, $zoneName)
    {
        $this->lampId = $lampId;
        $this->lampName = $lampName;
        $this->lampOn = $lampOn;
        $this->modelPartNumber = $modelPartNumber;
        $this->modelWattage = $modelWattage;
        $this->zoneName = $zoneName;
    }

    public function getLampId()
    {
        return $this->lampId;
    }

    public function getLampName()
    {
        return $this->lampName;
    }

    public function getLampOn()
    {
        return $this->lampOn;
    }

    public function setLampOn($lampOn): self
    {
        $this->lampOn = $lampOn;

        return $this;
    }

    public function getModelPartNumber()
    {
        return $this->modelPartNumber;
    }

    public function getModelWattage()
    {
        return $this->modelWattage;
    }

    public function getZoneName()
    {
        return $this->zoneName;
    }

}
