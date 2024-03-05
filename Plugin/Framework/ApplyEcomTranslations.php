<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Framework;

use Magento\Framework\Phrase\Renderer\Placeholder;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Config;
use Throwable;

/**
 * Applies Ecom translations.
 */
class ApplyEcomTranslations
{
    /**
     * Check for untranslated Ecom phrases and feed them into Ecom's translation class.
     *
     * @param Placeholder $subject
     * @param string $result
     * @return string
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRender(
        Placeholder $subject,
        string $result
    ) {
        try {
            if (str_starts_with(haystack: $result, needle: 'rb-')) {
                $result = Translator::translate(
                    phraseId: substr(string: $result, offset: 3)
                );
            }
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return $result;
    }
}
