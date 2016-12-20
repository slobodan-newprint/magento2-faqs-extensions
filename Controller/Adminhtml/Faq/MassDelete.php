<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-18 02:30:05
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-20 21:16:55
 */

namespace PHPCuong\Faq\Controller\Adminhtml\Faq;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use PHPCuong\Faq\Model\ResourceModel\Faq\CollectionFactory;
use PHPCuong\Faq\Model\ResourceModel\Faq;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PHPCuong_Faq::faq_delete';
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $page) {
            $page->delete();
            $faq_id = $page->getData()['faq_id'];

            $url_rewrite_model = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');

            $urls_rewrite = $url_rewrite_model->getCollection()
            ->addFieldToFilter('entity_type', Faq::FAQ_ENTITY_TYPE)
            ->addFieldToFilter('entity_id', $faq_id)
            ->load()->getData();
            foreach ($urls_rewrite as $value) {
                $url_rewrite_model = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');
                $url_rewrite_model->load($value['url_rewrite_id'])->delete();
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
