<?php

namespace Perspective\FollowUpMessages\Model\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Perspective\FollowUpMessages\Api\Data\FollowUpSentResultInterfaceFactory as SearchResultFactory;
use Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex\CollectionFactory as CollectionFactory;
use Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex\Collection as Collection;
use Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex as Resource;
use Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterfaceFactory as ModelFactory;

class FollowUpSentRepository implements \Perspective\FollowUpMessages\Api\FollowUpSentRepositoryInterface
{
    /**
     * @var \Perspective\FollowUpMessages\Api\Data\FollowUpSentResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var \Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex
     */
    private $resource;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterfaceFactory
     */
    private $itemInterfaceFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var \Perspective\Logger\Helper\Logger
     */
    private $logger;

    /**
     * @param \Perspective\FollowUpMessages\Api\Data\FollowUpSentResultInterfaceFactory $searchResultFactory
     * @param \Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex\CollectionFactory $collectionFactory
     * @param \Perspective\FollowUpMessages\Model\ResourceModel\FollowUpSentIndex $resource
     * @param \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterfaceFactory $itemInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        CollectionFactory $collectionFactory,
        Resource $resource,
        ModelFactory $itemInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Perspective\Logger\Helper\Logger $logger,
        \Perspective\Logger\Helper\Logger\HandlerFactory $handlerFactory
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->itemInterfaceFactory = $itemInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $handler = $handlerFactory->create([
            'root' => '/var/log/follow_up',
            'filename' => 'follow_up.log'
        ]);
        $this->logger = $logger->pushHandler($handler);
    }

    /**
     * @inheritDoc
     */
    public function save(\Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $item)
    {
        try {
            $dataToSave = $this->dataObjectProcessor->buildOutputDataArray(
                $item,
                \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface::class
            );
            /** @var \Perspective\FollowUpMessages\Model\FollowUpSentIndex $model */
            $model = $this->itemInterfaceFactory->create();
            $model->addData($dataToSave);
            $this->resource->save($model);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $exception) {
            // Просто пропускаем, но залогаем на всякий случай
            $this->logger->info("Data inserting was skipped because it already exists\n");
            $this->logger->info(print_r($dataToSave, true) . "\n");
        } catch (\Exception $exception) {
            // А общие ошибки будем все так же логать и бросать эксепшен
            $this->logger->info("Error while inserting data\n");
            $this->logger->info(print_r($dataToSave, true) . "\n");
            $this->logger->info($exception->getMessage() . "\n");
            $this->logger->info($exception->getTraceAsString() . "\n");
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function delete(\Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $item)
    {
        try {

            $dataToSave = $this->dataObjectProcessor->buildOutputDataArray(
                $item,
                \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface::class
            );
            /** @var \Perspective\FollowUpMessages\Model\FollowUpSentIndex $model */
            $model = $this->itemInterfaceFactory->create();
            $model->addData($dataToSave);
            $this->resource->delete($model);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getEmptyModel()
    {
        return $this->itemInterfaceFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultFactory->create();
        $items = [];
        $searchResults->setSearchCriteria($searchCriteria);
        foreach ($collection as $itemModel) {
            /** @var \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface $itemData */
            $itemData = $this->itemInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $itemData,
                $itemModel->getData(),
                \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface::class
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $itemData,
                \Perspective\FollowUpMessages\Api\Data\FollowUpSentIndexInterface::class
            );
        }
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
