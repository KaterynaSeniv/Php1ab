<?php

declare(strict_types=1);

namespace App\Tasks;

class Store
{
    /**
     * @param Product[] $products
     */
    public function __construct(
        public readonly int $store_id,
        public string $name,
        private array $products = []
    ) {}

    public function addProduct(Product $product): void
    {
        $this->products[$product->id] = $product;
    }

    public function removeProduct(int $id): bool
    {
        if (isset($this->products[$id])) {
            unset($this->products[$id]);
            return true;
        }

        return false;
    }

    /**
     * @return Product[]
     */
    public function searchProduct(string $name): array
    {
        $result = [];
        foreach ($this->products as $product) {
            if (mb_stripos($product->name, $name) !== false) {
                $result[] = $product;
            }
        }

        return $result;
    }

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return array_values($this->products);
    }
}