<?php
namespace Helpdesk\Service;

use DateTime;
use Helpdesk\Model\HelpdeskTicketAttachment as HelpdeskTicketAttachmentEntity;

class HelpdeskTicketAttachment
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
    public $username;

    /**
     *
     * @var DateTime
     */
    public $createdAt;

    /**
     *
     * @var string
     */
    public $fileName;

    /**
     * @param HelpdeskTicketAttachmentEntity $entity
     * @return HelpdeskTicketAttachment
     */
    public static function createFromEntity(HelpdeskTicketAttachmentEntity $entity)
    {
        $attachment = new static();

        $attachment->fileName = $entity->getFileName();
        $attachment->username = $entity->getUser()->getUsername();
        $attachment->createdAt = $entity->getCreatedAt();

        return $attachment;
    }

}
