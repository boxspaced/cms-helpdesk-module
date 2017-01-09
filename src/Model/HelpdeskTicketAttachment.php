<?php
namespace Helpdesk\Model;

use DateTime;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Account\Model\User;

class HelpdeskTicketAttachment extends AbstractEntity
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
     * @return HelpdeskTicketAttachment
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
     * @return HelpdeskTicketAttachment
     */
    public function setTicket(HelpdeskTicket $ticket)
    {
        $this->set('ticket', $ticket);
		return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->get('file_name');
    }

    /**
     * @param string $fileName
     * @return HelpdeskTicketAttachment
     */
    public function setFileName($fileName)
    {
        $this->set('file_name', $fileName);
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
     * @return HelpdeskTicketAttachment
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
     * @return HelpdeskTicketAttachment
     */
    public function setCreatedAt(DateTime $createdAt = null)
    {
        $this->set('created_at', $createdAt);
		return $this;
    }

}
