<?php
namespace Boxspaced\CmsHelpdeskModule\Model;

use DateTime;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Collection\Collection;
use Boxspaced\CmsAccountModule\Model\User;

class HelpdeskTicket extends AbstractEntity
{

    const STATUS_OPEN = 'OPEN';
    const STATUS_RESOLVED = 'RESOLVED';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->get('id');
    }

    /**
     * @param int $id
     * @return HelpdeskTicket
     */
    public function setId($id)
    {
        $this->set('id', $id);
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->get('status');
    }

    /**
     * @param string $status
     * @return HelpdeskTicket
     */
    public function setStatus($status)
    {
        $this->set('status', $status);
		return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->get('subject');
    }

    /**
     * @param string $subject
     * @return HelpdeskTicket
     */
    public function setSubject($subject)
    {
        $this->set('subject', $subject);
		return $this;
    }

    /**
     * @return string
     */
    public function getIssue()
    {
        return $this->get('issue');
    }

    /**
     * @param string $issue
     * @return HelpdeskTicket
     */
    public function setIssue($issue)
    {
        $this->set('issue', $issue);
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
     * @return HelpdeskTicket
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
     * @return HelpdeskTicket
     */
    public function setCreatedAt(DateTime $createdAt = null)
    {
        $this->set('created_at', $createdAt);
		return $this;
    }

    /**
     * @return Collection
     */
    public function getComments()
    {
        return $this->get('comments');
    }

    /**
     * @param HelpdeskTicketComment $helpdeskComment
     * @return HelpdeskTicket
     */
    public function addComment(HelpdeskTicketComment $helpdeskComment)
    {
        $helpdeskComment->setTicket($this);
        $this->getComments()->add($helpdeskComment);
		return $this;
    }

    /**
     * @param HelpdeskTicketComment $helpdeskComment
     * @return HelpdeskTicket
     */
    public function deleteComment(HelpdeskTicketComment $helpdeskComment)
    {
        $this->getComments()->delete($helpdeskComment);
		return $this;
    }

    /**
     * @return HelpdeskTicket
     */
    public function deleteAllComments()
    {
        foreach ($this->getComments() as $comment) {
            $this->deleteComment($comment);
        }
		return $this;
    }

    /**
     * @return Collection
     */
    public function getAttachments()
    {
        return $this->get('attachments');
    }

    /**
     * @param HelpdeskTicketAttachment $attachment
     * @return HelpdeskTicket
     */
    public function addAttachment(HelpdeskTicketAttachment $attachment)
    {
        $attachment->setTicket($this);
        $this->getAttachments()->add($attachment);
		return $this;
    }

    /**
     * @param HelpdeskTicketAttachment $attachment
     * @return HelpdeskTicket
     */
    public function deleteAttachment(HelpdeskTicketAttachment $attachment)
    {
        $this->getAttachments()->delete($attachment);
		return $this;
    }

    /**
     * @return HelpdeskTicket
     */
    public function deleteAllAttachments()
    {
        foreach ($this->getAttachments() as $attachment) {
            $this->deleteAttachment($attachment);
        }
		return $this;
    }

}
