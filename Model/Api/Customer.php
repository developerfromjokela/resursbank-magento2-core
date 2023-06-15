<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api;

/**
 * Represents customer data fetched through the API. This data is incompatible
 * with the Magento customer standard and has to be converted before being used.
 */
class Customer
{
    /**
     * @var string
     */
    private string $governmentId;

    /**
     * @var string
     */
    private string $phone;

    /**
     * @var string
     */
    private string $email;

    /**
     * Customer type (NATURAL (private) | LEGAL (company)).
     *
     * @var string
     */
    private string $type;

    /**
     * @var Address
     */
    private Address $address;

    /**
     * @param string $governmentId
     * @param string $phone
     * @param string $email
     * @param string $type
     * @param Address|null $address
     */
    public function __construct(
        string $governmentId = '',
        string $phone = '',
        string $email = '',
        string $type = '',
        Address $address = null
    ) {
        $this->setGovernmentId($governmentId)
            ->setPhone($phone)
            ->setEmail($email)
            ->setType($type)
            ->setAddress($address ?? new Address());
    }

    /**
     * Set governmentId
     *
     * @see Customer::$governmentId
     * @param string $value
     * @return self
     */
    public function setGovernmentId(
        string $value
    ): self {
        $this->governmentId = $value;

        return $this;
    }

    /**
     * Get governmentId
     *
     * @see Customer::$governmentId
     * @return string
     */
    public function getGovernmentId(): string
    {
        return $this->governmentId;
    }

    /**
     * Set phone
     *
     * @see Customer::$phone
     * @param string $value
     * @return self
     */
    public function setPhone(
        string $value
    ): self {
        $this->phone = $value;

        return $this;
    }

    /**
     * Get phone
     *
     * @see Customer::$phone
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @see Customer::$email
     * @param string $value
     * @return self
     */
    public function setEmail(
        string $value
    ): self {
        $this->email = $value;

        return $this;
    }

    /**
     * Get email
     *
     * @see Customer::$email
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set type
     *
     * @see Customer::$type
     * @param string $value
     * @return self
     */
    public function setType(
        string $value
    ): self {
        $this->type = $value;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set address
     *
     * @param Address $value
     * @return self
     */
    public function setAddress(
        Address $value
    ): self {
        $this->address = $value;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * Check if customer is a company.
     *
     * @return bool
     */
    public function isCompany(): bool
    {
        return $this->type === 'LEGAL';
    }
}
