<?php
namespace App\MyClasses;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart 
{
    public function __construct(
        private SessionInterface $session
    )
    {
        
    }

}