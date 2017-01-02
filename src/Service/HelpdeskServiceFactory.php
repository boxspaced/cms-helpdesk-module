<?php
namespace Helpdesk\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Log\Logger;
use Zend\Authentication\AuthenticationService;
use Boxspaced\EntityManager\EntityManager;
use Helpdesk\Model;
use Zend\Mail\Transport\Smtp;
use Account\Model\UserRepository;
use Core\Model\EntityFactory;

class HelpdeskServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new HelpdeskService(
            $container->get(Logger::class),
            $container->get('config'),
            $container->get(AuthenticationService::class),
            $container->get(Smtp::class),
            $container->get(EntityManager::class),
            $container->get(UserRepository::class),
            $container->get(Model\HelpdeskTicketRepository::class),
            $container->get(EntityFactory::class)
        );
    }

}
