<?php
namespace Boxspaced\CmsHelpdeskModule\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Boxspaced\CmsHelpdeskModule\Controller\HelpdeskController;
use Boxspaced\CmsHelpdeskModule\Service\HelpdeskService;
use Boxspaced\CmsAccountModule\Service\AccountService;
use Zend\Log\Logger;

class HelpdeskControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new HelpdeskController(
            $container->get(HelpdeskService::class),
            $container->get(AccountService::class),
            $container->get(Logger::class),
            $container->get('config')
        );
    }

}
