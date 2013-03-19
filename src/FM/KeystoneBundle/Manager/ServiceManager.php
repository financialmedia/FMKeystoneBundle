<?php

namespace FM\KeystoneBundle\Manager;

use Doctrine\ORM\EntityManager;
use FM\KeystoneBundle\Entity\Service;
use FM\KeystoneBundle\Entity\Endpoint;

class ServiceManager
{
    protected $entityManager;
    protected $repository;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository('FMKeystoneBundle:Service');
    }

    /**
     * Returns service with given criteria
     *
     * @param  array $criteria
     * @return array<Service>
     */
    public function findServiceBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Returns service with given id
     *
     * @param  integer $id
     * @return Service
     */
    public function findServiceById($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Returns all services
     *
     * @return array<Service>
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * Creates a new service
     *
     * @return Service
     * @throws RuntimeException When trying to insert duplicate service
     */
    public function createService($type, $name)
    {
        $service = $this->repository->findOneBy(array('type' => $type, 'name' => $name));
        if ($service !== null) {
            throw new \RuntimeException(
                sprintf('Service of type "%s" named "%s" already exists', $type, $name)
            );
        }

        $service = new Service;
        $service->setType($type);
        $service->setName($name);

        return $service;
    }

    public function addEndpoint(Service $service, $publicUrl, $adminUrl)
    {
        $endpoint = new Endpoint;
        $endpoint->setPublicUrl($publicUrl);
        $endpoint->setAdminUrl($adminUrl);

        $service->addEndpoint($endpoint);
        $endpoint->setService($service);

        $this->updateService($service);

        return $service;
    }

    /**
     * Updates a service.
     *
     * @param Service $service
     * @param Boolean $andFlush Whether to flush the changes (default true)
     */
    public function updateService(Service $service, $andFlush = true)
    {
        $this->entityManager->persist($service);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Removes a service.
     *
     * @param Service $service
     * @param Boolean $andFlush Whether to flush the changes (default true)
     */
    public function removeService(Service $service, $andFlush = true)
    {
        $this->entityManager->remove($service);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }
}
