<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Codilar\VendorGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;



/**
 * Products field resolver, used for GraphQL request processing.
 */
class ProductVendorResolver implements ResolverInterface
{
    /**
     * @var ProductQueryInterface
     */
    private $searchQuery;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchApiCriteriaBuilder;

    /**
     * @param ProductQueryInterface $searchQuery
     * @param SearchCriteriaBuilder|null $searchApiCriteriaBuilder
     */
    public function __construct(
        ProductQueryInterface                                          $searchQuery,
        \Magento\Framework\Api\SearchCriteriaBuilder                   $searchCriteriaBuilders,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface                                     $productRepository,
        SearchCriteriaBuilder                                          $searchApiCriteriaBuilder = null
    )
    {
        $this->searchQuery = $searchQuery;
        $this->searchCriteriaBuilders = $searchCriteriaBuilders;
        $this->productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->searchApiCriteriaBuilder = $searchApiCriteriaBuilder ??
            \Magento\Framework\App\ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    )
    {
        $searchCriteriaFilter = $this->searchCriteriaBuilders->addFilter('vendor_dropdown', $args['id'], 'eq')->create();
        $productCollection = $this->productRepository->getList($searchCriteriaFilter);
        $productRecord['items'] = [];
        foreach ($productCollection->getItems() as $VendorProduct) {
            $productId = $VendorProduct->getId();
            $productRecord['items'][$productId] = $VendorProduct->getData();
            $productRecord['items'][$productId] ['model'] = $VendorProduct;
        }

        return $productRecord;
    }

}
