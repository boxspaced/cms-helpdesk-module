<?php
namespace Helpdesk\Model;

use DateTime;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Account\Model\User;

class HelpdeskTicketComment extends AbstractEntity
{

    /**
     * @return int
     */
    public function getId()
    {
        return $this->get('id');
    }

    /**
     * @param int $id
     * @return HelpdeskTicketComment
     */
    public function setId($id)
    {
        $this->set('id', $id);
        return $this;
    }

    /**
     * @return HelpdeskTicket
     */
    public function getTicket()
    {
        return $this->get('ticket');
    }

    /**
     * @param HelpdeskTicket $ticket
     * @return HelpdeskTicketComment
     */
    public function setTicket(HelpdeskTicket $ticket)
    {
        $this->set('ticket', $ticket);
		return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->get('comment');
    }

    /**
     * @param string $comment
     * @return HelpdeskTicketComment
     */
    public function setComment($comment)
    {
        $this->set('comment', $comment);
		return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * @param User $user
     * @return HelpdeskTicketComment
     */
    public function setUser(User $user)
    {
        $this->set('user', $user);
		return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->get('created_at');
    }

    /**
     * @param DateTime $createdAt
     * @return HelpdeskTicketComment
     */
    public function setCreatedAt(DateTime $createdAt = null)
    {
        $this->set('created_at', $createdAt);
		return $this;
    }

}
