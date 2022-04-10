<?php
namespace Modules\Shopping\Repositories\Cache;

use Modules\Help\Repositories\Cache\BaseCacheDecorator;
use Modules\Shopping\Repositories\ProductRepository;

class CacheProductDecorator extends BaseCacheDecorator implements ProductRepository
{

    /**
     * Product Repository
     *
     * @var ProductRepository
     */
    private $repository;

    public function __construct(ProductRepository $product)
    {
      parent::__construct();
      $this->entityName = 'shopping.products';
      $this->repository = $product;
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
