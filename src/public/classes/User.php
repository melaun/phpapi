<?php

require '../vendor/autoload.php';

class User {

    public $first_name = 'default';
    public $last_name = 'default';
    public $telephone = 'default';
    public $adress = 'default';
    public $ic = 'default';
    public $dic = 'default';
    public $email = 'default';

    public function print_me() {
        $message = 'User: ' . $this->first_name .
        " <br>" . $this->last_name .
        " <br>" . $this->email .
        " <br>" . $this->adress .
        " <br>" . $this->telephone .
        " <br>" . $this->dic .
        " <br>" . $this->email."<br>"
                ."-------------------------------------<br>";
        return $message;
    }

}
