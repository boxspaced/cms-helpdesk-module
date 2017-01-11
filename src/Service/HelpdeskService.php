<?php
namespace Boxspaced\CmsHelpdeskModule\Service;

use Exception as PhpException;
use DateTime;
use Zend\Log\Logger;
use Zend\Authentication\AuthenticationService;
use Boxspaced\EntityManager\EntityManager;
use Boxspaced\CmsHelpdeskModule\Model;
use Zend\Db\Sql;
use Zend\Uri\Http as HttpUri;
use Boxspaced\CmsHelpdeskModule\Exception;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Message;
use Boxspaced\CmsAccountModule\Model\UserRepository;
use Boxspaced\CmsCoreModule\Model\EntityFactory;
use Boxspaced\CmsAccountModule\Model\User;

class HelpdeskService
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var TransportInterface
     */
    protected $mailTransport;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var Model\HelpdeskTicketRepository
     */
    protected $helpdeskTicketRepository;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @param Logger $logger
     * @param array $config
     * @param AuthenticationService $authService
     * @param TransportInterface $mailTransport
     * @param EntityManager $entityManager
     * @param UserRepository $userRepository
     * @param Model\HelpdeskTicketRepository $helpdeskTicketRepository
     * @param EntityFactory $entityFactory
     */
    public function __construct(
        Logger $logger,
        array $config,
        AuthenticationService $authService,
        TransportInterface $mailTransport,
        EntityManager $entityManager,
        UserRepository $userRepository,
        Model\HelpdeskTicketRepository $helpdeskTicketRepository,
        EntityFactory $entityFactory
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->authService = $authService;
        $this->mailTransport = $mailTransport;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->helpdeskTicketRepository = $helpdeskTicketRepository;
        $this->entityFactory = $entityFactory;

        if ($this->authService->hasIdentity()) {
            $identity = $authService->getIdentity();
            $this->user = $userRepository->getById($identity->id);
        }
    }

    /**
     * @param int $id
     * @return HelpdeskTicket
     */
    public function getTicket($id)
    {
        $ticket = $this->helpdeskTicketRepository->getById($id);

        if (null === $ticket) {
            throw new Exception\UnexpectedValueException('Unable to find a ticket with given ID');
        }

        return HelpdeskTicket::createFromEntity($ticket);
    }

    /**
     * @param HelpdeskTicket $ticket
     * @return int
     */
    public function createNewTicket(HelpdeskTicket $ticket, $attachmentFileName = null)
    {
        $ticketEntity = $this->entityFactory->createEntity(Model\HelpdeskTicket::class);
        $ticketEntity->setSubject($ticket->subject);
        $ticketEntity->setIssue($ticket->issue);
        $ticketEntity->setStatus(Model\HelpdeskTicket::STATUS_OPEN);
        $ticketEntity->setCreatedAt(new DateTime());
        $ticketEntity->setUser($this->user);

        if ($attachmentFileName) {

            $attachmentEntity = $this->entityFactory->createEntity(Model\HelpdeskTicketAttachment::class);
            $attachmentEntity->setFileName($attachmentFileName);
            $attachmentEntity->setCreatedAt(new DateTime());
            $attachmentEntity->setUser($this->user);

            $ticketEntity->addAttachment($attachmentEntity);
        }

        $this->entityManager->flush();

        $uri = new HttpUri();
        $uri->setScheme($this->config['core']['has_ssl'] ? 'https' : 'http');
        $uri->setHost($this->config['core']['hostname']);
        $uri->setPath(sprintf('/helpdesk/view-ticket/%d', $ticketEntity->getId()));

        $mail = new Message();
        $mail->setFrom($this->config['core']['email']);
        foreach ($this->config['helpdesk']['managers'] as $recipient) {
            $mail->addTo($recipient);
        }
        $mail->setSubject('Helpdesk ticket: ' . $ticket->subject);
        $mail->setBody($ticket->issue . PHP_EOL . PHP_EOL . 'Have a look (please don\'t reply to this email): ' . $uri->toString());

        try {
            $this->mailTransport->send($mail);
        } catch (PhpException $e) {
            $this->logger->err($e);
        }

        return (int) $ticketEntity->getId();
    }

    /**
     * @return HelpdeskTicket[]
     */
    public function getOpenTickets($offset = null, $showPerPage = null)
    {
        $tickets = [];

        foreach ($this->helpdeskTicketRepository->getAllOpenTickets($offset, $showPerPage) as $ticket) {
            $tickets[] = HelpdeskTicket::createFromEntity($ticket);
        }

        return $tickets;
    }

    /**
     * @todo need to find a way of using SQL_CALC_FOUND_ROWS, in mappers and returned to repository
     * @return int
     */
    public function countOpenTickets()
    {
        $sql = new Sql\Sql($this->entityManager->getDb());

        $select = $sql->select();
        $select->columns([
            'count' => new Sql\Expression('COUNT(*)'),
        ]);
        $select->from('helpdesk_ticket');
        $select->where([
            'status = ?' => Model\HelpdeskTicket::STATUS_OPEN,
        ]);

        $stmt = $sql->prepareStatementForSqlObject($select);

        return (int) $stmt->execute()->getResource()->fetchColumn();
    }

    /**
     * @param int $id
     * @param string $commentText
     * @return void
     */
    public function addCommentToTicket($id, $commentText, $attachmentFileName = null)
    {
        $ticket = $this->helpdeskTicketRepository->getById($id);

        if (null === $ticket) {
            throw new Exception\UnexpectedValueException('Unable to find a ticket with given ID');
        }

        if ($ticket->getStatus() === Model\HelpdeskTicket::STATUS_RESOLVED) {
            $ticket->setStatus(Model\HelpdeskTicket::STATUS_OPEN);
        }

        $comment = $this->entityFactory->createEntity(Model\HelpdeskTicketComment::class);
        $comment->setComment($commentText);
        $comment->setCreatedAt(new DateTime());
        $comment->setUser($this->user);

        $ticket->addComment($comment);

        if ($attachmentFileName) {

            $attachment = $this->entityFactory->createEntity(Model\HelpdeskTicketAttachment::class);
            $attachment->setFileName($attachmentFileName);
            $attachment->setCreatedAt(new DateTime());
            $attachment->setUser($this->user);

            $ticket->addAttachment($attachment);
        }

        $this->entityManager->flush();

        $uri = new HttpUri();
        $uri->setScheme($this->config['core']['has_ssl'] ? 'https' : 'http');
        $uri->setHost($this->config['core']['hostname']);
        $uri->setPath(sprintf('/helpdesk/view-ticket/%d', $ticket->getId()));

        $mail = new Message();
        $mail->setFrom($this->config['core']['email']);
        $mail->setTo($ticket->getUser()->getEmail());
        foreach ($this->config['helpdesk']['managers'] as $recipient) {
            $mail->addTo($recipient);
        }
        $mail->setSubject('Helpdesk comment: ' . $ticket->getSubject());
        $mail->setBody($commentText . PHP_EOL . PHP_EOL . 'Have a look (please don\'t reply to this email): ' . $uri->toString());

        try {
            $this->mailTransport->send($mail);
        } catch (PhpException $e) {
            $this->logger->err($e);
        }
    }

    /**
     * @param int $id
     * @param string $commentText
     * @return void
     */
    public function resolveTicket($id, $commentText, $attachmentFileName = null)
    {
        $ticket = $this->helpdeskTicketRepository->getById($id);

        if (null === $ticket) {
            throw new Exception\UnexpectedValueException('Unable to find a ticket with given ID');
        }

        $comment = $this->entityFactory->createEntity(Model\HelpdeskTicketComment::class);
        $comment->setComment($commentText);
        $comment->setCreatedAt(new DateTime());
        $comment->setUser($this->user);

        $ticket->setStatus(Model\HelpdeskTicket::STATUS_RESOLVED);
        $ticket->addComment($comment);

        if ($attachmentFileName) {

            $attachment = $this->entityFactory->createEntity(Model\HelpdeskTicketAttachment::class);
            $attachment->setFileName($attachmentFileName);
            $attachment->setCreatedAt(new DateTime());
            $attachment->setUser($this->user);

            $ticket->addAttachment($attachment);
        }

        $this->entityManager->flush();

        $uri = new HttpUri();
        $uri->setScheme($this->config['core']['has_ssl'] ? 'https' : 'http');
        $uri->setHost($this->config['core']['hostname']);
        $uri->setPath(sprintf('/helpdesk/view-ticket/%d', $ticket->getId()));

        $mail = new Message();
        $mail->setFrom($this->config['core']['email']);
        $mail->setTo($ticket->getUser()->getEmail());
        $mail->setSubject('Helpdesk resolution: ' . $ticket->getSubject());
        $mail->setBody($commentText . PHP_EOL . PHP_EOL . 'Have a look (please don\'t reply to this email): ' . $uri->toString());

        try {
            $this->mailTransport->send($mail);
        } catch (PhpException $e) {
            $this->logger->err($e);
        }
    }

}
