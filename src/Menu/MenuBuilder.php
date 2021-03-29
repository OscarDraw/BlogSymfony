<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MenuBuilder
{
    private $factory;
    private $security;

    /**
     * Add any other dependency you need...
     */
    public function __construct(FactoryInterface $factory, TokenStorageInterface $security)
    {
        $this->factory = $factory;
        $this->security = $security;
    }

    public function createMainMenu(array $options,): ItemInterface
    {
        $user = $this->security->getToken()->getUser();

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'navbar-nav mr-auto');

        $menu->addChild('Home', ['route' => 'index'])
            ->setLinkAttribute('class', 'nav-link')
            ->setAttribute('class', 'nav-item');

        $menu->addChild('Blog', ['route' => 'show_entries'])
            ->setLinkAttribute('class', 'nav-link')
            ->setAttribute('class', 'nav-item');

        $menu->addChild('Contact', ['route' => 'contact_message_new'])
            ->setLinkAttribute('class', 'nav-link')
            ->setAttribute('class', 'nav-item');


        if (isset($user) && !is_string($user)) {
            $menu->addChild('Welcome '.$user->getFirstName(), ['route' => "index"]);
            $menu['Welcome '.$user->getFirstName()]
                ->setLinkAttribute('class', 'nav-link')
                ->setAttribute('class', 'nav-item auth-btn user-span');

            $menu->addChild('Logout', ['route' => "app_logout"]);
            $menu['Logout']
                ->setLinkAttribute('class', 'nav-link')
                ->setAttribute('class', 'nav-item auth-btn ');
        } else {
            $menu->addChild('Login', ['route' => "app_login"]);
            $menu['Login']
                ->setLinkAttribute('class', 'nav-link')
                ->setAttribute('class', 'nav-item auth-btn');
        }

        return $menu;
    }
}