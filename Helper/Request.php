<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use function is_bool;
use function is_string;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Exception\MissingRequestParameterException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Request extends AbstractHelper
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Log $log
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Log $log
    ) {
        $this->resultFactory = $resultFactory;
        $this->log = $log;

        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function getResponse(): Json
    {
        return $this->resultFactory->create(ResultFactory::TYPE_JSON);
    }

    /**
     * Retrieve JSON response object.
     *
     * @param array<string, mixed> $data
     * @return Json
     * @throws Exception
     */
    public function getDataResponse(
        array $data
    ): Json {
        return $this->getResponse()->setData($data);
    }

    /**
     * Retrieve response with redirect header applied.
     *
     * @return Json
     * @throws Exception
     */
    public function getRedirectResponse(
        string $url
    ): Json {
        try {
            /** @var Json $result */
            $result = $this->resultFactory->create(
                ResultFactory::TYPE_JSON
            );

            $result->setHeader('location', '');
        } catch (Exception $e) {
            $this->log->exception($e);
            throw $e;
        }

        return $result;
    }
}
