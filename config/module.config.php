<?php
namespace Boxspaced\CmsHelpdeskModule;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Zend\Router\Http\Segment;
use Boxspaced\CmsCoreModule\Model\RepositoryFactory;
use Boxspaced\CmsAccountModule\Model\User;
use Zend\Permissions\Acl\Acl;

return [
    'helpdesk' => [
        'managers' => [],
        'attachments_directory' => '',
    ],
    'router' => [
        'routes' => [
            // LIFO
            'helpdesk' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/helpdesk[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\HelpdeskController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            // LIFO
        ],
    ],
    'acl' => [
        'resources' => [
            [
                'id' => Controller\HelpdeskController::class,
            ],
        ],
        'roles' => [
            [
                'id' => 'helpdesk-user',
                'parents' => 'authenticated-user',
            ],
            [
                'id' => 'helpdesk-manager',
                'parents' => 'helpdesk-user',
            ],
        ],
        'rules' => [
            [
                'type' => Acl::TYPE_ALLOW,
                'roles' => 'helpdesk-user',
                'resources' => Controller\HelpdeskController::class,
                'privileges' => [
                    'index',
                    'create-ticket',
                    'view-ticket',
                    'view-attachment',
                ],
            ],
            [
                'type' => Acl::TYPE_ALLOW,
                'roles' => 'helpdesk-manager',
                'resources' => Controller\HelpdeskController::class,
                'privileges' => 'resolve-ticket',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\HelpdeskService::class => Service\HelpdeskServiceFactory::class,
            Model\HelpdeskTicketRepository::class => RepositoryFactory::class,
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\HelpdeskController::class => Controller\HelpdeskControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'entity_manager' => [
        'types' => [
            Model\HelpdeskTicket::class => [
                'mapper' => [
                    'params' => [
                        'table' => 'helpdesk_ticket',
                        'columns' => [
                            'user' => 'user_id',
                        ],
                    ],
                ],
                'entity' => [
                    'fields' => [
                        'id' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'status' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'subject' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'issue' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'created_at' => [
                            'type' => AbstractEntity::TYPE_DATETIME,
                        ],
                        'user' => [
                            'type' => User::class,
                        ],
                    ],
                    'one_to_many' => [
                        'comments' => [
                            'type' => Model\HelpdeskTicketComment::class,
                        ],
                        'attachments' => [
                            'type' => Model\HelpdeskTicketAttachment::class,
                        ],
                    ],
                ],
            ],
            Model\HelpdeskTicketComment::class => [
                'mapper' => [
                    'params' => [
                        'table' => 'helpdesk_ticket_comment',
                        'columns' => [
                            'ticket' => 'ticket_id',
                            'user' => 'user_id',
                        ],
                    ],
                ],
                'entity' => [
                    'fields' => [
                        'id' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'comment' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'created_at' => [
                            'type' => AbstractEntity::TYPE_DATETIME,
                        ],
                        'ticket' => [
                            'type' => Model\HelpdeskTicket::class,
                        ],
                        'user' => [
                            'type' => User::class,
                        ],
                    ],
                ],
            ],
            Model\HelpdeskTicketAttachment::class => [
                'mapper' => [
                    'params' => [
                        'table' => 'helpdesk_ticket_attachment',
                        'columns' => [
                            'ticket' => 'ticket_id',
                            'user' => 'user_id',
                        ],
                    ],
                ],
                'entity' => [
                    'fields' => [
                        'id' => [
                            'type' => AbstractEntity::TYPE_INT,
                        ],
                        'file_name' => [
                            'type' => AbstractEntity::TYPE_STRING,
                        ],
                        'created_at' => [
                            'type' => AbstractEntity::TYPE_DATETIME,
                        ],
                        'ticket' => [
                            'type' => Model\HelpdeskTicket::class,
                        ],
                        'user' => [
                            'type' => User::class,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
