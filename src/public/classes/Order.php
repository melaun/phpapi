<?php

/**
 *  UKOLY
 *  Roydelit do objednavek podle oboru
 */
require '../vendor/autoload.php';

class Order {

    private $user = null;
    private $transportDate = 'neuvedeno';
    private $transport = 'neuvedeno';
    private $items;

    /**
     * cislo objednavky
     * @var type 
     */
    private $numberOrder = 0;

    public function __construct($numberOrder) {
        $this->numberOrder = $numberOrder;
    }
    
    public function getNumberOrder(){
        return $this->numberOrder;
    }

    public function setItems($items) {
        $this->items = $items;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setTransport($transport) {
        $this->transport = $transport;
    }

    public function setTransportDate($transportDate) {
        $this->transportDate = $transportDate;
    }

    /**
     * vytiskne objednavku
     * @return string
     */
    public function print_me() {
        $mesage = '';
        $mesage .= "Datum závozu: " . $this->transportDate . "<br>";
        $mesage .= "Závoz: " . $this->transport . "<br>";
        $mesage .= "--------------------------------------------<br>";
        $mesage .= $this->user->print_me();
        if (!empty($this->items)) {
            $mesage .= 'Items:<br>';
            foreach ($this->items as $i) {
                $mesage .= $i->print_me();
            }
        } else {
            $mesage .= "Objednávka neobsahuje položky.<br>";
        }
        return $mesage;
    }

    /**
     * ulozi email na mistni uloziste
     * @param type $path cesta na umisteni (slozku)
     * @return boolean
     */
    public function saveOrderLocal($path) {
        $myfile = fopen($path . $this->numberOrder . '.txt', "w") or die("Unable to open file!");
        if (!$myfile) {
            return false;
        }
        $txt = '';
        foreach ($this->items as $item) {
            $txt .= $item->name;
        }
        fwrite($myfile, $txt);
        
        fclose($myfile);
        return true;
    }

    /**
     * na uvedeny eamil odesle objednavku 
     * @param type $email
     * @return boolean
     */
    public function sendOnEmail($email) {

        $mail = new SimpleMail();

        $mail->setTo($email, '')
                ->setSubject('www.korunapb.cz - objednávka - V3 ')
                ->setFrom('no-reply@korunapb.cz', 'korunapb.cz')
                ->addMailHeader('Reply-To', 'no-reply@korunapb.cz', 'korunapb.cz')
                ->addMailHeader('Cc', $email)
                ->addGenericHeader('X-Mailer', 'PHP/' . phpversion())
                ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
                ->setMessage($this->prepareMsg())
                ->setWrap(100);
        $send = $mail->send();
        if ($send) {
            //$app->response->setStatus(200);
            return true;
        } else {
            //$app->response->setStatus(404);
            return false;
            //echo '{"error":{"text":' . 'Email se nepodaĹ™ilo odeslat' . '}}';
        }
    }

    /**
     * vytvori sablonu pro email, vraci komplet msg v mailu
     * @return string
     */
    private function prepareMsg() {
        $jmeno = $this->user->first_name;
        $prijmeni = $this->user->last_name;
        $tel = $this->user->telephone;
        $adresa = $this->user->adress;
        $zavest = $this->transport;
        $poznamka = "";
        $termin = $this->transportDate;
        $email = $this->user->email;
        if ($zavest) {
            $dovest = 'Zboží­ mi zavezte na adresu: <strong>' . $adresa . '</strong>' .
                    'v <strong>' . $termin . '</strong><br>' .
                    '';
        } else {
            $dovest = 'Pro zboží si přijedu na Korunu ' .
                    'v <strong>' . $termin . '</strong><br>';
        }
        $head = '
    <html>
        <head>
            <style>
            table{border-collapse:collapse;}
            table, th, td {border: 1px solid black;}
            th {text-align: center; }
            td {height: 30px;vertical-align: bottom;}
            td { padding: 10px;}
            table, td, th {border: 1px solid black;}
            th {background-color: black;color: white;}
            </style>
        </head>
        <body>' .
                'Jméno: <strong>' . $jmeno . ' ' . $prijmeni . '</strong><br>' .
                'Email: <strong>' . $email . '</strong><br>' .
                'Tel.: <strong>' . $tel . '</strong><br>'
                . $dovest
                . '<p><i>PoznĂˇmka: ' . $poznamka . '</i></p><br>';
        $table = '<table>'
                . '<thead>'
                . '<tr>'
                . '<th style="border: 1px solid black;">ÄŤĂ­slo</th>'
                . '<th style="border: 1px solid black;">nĂˇzev</th>'
                . '<th style="border: 1px solid black;">pocet</th>'
                . '<th style="border: 1px solid black;">bDPH</th>'
                . '<th style="border: 1px solid black;">sDPH</th>'
                . '</thead>'
                . '</tr>'
                . '<tbody>';
        foreach ($this->items as $item) {
            $table = $table . '<tr>'
                    . '<td >' . $item->number . '</td>'
                    . '<td >' . $item->name . '</td>'
                    . '<td >' . $item->count . '</td>'
                    . '<td >' . $item->price . '</td>'
                    . '<td >' . $item->priceVat . '</td>'
                    . '</tr>';
        }
        $table = $table . '</tbody>'
                . '</table>'
                . '</body>';
        $msg = $head . $table;
        return $msg;
    }

}
