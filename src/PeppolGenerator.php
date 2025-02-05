<?php

namespace JGI\Peppol;

use JGI\Peppol\Model\{
    AllowanceCharge,
    FinancialInstitutionBranch,
    Invoice,
    InvoiceLine,
    Party,
    PayeeFinancialAccount,
    PaymentMeans,
    TaxCategory
};

class PeppolGenerator
{
    public function createInvoiceDocument(Invoice $invoice): PeppolDocument
    {
        $peppolDocument = new PeppolDocument();

        $peppolDocument->encoding = 'utf-8';
        $peppolDocument->xmlVersion = '1.0';
        $peppolDocument->formatOutput = true;

        $root = $peppolDocument->createElement('Invoice');
        $root->setAttributeNodeNS(new \DOMAttr('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'));
        $root->setAttributeNodeNS(new \DOMAttr('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'));
        $root->setAttributeNodeNS(new \DOMAttr('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2'));
        $root->appendChild($peppolDocument->createElement('cbc:CustomizationID', $invoice->getCustomizationId()));
        if ($invoice->getProfileId()) {
            $root->appendChild($peppolDocument->createElement('cbc:ProfileID', $invoice->getProfileId()));
        }
        $root->appendChild($peppolDocument->createElement('cbc:ID', $invoice->getId()));
        $root->appendChild($peppolDocument->createElement('cbc:IssueDate', $invoice->getIssueDate()->format('Y-m-d')));
        $root->appendChild($peppolDocument->createElement('cbc:DueDate', $invoice->getDueDate()->format('Y-m-d')));
        $root->appendChild($peppolDocument->createElement('cbc:InvoiceTypeCode', $invoice->getInvoiceTypeCode()));
        $root->appendChild($peppolDocument->createElement('cbc:DocumentCurrencyCode', $invoice->getCurrency()));
        if ($invoice->getAccountingCost()) {
            $root->appendChild($peppolDocument->createElement('cbc:AccountingCost', $invoice->getAccountingCost()));
        }
        if ($invoice->getBuyerReference()) {
            $root->appendChild($peppolDocument->createElement('cbc:BuyerReference', $invoice->getBuyerReference()));
        }

        $accountingSupplierParty = $peppolDocument->createElement('cac:AccountingSupplierParty');
        $accountingSupplierParty->appendChild(
            $this->createPartyElement($peppolDocument, $invoice->getAccountingSupplierParty())
        );
        $root->appendChild($accountingSupplierParty);

        $accountingCustomerParty = $peppolDocument->createElement('cac:AccountingCustomerParty');
        $accountingCustomerParty->appendChild(
            $this->createPartyElement($peppolDocument, $invoice->getAccountingCustomerParty())
        );
        $root->appendChild($accountingCustomerParty);

        if ($invoice->getAllowanceCharge()) {
            $root->appendChild(
                $this->createAllowanceCharge($peppolDocument, $invoice->getAllowanceCharge(), $invoice)
            );
        }

        $root->appendChild(
            $this->createTaxTotal($peppolDocument, $invoice)
        );

        $root->appendChild(
            $this->createLegalMonetaryTotal($peppolDocument, $invoice)
        );

        foreach ($invoice->getInvoiceLines() as $invoiceLine) {
            $root->appendChild(
                $this->createInvoiceLine($peppolDocument, $invoiceLine, $invoice)
            );
        }

        $root->appendChild(
            $this->createPayment($peppolDocument, $invoice)
        );

        $peppolDocument->appendChild($root);

        return $peppolDocument;
    }

    private function createAllowanceCharge(PeppolDocument $peppolDocument, AllowanceCharge $allowanceChargeModel, Invoice $invoice): \DOMNode
    {
        $allowanceCharge = $peppolDocument->createElement('cac:AllowanceCharge');
        $allowanceCharge->appendChild($peppolDocument->createElement('cbc:ChargeIndicator', $allowanceChargeModel->isChargeIndicator() ? 'true' : 'false'));
        $allowanceCharge->appendChild($peppolDocument->createElement('cbc:AllowanceChargeReason', $allowanceChargeModel->getAllowanceChargeReason()));

        $amount = $peppolDocument->createElement('cbc:Amount', $allowanceChargeModel->getAmount());
        $amount->setAttributeNodeNS(new \DOMAttr('currencyID', $invoice->getCurrency()));
        $allowanceCharge->appendChild($amount);

        if($allowanceChargeModel->getTaxCategory()){
            $allowanceCharge->appendChild(
                $this->createTaxCategory('cac:TaxCategory', $peppolDocument, $allowanceChargeModel->getTaxCategory())
            );
        }

        return $allowanceCharge;
    }

    private function createInvoiceLine(PeppolDocument $peppolDocument, InvoiceLine $invoiceLineModel, Invoice $invoice): \DOMNode
    {
        $invoiceLine = $peppolDocument->createElement('cac:InvoiceLine');
        $invoiceLine->appendChild($peppolDocument->createElement('cbc:ID', $invoiceLineModel->getId()));

        $invoicedQuantity = $peppolDocument->createElement('cbc:InvoicedQuantity', $invoiceLineModel->getQuantity());
        $invoicedQuantity->setAttribute('unitCode', $invoiceLineModel->getQuantityCode());
        $invoiceLine->appendChild($invoicedQuantity);

        $lineExtensionAmount = $peppolDocument->createElement('cbc:LineExtensionAmount', $invoiceLineModel->getExtensionAmount());
        $lineExtensionAmount->setAttribute('currencyID', $invoice->getCurrency());
        $invoiceLine->appendChild($lineExtensionAmount);

        if ($invoiceLineModel->getAccountingCost()) {
            $invoiceLine->appendChild($peppolDocument->createElement('cbc:AccountingCost', $invoiceLineModel->getAccountingCost()));
        }

        if ($invoiceLineModel->getOrderLineReferenceLineId()) {
            $invoiceLine
                ->appendChild($peppolDocument->createElement('cac:OrderLineReference'))
                ->appendChild($peppolDocument->createElement('cbc:LineID', $invoiceLineModel->getOrderLineReferenceLineId()));
        }


        $item = $peppolDocument->createElement('cac:Item');
        if ($invoiceLineModel->getItem()->getDescription()) {
            $description = $peppolDocument->createElement('cbc:Description');
            $description->appendChild($peppolDocument->createTextNode($invoiceLineModel->getItem()->getDescription()));
            $item->appendChild($description);
        }
        if ($invoiceLineModel->getItem()->getName()) {
            $item->appendChild($peppolDocument->createElement('cbc:Name', $invoiceLineModel->getItem()->getName()));
        }
        if ($invoiceLineModel->getItem()->getTaxCategory()) {
            $item->appendChild($this->createTaxCategory('cac:ClassifiedTaxCategory', $peppolDocument, $invoiceLineModel->getItem()->getTaxCategory()));
        }

        if ($invoiceLineModel->getItem()->getBuyersItemIdentification()) {   
            $item->appendChild($peppolDocument->createElement('cbc:BuyersItemIdentification', $invoiceLineModel->getItem()->getBuyersItemIdentification()));
        }

        if ($invoiceLineModel->getItem()->getSellersItemIdentification()) {
            $item->appendChild($peppolDocument->createElement('cbc:SellersItemIdentification', $invoiceLineModel->getItem()->getSellersItemIdentification()));
        }

        if ($invoiceLineModel->getItem()->getStandardItemIdentification()) {
            $item->appendChild($peppolDocument->createElement('cbc:StandardItemIdentification', $invoiceLineModel->getItem()->getStandardItemIdentification()));
        }

        if ($invoiceLineModel->getItem()->getOriginCountry()) {
            $item->appendChild($peppolDocument->createElement('cbc:OriginCountry', $invoiceLineModel->getItem()->getOriginCountry()));
        }   

        $invoiceLine->appendChild($item);

        $price = $peppolDocument->createElement('cac:Price');
        $priceAmount = $peppolDocument->createElement('cbc:PriceAmount', $invoiceLineModel->getPriceAmount());
        $priceAmount->setAttribute('currencyID', $invoice->getCurrency());
        $price->appendChild($priceAmount);
        $invoiceLine->appendChild($price);


        return $invoiceLine;
    }

    private function createTaxCategory($name, PeppolDocument $peppolDocument, TaxCategory $taxCategoryModel): \DOMNode
    {
        $taxCategory = $peppolDocument->createElement($name);
        $taxCategory->appendChild($peppolDocument->createElement('cbc:ID', $taxCategoryModel->getId()));
        $taxCategory->appendChild($peppolDocument->createElement('cbc:Percent', $taxCategoryModel->getPercent()));

        if($taxCategoryModel->getTaxExemptionReasonCode()) {
            $taxCategory->appendChild($peppolDocument->createElement('cbc:TaxExemptionReasonCode', $taxCategoryModel->getTaxExemptionReasonCode()));
        }
        if($taxCategoryModel->getTaxExemptionReason()) {
            $taxCategory->appendChild($peppolDocument->createElement('cbc:TaxExemptionReason', $taxCategoryModel->getTaxExemptionReason()));
        }

        $taxCategory
            ->appendChild($peppolDocument->createElement('cac:TaxScheme'))
            ->appendChild($peppolDocument->createElement('cbc:ID', $taxCategoryModel->getTaxSchemeId()));


        return $taxCategory;
    }

    private function createPartyElement(PeppolDocument $peppolDocument, Party $partyModel): \DOMNode
    {
        $party = $peppolDocument->createElement('cac:Party');
        $endpointId = $peppolDocument->createElement('cbc:EndpointID', $partyModel->getEndpointId());
        $endpointId->setAttribute('schemeID', $partyModel->getEndpointSchemeId());
        $party->appendChild($endpointId);

        $name = $peppolDocument->createElement('cbc:Name');
        $name->appendChild($peppolDocument->createTextNode($partyModel->getName()));
        $party->appendChild($peppolDocument->createElement('cac:PartyName'))->appendChild($name);

        $addressModel = $partyModel->getPostalAddress();
        $postalAddress = $peppolDocument->createElement('cac:PostalAddress');
        if ($addressModel->getStreetName()) {
            $streetName = $peppolDocument->createElement('cbc:StreetName');
            $streetName->appendChild($peppolDocument->createTextNode($addressModel->getStreetName()));
            $postalAddress->appendChild($streetName);
        }
        if ($addressModel->getAddressLine()) {
            $postalAddress->appendChild($peppolDocument->createElement('cbc:AddressLine', $addressModel->getAddressLine()));
        }
        if ($addressModel->getAdditionalStreetName()) {
            $postalAddress->appendChild($peppolDocument->createElement('cbc:AdditionalStreetName', $addressModel->getAdditionalStreetName()));
        }
        if ($addressModel->getCityName()) {
            $postalAddress->appendChild($peppolDocument->createElement('cbc:CityName', $addressModel->getCityName()));
        }
        if ($addressModel->getPostalZone()) {
            $postalAddress->appendChild($peppolDocument->createElement('cbc:PostalZone', $addressModel->getPostalZone()));
        }
        if ($addressModel->getCountryCode()) {
            $postalAddress
                ->appendChild($peppolDocument->createElement('cac:Country'))
                ->appendChild($peppolDocument->createElement('cbc:IdentificationCode', $addressModel->getCountryCode()));
        }
        $party->appendChild($postalAddress);

        if ($partyModel->getPartyTaxScheme()) {
            $partyTaxScheme = $peppolDocument->createElement('cac:PartyTaxScheme');
            $partyTaxScheme->appendChild($peppolDocument->createElement('cbc:CompanyID', $partyModel->getPartyTaxScheme()->getCompanyId()));
            $partyTaxScheme
                ->appendChild($peppolDocument->createElement('cac:TaxScheme'))
                ->appendChild($peppolDocument->createElement('cbc:ID', $partyModel->getPartyTaxScheme()->getTaxSchemeId()));
            $party->appendChild($partyTaxScheme);
        }


        $partyLegalEntity = $party->appendChild($peppolDocument->createElement('cac:PartyLegalEntity'));

        $name = $peppolDocument->createElement('cbc:RegistrationName');
        $name->appendChild($peppolDocument->createTextNode($partyModel->getRegistrationName()));
        $partyLegalEntity->appendChild($name);

        if ($partyModel->getCompanyId()) {
            $companyId = $peppolDocument->createElement('cbc:CompanyID');
            $companyId->appendChild($peppolDocument->createTextNode($partyModel->getCompanyId()));
            $companyId->setAttribute('schemeID', $partyModel->getCompanySchemeId());
            $partyLegalEntity->appendChild($companyId);
        }

        if ($partyModel->getContact()) {
            $contact = $peppolDocument->createElement('cac:Contact');
            $contact->appendChild($peppolDocument->createElement('cbc:Name', $partyModel->getContact()->getName()));
            $contact->appendChild($peppolDocument->createElement('cbc:Telephone', $partyModel->getContact()->getTelephone()));
            $contact->appendChild($peppolDocument->createElement('cbc:ElectronicMail', $partyModel->getContact()->getEmail()));
            $party->appendChild($contact);
        }

        return $party;

    }

    private function createLegalMonetaryTotal(PeppolDocument $peppolDocument, Invoice $invoice): \DOMNode
    {
        $legalMonetaryTotal = $peppolDocument->createElement('cac:LegalMonetaryTotal');

        $lineExtensionAmount = $peppolDocument->createElement('cbc:LineExtensionAmount', $invoice->getLineExtensionAmount());
        $lineExtensionAmount->setAttribute('currencyID', $invoice->getCurrency());
        $legalMonetaryTotal->appendChild($lineExtensionAmount);

        $taxExclusiveAmount = $peppolDocument->createElement('cbc:TaxExclusiveAmount', $invoice->getTaxExclusiveAmount());
        $taxExclusiveAmount->setAttribute('currencyID', $invoice->getCurrency());
        $legalMonetaryTotal->appendChild($taxExclusiveAmount);

        $taxInclusiveAmount = $peppolDocument->createElement('cbc:TaxInclusiveAmount', $invoice->getTaxInclusiveAmount());
        $taxInclusiveAmount->setAttribute('currencyID', $invoice->getCurrency());
        $legalMonetaryTotal->appendChild($taxInclusiveAmount);

        $chargeTotalAmount = $peppolDocument->createElement('cbc:ChargeTotalAmount', $invoice->getChargeTotalAmount());
        $chargeTotalAmount->setAttribute('currencyID', $invoice->getCurrency());
        $legalMonetaryTotal->appendChild($chargeTotalAmount);

        if ($invoice->getPayableRoundingAmount() != 0) {
            $roundingAmount = $peppolDocument->createElement('cbc:PayableRoundingAmount', $invoice->getPayableRoundingAmount());
            $roundingAmount->setAttribute('currencyID', $invoice->getCurrency());
            $legalMonetaryTotal->appendChild($roundingAmount);
        }

        $payableAmount = $peppolDocument->createElement('cbc:PayableAmount', $invoice->getInvoicePayableAmount());
        $payableAmount->setAttribute('currencyID', $invoice->getCurrency());
        $legalMonetaryTotal->appendChild($payableAmount);

        return $legalMonetaryTotal;
    }

    private function createTaxTotal(PeppolDocument $peppolDocument, Invoice $invoice): \DOMNode
    {
        $taxTotal = $peppolDocument->createElement('cac:TaxTotal');

        $taxAmount = $peppolDocument->createElement('cbc:TaxAmount', $invoice->getTaxAmount());
        $taxAmount->setAttribute('currencyID', $invoice->getCurrency());
        $taxTotal->appendChild($taxAmount);
        foreach ($invoice->getTaxSubTotals() as $taxSubTotalModel) {
            $taxSubTotal = $peppolDocument->createElement('cac:TaxSubtotal');

            $taxableAmount = $peppolDocument->createElement('cbc:TaxableAmount', $taxSubTotalModel->getTaxableAmount());
            $taxableAmount->setAttribute('currencyID', $invoice->getCurrency());
            $taxSubTotal->appendChild($taxableAmount);

            $taxAmount2 = $peppolDocument->createElement('cbc:TaxAmount', $taxSubTotalModel->getTaxAmount());
            $taxAmount2->setAttribute('currencyID', $invoice->getCurrency());
            $taxSubTotal->appendChild($taxAmount2);


            $taxSubTotal->appendChild(
                $this->createTaxCategory('cac:TaxCategory', $peppolDocument, $taxSubTotalModel->getTaxCategory())
            );

            $taxTotal->appendChild($taxSubTotal);
        }


        return $taxTotal;
    }

    private function createPayment(PeppolDocument $peppolDocument, Invoice $invoice): \DOMNode
    {
        $paymentMeansWrap = $peppolDocument->createElement('cac:PaymentMeans');

        $pmCode = $peppolDocument->createElement('cbc:PaymentMeansCode', $invoice->getPaymentMeans()->getPaymentMeansCode());
        $paymentMeansWrap->appendChild($pmCode);

        $payeeWrap = $peppolDocument->createElement('cac:PayeeFinancialAccount');
        $payeeWrap->appendChild($peppolDocument->createElement('cbc:ID', $invoice->getPaymentMeans()->getPayeeFinancialAccount()->getId()));

        $finWrap = $peppolDocument->createElement('cac:FinancialInstitutionBranch');
        $finWrap->appendChild($peppolDocument->createElement('cbc:ID', $invoice->getPaymentMeans()->getPayeeFinancialAccount()->getFinancialInstitution()->getId()));

        $payeeWrap->appendChild($finWrap);
        $paymentMeansWrap->appendChild($payeeWrap);

        return $paymentMeansWrap;
    }
}
