<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api;

/**
 * Represents customer address data fetched through the API. This data is
 * incompatible with the Magento address standard and has to be converted before
 * being used.
 */
class Address
{
    /**
     * Whether this address was fetched for a person or a company customer.
     *
     * NOTE: Not part of the address information returned from the API.
     *
     * NOTE: It's not clear from the data returned by the API what kind of
     * customer the address belongs to. By specifying it here we don't have to
     * pass around a flag stating its ownership.
     *
     * @var bool
     */
    private bool $isCompany;

    /**
     * What the full name represents depends on the customer type. If the
     * customer is a person (NATURAL) it's firstname + lastname name. If the
     * customer is a company (LEGAL) it's the name of the company.
     *
     * @var string
     */
    private string $fullName;

    /**
     * @var string
     */
    private string $firstName;

    /**
     * @var string
     */
    private string $lastName;

    /**
     * @var string
     */
    private string $addressRow1;

    /**
     * @var string
     */
    private string $addressRow2;

    /**
     * City.
     *
     * @var string
     */
    private string $postalArea;

    /**
     * @var string
     */
    private string $postalCode;

    /**
     * @var string
     */
    private string $country;

    /**
     * @var string
     */
    private string $telephone;

    /**
     * @param bool $isCompany
     * @param string $fullName
     * @param string $firstName
     * @param string $lastName
     * @param string $addressRow1
     * @param string $addressRow2
     * @param string $postalArea
     * @param string $postalCode
     * @param string $country
     * @param string $telephone
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        bool $isCompany = false,
        string $fullName = '',
        string $firstName = '',
        string $lastName = '',
        string $addressRow1 = '',
        string $addressRow2 = '',
        string $postalArea = '',
        string $postalCode = '',
        string $country = '',
        string $telephone = ''
    ) {
        $this->setIsCompany($isCompany)
            ->setFullName($fullName)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setAddressRow1($addressRow1)
            ->setAddressRow2($addressRow2)
            ->setPostalArea($postalArea)
            ->setPostalCode($postalCode)
            ->setCountry($country)
            ->setTelephone($telephone);
    }

    /**
     * @see Address::$isCompany
     * @param bool $value
     * @return self
     */
    public function setIsCompany(
        bool $value
    ): self {
        $this->isCompany = $value;

        return $this;
    }

    /**
     * @see Address::$isCompany
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCompany(): bool
    {
        return $this->isCompany;
    }

    /**
     * @see Address::$fullName
     * @param string $value
     * @return self
     */
    public function setFullName(
        string $value
    ): self {
        $this->fullName = $value;

        return $this;
    }

    /**
     * @see Address::$fullName
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setFirstName(
        string $value
    ): self {
        $this->firstName = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setLastName(
        string $value
    ): self {
        $this->lastName = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setAddressRow1(
        string $value
    ): self {
        $this->addressRow1 = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressRow1(): string
    {
        return $this->addressRow1;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setAddressRow2(
        string $value
    ): self {
        $this->addressRow2 = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressRow2(): string
    {
        return $this->addressRow2;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setPostalArea(
        string $value
    ): self {
        $this->postalArea = $value;

        return $this;
    }

    /**
     * @see Address::$postalArea
     * @return string
     */
    public function getPostalArea(): string
    {
        return $this->postalArea;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setPostalCode(
        string $value
    ): self {
        $this->postalCode = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setCountry(
        string $value
    ): self {
        $this->country = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setTelephone(
        string $value
    ): self {
        $this->telephone = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }
}
