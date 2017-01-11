<?php
namespace Boxspaced\CmsHelpdeskModule\Service;

use DateTime;
use Boxspaced\CmsHelpdeskModule\Model\HelpdeskTicketComment as HelpdeskTicketCommentEntity;

class HelpdeskTicketComment
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
    public $comment;

    /**
     * @param HelpdeskTicketCommentEntity $entity
     * @return HelpdeskTicketComment
     */
    public static function createFromEntity(HelpdeskTicketCommentEntity $entity)
    {
        $comment = new static();

        $comment->comment = $entity->getComment();
        $comment->username = $entity->getUser()->getUsername();
        $comment->createdAt = $entity->getCreatedAt();

        return $comment;
    }

}
