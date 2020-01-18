<?php


namespace Magenest\ProductFeed\Controller\Adminhtml\Mail;

use Magenest\ProductFeed\Helper\Data;
use Magento\Backend\App\Action;

class Sendmail extends Action
{
    /** @var Data */
    protected $sendMail;

    public function __construct(
        Action\Context $context,
        Data $sendMail
    ) {
        $this->sendMail = $sendMail;
        parent::__construct($context);
    }

    public function execute()
    {
        $success = "success";
        return $this->sendMail->sendMail($success);
    }
}
