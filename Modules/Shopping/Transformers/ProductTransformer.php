<?php

namespace Modules\Shopping\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'externalId' => $this->when($this->external_id, $this->external_id),
            'name' => $this->name ?? '',
            'slug' => $this->slug ?? '',
            'options' => $this->when($this->options, $this->options),
            'sku' => $this->when($this->sku, $this->sku),
            'quantity' => $this->when(isset($this->quantity), $this->quantity),
            'shipping' => $this->when($this->shipping, ((int)$this->shipping ? true : false)),
            'price' => $this->when($this->price, $this->price),
            'dateAvailable' => $this->when($this->date_available, $this->date_available),
            'description' => $this->when($this->description, $this->description),
            'createdAt' => $this->when($this->created_at, $this->created_at),
            'updatedAt' => $this->when($this->updated_at, $this->updated_at),
            'status' => $this->when(isset($this->status), $this->status),
            'stockStatus' => $this->when(isset($this->stock_status), $this->stock_status),
            'parentId' => $this->when($this->parent_id, $this->parent_id),
            'addedById' => $this->when($this->added_by_id, $this->added_by_id),
            'categoryId' => $this->when($this->category_id, intval($this->category_id)),
        ];
        return $data;
    }
}
