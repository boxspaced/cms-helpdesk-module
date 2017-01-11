<?php
namespace Boxspaced\CmsHelpdeskModule\Model;

use Boxspaced\EntityManager\EntityManager;
use Boxspaced\EntityManager\Collection\Collection;

class HelpdeskTicketRepository
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     * @return HelpdeskTicket
     */
    public function getById($id)
    {
        return $this->entityManager->find(HelpdeskTicket::class, $id);
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return $this->entityManager->findAll(HelpdeskTicket::class);
    }

    /**
     * @param int $offset
     * @param int $showPerPage
     * @return Collection
     */
    public function getAllOpenTickets($offset = null, $showPerPage = null)
    {
        $conditions = $this->entityManager->createConditions();
        $conditions->field('status')->eq(HelpdeskTicket::STATUS_OPEN);

        if (null !== $offset && null !== $showPerPage) {
            $conditions->paging($offset, $showPerPage);
        }

        return $this->entityManager->findAll(HelpdeskTicket::class, $conditions);
    }

    /**
     * @param HelpdeskTicket $entity
     * @return HelpdeskTicketRepository
     */
    public function delete(HelpdeskTicket $entity)
    {
        $this->entityManager->delete($entity);
        return $this;
    }

}
