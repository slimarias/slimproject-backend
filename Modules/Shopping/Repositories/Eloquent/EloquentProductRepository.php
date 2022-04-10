<?php

namespace Modules\Shopping\Repositories\Eloquent;

use Modules\Help\Repositories\Eloquent\EloquentBaseRepository;
use Modules\Shopping\Repositories\ProductRepository;

class EloquentProductRepository extends EloquentBaseRepository implements ProductRepository{

    public function getMany($params = false){
        /*== initialize query ==*/
        $query = $this->model->query();
        /*== FIELDS ==*/
        if (isset($params->fields) && count($params->fields))
            $query->select($params->fields);
        //dd($query->toSql());
        /*== REQUEST ==*/
        if (isset($params->onlyQuery) && $params->onlyQuery) {
            return $query;
        } else if (isset($params->page) && $params->page) {
            //return $query->paginate($params->take);
            return $query->paginate($params->take, ['*'], null, $params->page);
        } else {
            isset($params->take) && $params->take ? $query->take($params->take) : false;//Take
            return $query->get();
        }

    }

    public function getOne($criteria, $params){
        /*== initialize query ==*/
        $query = $this->model->query();

        if (isset($params->filter->field)) {
            $field = $params->filter->field;
            $query->where($field, $criteria);
        }else{
            $query->where(is_numeric($criteria) ? 'id' : 'slug', $criteria);
        }

        /*== REQUEST ==*/
        return $query->first();
    }

    public function create($data){
        $product = $this->model->create($data);
        if ($product) {
        }
        return $product;
    }

    public function updateBy($criteria, $data, $params){
        /*== initialize query ==*/
        $query = $this->model->query();

        if (isset($params->filter->field)) {
            $field = $params->filter->field;
            $query->where($field, $criteria);
        }else{
            $query->where(is_numeric($criteria) ? 'id' : 'slug', $criteria);
        }

        $model = $query->first();

        if($model){
            $model->update($data);
            return $model;
        }
        return false;
    }

    public function deleteBy($criteria, $params){
        /*== initialize query ==*/
        $query = $this->model->query();

        if (isset($params->filter->field)) {
            $field = $params->filter->field;
            $query->where($field, $criteria);
        }else{
            $query->where(is_numeric($criteria) ? 'id' : 'slug', $criteria);
        }

        $model = $query->first();
        /*== REQUEST ==*/
        $model ? $model->delete() : false;
    }
}
