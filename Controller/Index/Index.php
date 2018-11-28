<?php

namespace Mmsbuilder\Premium\Controller\Index;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Category $category,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Mmsbuilder\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->category           = $category;
        $this->categoryRepository = $categoryRepository;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->customHelper      = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $categories  = $this->category->getStoreCategories();
        $resultArray = array();
        foreach ($categories as $category) {
            $categoryObj = $this->categoryRepository->get($category->getId());
            if ($categoryObj->getMssAttribute() && $categoryObj->getMssImage()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $resultArray[] = array(
                    'category_icon' => $mediaUrl . "catalog/category/" . $categoryObj->getMssImage(),
                    'id'   => $categoryObj->getId(),
                    'title' => $categoryObj->getName(),
                );
            }
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(array('status' => 'success', 'data'=>$resultArray));
        return $result;
    }
}
