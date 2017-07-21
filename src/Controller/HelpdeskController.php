<?php
namespace Boxspaced\CmsHelpdeskModule\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Boxspaced\CmsHelpdeskModule\Service;
use Zend\Paginator;
use Boxspaced\CmsHelpdeskModule\Form;
use Boxspaced\CmsAccountModule\Service\AccountService;
use Zend\EventManager\EventManagerInterface;

class HelpdeskController extends AbstractActionController
{

    /**
     * @var Service\HelpdeskService
     */
    protected $helpdeskService;

    /**
     * @var AccountService
     */
    protected $accountService;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * @param Service\HelpdeskService $helpdeskService
     * @param AccountService $accountService
     * @param Logger $logger
     * @param array $config
     */
    public function __construct(
        Service\HelpdeskService $helpdeskService,
        AccountService $accountService,
        Logger $logger,
        array $config
    )
    {
        $this->helpdeskService = $helpdeskService;
        $this->accountService = $accountService;
        $this->logger = $logger;
        $this->config = $config;

        $this->view = new ViewModel();
    }

    /**
     * @param EventManagerInterface $events
     * @return void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $controller = $this;
        $events->attach('dispatch', function ($e) use ($controller) {
            $controller->layout('layout/admin');
        }, 100);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $adminNavigation = $this->adminNavigationWidget();
        if (null !== $adminNavigation) {
            $this->view->addChild($adminNavigation, 'adminNavigation');
        }

        $adapter = new Paginator\Adapter\Callback(
            function ($offset, $itemCountPerPage) {
                return $this->helpdeskService->getOpenTickets($offset, $itemCountPerPage);
            },
            function () {
                return $this->helpdeskService->countOpenTickets();
            }
        );

        $paginator = new Paginator\Paginator($adapter);
        $paginator->setCurrentPageNumber($this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage($this->config['core']['admin_show_per_page']);
        $this->view->paginator = $paginator;

        return $this->view;
    }

    /**
     * @return void
     */
    public function createTicketAction()
    {
        $adminNavigation = $this->adminNavigationWidget();
        if (null !== $adminNavigation) {
            $this->view->addChild($adminNavigation, 'adminNavigation');
        }

        $form = new Form\HelpdeskTicketForm(
            $this->config['helpdesk']['attachments_directory']
        );

        $this->view->form = $form;

        if (!$this->getRequest()->isPost()) {
            return $this->view;
        }

        $form->setData(array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        ));

        if (!$form->isValid()) {

            $this->flashMessenger()->addErrorMessage('Validation failed.');
            return $this->view;
        }

        $values = $form->getData();

        $ticket = new Service\HelpdeskTicket();
        $ticket->subject = $values['subject'];
        $ticket->issue = $values['issue'];

        $this->helpdeskService->createNewTicket($ticket, basename($values['attachment']['tmp_name']));

        $this->flashMessenger()->addSuccessMessage('Create ticket successful.');

        return $this->redirect()->toRoute('helpdesk');
    }

    /**
     * @return void
     */
    public function viewTicketAction()
    {
        $id = $this->params()->fromRoute('id');
        $ticket = $this->helpdeskService->getTicket($id);

        $this->view->ticket = $ticket;

        $canResolve = $this->accountService->isAllowed(get_class(), 'resolve-ticket');
        $this->view->canResolve = $canResolve;

        $adminNavigation = $this->adminNavigationWidget();
        if (null !== $adminNavigation) {
            $this->view->addChild($adminNavigation, 'adminNavigation');
        }

        $form = new Form\HelpdeskCommentForm(
            $this->config['helpdesk']['attachments_directory']
        );

        $this->view->form = $form;

        if (!$this->getRequest()->isPost()) {
            return $this->view;
        }

        $form->setData(array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        ));

        if (!$form->isValid()) {

            $this->flashMessenger()->addErrorMessage('Validation failed.');
            return $this->view;
        }

        $values = $form->getData();

        if (null !== $values['resolve'] && $canResolve) {

            $this->helpdeskService->resolveTicket(
                $id,
                $values['comment'],
                basename($values['attachment']['tmp_name'])
            );

            $message = 'Ticket resolved.';

        } else {

            $this->helpdeskService->addCommentToTicket(
                $id,
                $values['comment'],
                basename($values['attachment']['tmp_name'])
            );

            $message = 'Comment added to ticket.';
        }

        $this->flashMessenger()->addSuccessMessage($message);

        return $this->redirect()->toRoute('helpdesk');
    }

    /**
     * @return void
     */
    public function viewAttachmentAction()
    {
        $filename = basename($this->params()->fromQuery('fileName'));
        $filePath = $this->config['helpdesk']['attachments_directory'] . DIRECTORY_SEPARATOR . basename($filename);

        $mime = image_type_to_mime_type(exif_imagetype($filePath));

        header('Content-Type: ' . $mime);
        readfile($filePath);
        exit;
    }

}
