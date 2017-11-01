<?php

class Item {

    public $number;
    public $name;
    public $ean;
    public $vat;
    public $priceVat;
    public $price;
    public $count;
    public $fieldNumber;

    public function print_me() {
        $message = $this->number . ", " .
                $this->name . ", " .
                $this->ean . ", " .
                $this->vat . ", " .
                $this->priceVat . ", >" .
                $this->price . ", " .
                $this->count . " <br>";
        return $message;
    }

}
