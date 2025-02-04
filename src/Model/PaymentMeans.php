<?php

namespace JGI\Peppol\Model;

class PaymentMeans
{
    private $paymentMeansCode;
    private $paymentID;

    public function getPaymentMeansCode(): ?string
    {
        return $this->paymentMeansCode;
    }

    public function setPaymentMeansCode(?string $paymentMeansCode): void
    {
        $this->paymentMeansCode = $paymentMeansCode;
    }

    public function getPaymentID(): ?string
    {
        return $this->paymentID;
    }

    public function setPaymentID(?string $paymentID): void
    {
        $this->paymentID = $paymentID;
    }
}