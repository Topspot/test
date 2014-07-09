<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Clients\Controller\Index' => 'Clients\Controller\IndexController',
            'Clients\Controller\Link' => 'Clients\Controller\LinkController',
            'Clients\Controller\Lead' => 'Clients\Controller\LeadController',
            'Clients\Controller\Transcript' => 'Clients\Controller\TranscriptController',
            'Clients\Controller\Book' => 'Clients\Controller\BookController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'clients' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/clients[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Clients\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'link' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/link[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Clients\Controller\Link',
                        'action' => 'index',
                    ),
                ),
            ),
            'lead' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/lead[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Clients\Controller\Lead',
                        'action' => 'index',
                    ),
                ),
            ),
            'transcript' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/transcript[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Clients\Controller\Transcript',
                        'action' => 'index',
                    ),
                ),
            ),
            'book' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/book[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Clients\Controller\Book',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'clients' => __DIR__ . '/../view',
        ),
    ),
    'module_config' => array(
        'upload_location' => __DIR__ . '/../data/uploads',
    ),
    'service_manager' => array(
		// added for Authentication and Authorization. Without this each time we have to create a new instance.
		// This code should be moved to a module to allow Doctrine to overwrite it
		'aliases' => array( // !!! aliases not alias
			'Zend\Authentication\AuthenticationService' => 'my_auth_service',
		),
		'invokables' => array(
			'my_auth_service' => 'Zend\Authentication\AuthenticationService',
		),
    ),
);
