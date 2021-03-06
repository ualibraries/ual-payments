<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Fee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fee_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $balance;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Transaction", inversedBy="fees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    public function __construct($fee_id, $balance, $label)
    {
        $this->fee_id = $fee_id;
        $this->balance = $balance;
        $this->label = $label;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFeeId(): ?string
    {
        return $this->fee_id;
    }

    public function setFeeId(string $fee_id): self
    {
        $this->fee_id = $fee_id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function setBalance($balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }
}
