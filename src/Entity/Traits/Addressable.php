<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Embeddables\Address;

trait Addressable
{
    #[ORM\Embedded(class: Address::class, columnPrefix: false)]
    protected Address $address;


	public function setStreet(?string $street): void
	{
		$this->address->setStreet($street);
	}


	public function getStreet(): ?string
	{
		return $this->address->getStreet();
	}


	public function setCity(?string $city): void
	{
		$this->address->setCity($city);
	}


	public function getCity(): ?string
	{
		return $this->address->getCity();
	}


	public function setZip(?string $zip): void
	{
		$this->address->setZip($zip);
	}


	public function getZip(): ?string
	{
		return $this->address->getZip();
	}


	public function setCountry(?string $country): void
	{
		$this->address->setCountry($country);
	}


	public function getCountry(): ?string
	{
		return $this->address->getCountry();
	}


	public function getAddress(): ?string
	{
		return $this->address->__toString() ?: null;
	}
}
