<?php

namespace JGI\Peppol\Model;

class PayeeFinancialAccount
{
    private $id;
    private $name;

    /**
    * @var FinancialInstitutionBranch|null
    */
    private $financialInstitution;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFinancialInstitution(): ?FinancialInstitutionBranch
    {
        return $this->financialInstitution;
    }

    public function setFinancialInstitution(?FinancialInstitutionBranch $financialInstitution): void
    {
        $this->financialInstitution = $financialInstitution;
    }
}
