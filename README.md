
# Resurs Bank - Magento 2 module - Core

## Description

Core part of Magento 2 module. This module supports older Magento 2.3 framework, unlike offical module available in bitbucket.

---

## Prerequisites

* [Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/bk-install-guide.html) [Supports Magento 2.3.x+]
* PHP 7.4

---

## Changelog

#### 1.6.1

* Fixed Exception in gateway value handler.

#### 1.6.3

* Replaced logotypes.
* Added support for PHP 8.1 

#### 1.6.6

* Sort order of payment methods changed to adhere to change in Magento.
* Added sort order to payment methods table in configuration.
* Re-designed payment methods table.

#### 1.6.7

* Added additional documentation to configuration page.

#### 1.6.8

* Removed deprecated implementation of ObjectManager.

#### 1.6.9

* Discount items are now handled on product level and each discount percentage is assigned too a separate row.
* Revamped product tax calculations to support more.

#### 1.7.0

* Added saftey checks, ensuring code doesn't execute when not applicable to the payment method applied on an order.

#### 1.7.8

* Backport for older Magento 2.3 and added real support for PHP 7.4 :D

#### 1.7.9
* Added VAT support for 25.5% in Finland
