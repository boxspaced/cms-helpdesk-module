<?php
namespace Boxspaced\CmsHelpdeskModule;

use Boxspaced\CmsHelpdeskModule\Controller\HelpdeskController;
use Boxspaced\CmsCoreModule\Listener\ForceHttpsListener;
use Zend\Mvc\MvcEvent;

class Module
{

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @param MvcEvent $event
     * @return void
     */
    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();

        $sharedEventManager->attach(
            HelpdeskController::class,
            MvcEvent::EVENT_DISPATCH,
            new ForceHttpsListener(),
            100
        );
    }

}
