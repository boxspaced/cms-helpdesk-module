<?php
namespace Boxspaced\CmsHelpdeskModule\Service;

use DateTime;
use Boxspaced\CmsHelpdeskModule\Model\HelpdeskTicket as HelpdeskTicketEntity;

class HelpdeskTicket
{

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var string
     */
    public $subject;

    /**
     *
     * @var string
     */
    public $issue;

    /**
     *
     * @var string
     */
    public $status;

    /**
     *
     * @var string
     */
    public $username;

    /**
     *
     * @var DateTime
     */
    public $createdAt;

    /**
     *
     * @var HelpdeskTicketComment[]
     */
    public $comments = [];

    /**
     *
     * @var HelpdeskTicketAttachment[]
     */
    public $attachments = [];

    /**
     * @param HelpdeskTicketEntity $entity
     * @return HelpdeskTicket
     */
    public static function createFromEntity(HelpdeskTicketEntity $entity)
    {
        $ticket = new static();

        $ticket->id = $entity->getId();
        $ticket->subject = $entity->getSubject();
        $ticket->issue = $entity->getIssue();
        $ticket->status = $entity->getStatus();
        $ticket->username = $entity->getUser()->getUsername();
        $ticket->createdAt = $entity->getCreatedAt();

        foreach ($entity->getComments() as $comment) {
            $ticket->comments[] = HelpdeskTicketComment::createFromEntity($comment);
        }

        foreach ($entity->getAttachments() as $attachment) {
            $ticket->attachments[] = HelpdeskTicketAttachment::createFromEntity($attachment);
        }

        return $ticket;
    }

}
