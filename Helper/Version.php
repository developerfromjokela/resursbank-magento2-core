<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Resursbank\Core\Exception\InvalidDataException;
use function is_string;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class Version extends AbstractHelper
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param Log $log
     */
    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Log $log
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->log = $log;

        parent::__construct($context);
    }

    /**
     * Retrieve version number assigned in composer.json file.
     *
     * @param string $module (eg. Resursbank_Core)
     * @return string
     */
    public function getComposerVersion(
        string $module
    ): string {
        $result = 'unknown';

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                $module
            );
            
            if ($path === null) {
                throw new InvalidDataException(
                    __('Failed to resolve module path.')
                );
            }

            $raw = (array) json_decode(
                $this->readFactory->create($path)->readFile('composer.json'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (isset($raw['version']) &&
                is_string($raw['version']) &&
                $raw['version'] !== ''
            ) {
                $result = $raw['version'];
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
