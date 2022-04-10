<?php
namespace Modules\Shopping\Repositories;

use Modules\Help\Repositories\BaseRepository;

interface ProductRepository extends BaseRepository {

    public function getMany($params);

    public function getOne($criteria, $params);

    public function create($data);

    public function updateBy($criteria, $data, $params);

    public function deleteBy($criteria, $params);

}
