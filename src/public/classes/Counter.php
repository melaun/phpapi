<?php

class Counter {

    private $pathToCounter;
    private $number;
    private $decodeJson;

    public function __construct($pathToCounter) {
        $this->pathToCounter = $pathToCounter . ".json";
    }

    /**
     * precte cislo z counter souboru pokud soubor nenajde
     * nebo je chybny vrati cislo ERR+datum
     * @return type
     */
    public function getCounter() {
//existuje file?
        if (!file_exists($this->pathToCounter)) {
            return 'ERR' . date("YmdHi");
        }
        $file = file_get_contents($this->pathToCounter);
//je nacten file?
        if (!$file) {
            return 'ERR' . date("YmdHi");
        }
        $json = json_decode($file, true);
// obsahuje json pozadovane data?
        if (!isset($json['number']) || empty($json['number'])) {
            return 'ERR' . date("YmdHi");
        }
        $this->decodeJson = $json;
        $this->number = $json['number'];
        return $this->number;
    }

    public function incrementCounter() {
        // je nastaveno cislo counteru?
        if ($this->number !== null) {
            // existuje counter file?
            if (file_exists($this->pathToCounter)) {
                $result = $this->saveCounter($this->incrementNumber($this->number));
                if ($result) {
                    return true;
                }
                return false;
            }
            return false;
        }
    }

    /**
     * vezme string prevede na cislo incrementuje ud2l8 ho osmimistny a
     * vrati string
     * @param type $number string/int
     * @return type string
     */
    private function incrementNumber($number) {
        $intNumber = intval($number);
        $intNumber++;
        return sprintf('%08d', $intNumber);
    }

    private function saveCounter($number) {
        if (!file_exists($this->pathToCounter)) {
            return false;
        } else {
            //edituji / ukladam json file
            $this->decodeJson['number'] = $number;
            $encodeJson = json_encode($this->decodeJson);
            file_put_contents($this->pathToCounter, $encodeJson);
            return true;
        }
    }

}
