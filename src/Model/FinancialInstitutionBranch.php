<?php

namespace JGI\Peppol\Model;

class FinancialInstitutionBranch
{
    private $id;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
