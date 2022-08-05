<?php

namespace Perspective\FollowUpMessages\Service\Aggregate;

use Magento\Catalog\Api\ProductRepositoryInterface;

class TwoProducts implements \Perspective\FollowUpMessages\Api\Data\AbstractProductAggregate
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function process(array $products)
    {
        $productsPrices = [];
        foreach ($products as $productSku) {
            $product = $this->productRepository->get($productSku);
            if ($product && $product->getData('follow_up_messages')) {
                $productsPrices[$product->getFinalPrice() ?? $product->getPrice()] = $productSku;
            }
        }
        krsort($productsPrices, SORT_NUMERIC);
        $newProducts = current(array_chunk($productsPrices, 2, 1));
        return $newProducts != false ? $newProducts : $products;
    }
}
