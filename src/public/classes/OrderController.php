<?php

require '../vendor/autoload.php';

/**
 * Controller pro objednavky
 */
class OrderController {

    // obory ktere spadaji pod cash
    private $cashFields = array("1", "2", "5", "4.3", "4.2");
    private $itemsCash = array();
    private $itemsVO = array();
    private $ci;

    public function __construct(Slim\Container $ci) {
        $this->ci = $ci;
    }

    /**
     * hlavni funkce
     * @param type $request
     * @param type $response
     * @param type $args
     * @return type
     */
    public function makeOrder($request, $response, $args) {
        $counterVO = new Counter('../upload/orders/vo/counter');
        $counterCash = new Counter('../upload/orders/cash/counter');
        $this->ci->get('logger')->info("OrderController: /order - Pozadavek na objednavku");
        $parsedBody = $request->getBody();
        $decodeBody = json_decode($parsedBody, true);
        if (isset($decodeBody['user']) && isset($decodeBody['items'])) {
            $user = $this->makeUser($decodeBody);
            $transportDate = $decodeBody['user']['termin'];
            $transport = $decodeBody['user']['zavest'];
            //separate items for cash a vo
            //nastavi promene items Vo a itemsCash
            $this->setItems($decodeBody);
            // pokud jsou nejake itemy z VO
            if ($this->itemsVO) {
                //vo objednavka
                $this->ci->get('logger')->info("Objednavka vekoobchod");
                $orderVO = new Order($counterVO->getCounter());
                $orderVO->setItems($this->itemsVO);
                $orderVO->setUser($user);
                $orderVO->setTransport($transport);
                $orderVO->setTransportDate($transportDate);
                $counterVO->incrementCounter();
                $orderVO->saveOrderLocal('../upload/orders/vo/');
                $this->ci->get('logger')->info("Vytvorena objednavka cislo " . $orderVO->getNumberOrder());
            }
            //pokud jsou nejake itemy z cash
            if ($this->itemsCash) {
                //cash objednavka
                $orderCash = new Order($counterCash->getCounter());
                $orderCash->setItems($this->itemsCash);
                $orderCash->setTransport($transport);
                $orderCash->setTransportDate($transportDate);
                $counterCash->incrementCounter();
                $orderCash->saveOrderLocal('../upload/orders/cash/');
                $this->ci->get('logger')->info("Vytvorena objednavka cislo " . $orderCash->getNumberOrder());
            }

            $data = array(
                'status' => 'success', 'data' =>
                "Objednavka byla uspesne zpracovana");

            return $response->withJson($data);
        } else {
            $data = array('status' => 'denied', 'data' => 'zadna data nebyla poslana');
            return $response->withJson($data);
        }
    }

    /**
     * 
     * @param type $body
     * @return type
     */
    private function makeUser($body) {
        $this->user = new User;

        if (isset($body['user']['jmeno'])) {
            $this->user->first_name = filter_var($body['user']['jmeno'], FILTER_SANITIZE_STRING);
        }
        if (isset($body['user']['prijmeni'])) {
            $this->user->last_name = filter_var($body['user']['prijmeni'], FILTER_SANITIZE_STRING);
        }
        if (isset($body['user']['email'])) {
            $this->user->email = filter_var($body['user']['email'], FILTER_SANITIZE_EMAIL);
        }
        if (isset($body['user']['adresa'])) {
            $this->user->adress = filter_var($body['user']['adresa'], FILTER_SANITIZE_STRING);
        }
        if (isset($body['user']['tel'])) {
            $this->user->telephone = filter_var($body['user']['tel'], FILTER_SANITIZE_NUMBER_INT);
        }
        if (isset($body['user']['dic'])) {
            $this->user->dic = filter_var($body['user']['dic'], FILTER_SANITIZE_STRING);
        }
        return $this->user;
    }

    /**
     * 
     * @param type $body
     */
    private function setItems($body) {
        $this->itemsCash = array();
        $this->itemsVO = array();
        foreach ($body['items'] as $item) {
            $newItem = new Item();
            if (isset($item['cislo'])) {
                $newItem->number = filter_var($item['cislo'], FILTER_SANITIZE_STRING);
            }
            if (isset($item['nazev'])) {
                $newItem->name = filter_var($item['nazev'], FILTER_SANITIZE_STRING);
            }
            if (isset($item['DPH'])) {
                $newItem->vat = filter_var($item['DPH'], FILTER_SANITIZE_NUMBER_INT);
            }
            if (isset($item['ean'])) {
                $newItem->ean = filter_var($item['ean'], FILTER_SANITIZE_NUMBER_INT);
            }
            if (isset($item['bDPH'])) {
                $newItem->price = filter_var($item['bDPH'], FILTER_SANITIZE_STRING);
            }
            if (isset($item['sDPH'])) {
                $newItem->priceVat = filter_var($item['sDPH'], FILTER_SANITIZE_STRING);
            }
            if (isset($item['pocet'])) {
                $newItem->count = filter_var($item['pocet'], FILTER_SANITIZE_NUMBER_INT);
            }
            if (isset($item['cisloOboru'])) {
                $newItem->fieldNumber = filter_var($item['cisloOboru'], FILTER_SANITIZE_STRING);
            }
            // rozendam itemy dle oboru do poli
            if (!$this->isVO($newItem->fieldNumber)) {
                array_push($this->itemsCash, $newItem);
            } else {
                array_push($this->itemsVO, $newItem);
            }
        }
    }

    /**
     * Testuje zda je item z VO nebo CASH
     * pokud VO vraci TRUE - nutny pole oboru s CASH
     * @param type $field - tostovany obor
     * @return boolean
     */
    private function isVO($field) {
        foreach ($this->cashFields as $cashField) {
            $lenS = strlen($cashField);
            if (substr($field, 0, $lenS) === $cashField) {
                return false;
            }
        }
        return true;
    }

}
