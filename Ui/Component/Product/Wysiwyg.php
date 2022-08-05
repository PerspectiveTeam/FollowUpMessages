<?php

namespace Perspective\FollowUpMessages\Ui\Component\Product;

use Magento\Backend\Helper\Data as DataHelper;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;

class Wysiwyg extends \Magento\Ui\Component\Form\Element\Wysiwyg
{
    /**
     * @var DataHelper
     * @since 101.0.0
     */
    protected $backendHelper;

    /**
     * @var LayoutInterface
     * @since 101.0.0
     */
    protected $layout;

    /**
     * @param ContextInterface $context
     * @param FormFactory $formFactory
     * @param ConfigInterface $wysiwygConfig
     * @param LayoutInterface $layout
     * @param DataHelper $backendHelper
     * @param CategoryAttributeRepositoryInterface $attrRepository
     * @param array $components
     * @param array $data
     * @param array $config
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextInterface $context,
        FormFactory $formFactory,
        ConfigInterface $wysiwygConfig,
        LayoutInterface $layout,
        DataHelper $backendHelper,
        ProductAttributeRepositoryInterface $attrRepository,
        array $components = [],
        array $data = [],
        array $config = []
    ) {
        $this->layout = $layout;
        $this->backendHelper = $backendHelper;

        $config['wysiwyg'] = (bool)$attrRepository->get($data['name'])->getIsWysiwygEnabled();
        parent::__construct($context, $formFactory, $wysiwygConfig, $components, $data, $config);
    }
}
