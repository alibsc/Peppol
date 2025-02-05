<?php

namespace JGI\Peppol\Model;

class TaxCategory
{
    const TAX_SCHEME_VAT = 'VAT';

    const TAX_CATEGORY_STANDARD_RATE = 'S';
    
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var float
     */
    private $percent = 0;

    /**
     * @var string|null
     */
    private $taxSchemeId;

    /**
     * @var string|null
     */
    private $taxExemptionReasonCode;

    /**
     * @var string|null
     */
    private $taxExemptionReason;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return float
     */
    public function getPercent(): float
    {
        return $this->percent;
    }

    /**
     * @param float $percent
     */
    public function setPercent(float $percent): void
    {
        $this->percent = $percent;
    }

    /**
     * @return string|null
     */
    public function getTaxSchemeId(): ?string
    {
        return $this->taxSchemeId;
    }

    /**
     * @param string|null $taxSchemeId
     */
    public function setTaxSchemeId(?string $taxSchemeId): void
    {
        $this->taxSchemeId = $taxSchemeId;
    }

    /**
     * @return string|null
     */
    public function getTaxExemptionReasonCode(): ?string
    {
        return $this->taxExemptionReasonCode;
    }   

    /**
     * @param string|null $taxExemptionReasonCode
     */
    public function setTaxExemptionReasonCode(?string $taxExemptionReasonCode): void
    {
        $this->taxExemptionReasonCode = $taxExemptionReasonCode;
    }   

    /**
     * @return string|null
     */
    public function getTaxExemptionReason(): ?string
    {
        return $this->taxExemptionReason;
    }

    /**
     * @param string|null $taxExemptionReason
     */
    public function setTaxExemptionReason(?string $taxExemptionReason): void
    {
        $this->taxExemptionReason = $taxExemptionReason;
    }
}
