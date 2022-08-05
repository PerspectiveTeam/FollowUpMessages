<?php

namespace Perspective\FollowUpMessages\Service;

class ProductsAggregator
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(
        array $data = []
    ) {
        $this->data = $data;
    }

    public function execute(array $products)
    {
        $newOrder = [];
        foreach ($this->data as $value) {
            $newOrder[$value['sort_order']] = $value['class'];
        }
        ksort($newOrder,SORT_NUMERIC);
        /** @var \Perspective\FollowUpMessages\Api\Data\AbstractProductAggregate $newOrderData */
        foreach ($newOrder as $newOrderData) {
            $products = $newOrderData->process($products);
        }
        return $products;
    }
}
