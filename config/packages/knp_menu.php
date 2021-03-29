<?php
    $container->loadFromExtension('knp_menu', [
        // use 'twig' => false to disable the Twig extension and the TwigRenderer
        'twig' => [
            'template' => 'KnpMenuBundle::menu.html.twig'
        ],
        // if true, enabled the helper for PHP templates
        'templating' => false,
        // the renderer to use, list is also available by default
        'default_renderer' => 'twig',
    ]);