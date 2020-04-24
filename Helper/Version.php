<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use \Exception;
use \Magento\Framework\Exception\FileSystemException;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Component\ComponentRegistrarInterface;
use \Magento\Framework\Component\ComponentRegistrar;
use \Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * Version collection methods.
 *
 * @package Resursbank\Core\Helper
 */
class Version extends AbstractHelper
{
    /**
     * @var string
     */
    const MODULE_NAME = 'Resursbank_Core';

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param Context $context
     */
    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Context $context
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;

        parent::__construct($context);
    }

    /**
     * Retrieve version specified in module composer.json
     *
     * @return string
     */
    public function getComposerVersion(): string
    {
        $result = 'unknown';

        try {
            // Get data from composer.json
            $data = $this->getComposerData();

            // Resolve version from data array.
            if (isset($data['version']) &&
                (string) $data['version'] !== ''
            ) {
                $result = (string) $data['version'];
            }
        } catch (Exception $e) {
            // $this->log->error($e);
        }

        return $result;
    }

    /**
     * Retrieve data from module composer.json
     *
     * @return array
     * @throws FileSystemException
     */
    private function getComposerData(): array
    {
        $baseDirectory = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            self::MODULE_NAME
        );

        $dir = $this->readFactory->create($baseDirectory);

        // Suppress PHP errors. Failure to collect information from the file
        // should be handled further up. If the composer.json file cannot be
        // read it just makes things more difficult for us to debug. An error
        // here will cause all API communication to cease functioning though,
        // so it's better we just ignore it since it's not operation critical.
        return (array) @json_decode(
            (string) $dir->readFile('composer.json'),
            true
        );
    }
}
