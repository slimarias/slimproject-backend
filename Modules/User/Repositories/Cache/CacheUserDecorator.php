<?php
namespace Modules\User\Repositories\Cache;

use Modules\Help\Repositories\Cache\BaseCacheDecorator;
use Modules\User\Repositories\UserRepository;

class CacheUserDecorator extends BaseCacheDecorator implements UserRepository{

    /**
     * User Repository
     *
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $user)
    {
      parent::__construct();
      $this->entityName = 'user.users';
      $this->repository = $user;
    }

    /**
     * List or resources
     *
     * @return collection
     */
    public function getMany($params)
    {
      return $this->remember(function () use ($params) {
        return $this->repository->getMany($params);
      });
    }

    /**
     * find a resource by id or slug
     *
     * @return object
     */
    public function getOne($criteria, $params = false)
    {
      return $this->remember(function () use ($criteria, $params) {
        return $this->repository->getOne($criteria, $params);
      });
    }

    /**
     * create a resource
     *
     * @return mixed
     */
    public function create($data)
    {
      $this->clearCache();

      return $this->repository->create($data);
    }

    /**
     * update a resource
     *
     * @return mixed
     */
    public function updateBy($criteria, $data, $params)
    {
      $this->clearCache();

      return $this->repository->updateBy($criteria, $data, $params);
    }

    /**
     * destroy a resource
     *
     * @return mixed
     */
    public function deleteBy($criteria, $params)
    {
      $this->clearCache();

      return $this->repository->deleteBy($criteria, $params);
    }
}
