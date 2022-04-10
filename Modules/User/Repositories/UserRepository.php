<?php
namespace Modules\User\Repositories;

use Modules\Help\Repositories\BaseRepository;

interface UserRepository extends BaseRepository {

    public function getMany($params);

    public function getOne($criteria, $params);

    public function create($data);

    public function updateBy($criteria, $data, $params);

    public function deleteBy($criteria, $params);

}
