<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Embedables;

use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Embeddable]
class Address implements Stringable
{
	#[ORM\Column(type: 'string', length: 64, nullable: true)]
	protected ?string $street = null;

	#[ORM\Column(type: 'string', length: 48, nullable: true)]
	protected ?string $city = null;

	#[ORM\Column(type: 'string', length: 6, nullable: true)]
	protected ?string $zip = null;

	#[ORM\Column(type: 'string', length: 2, nullable: true)]
	protected ?string $country = null;


	public function __toString(): string
	{
		return implode(', ', array_filter([
			$this->street,
			$this->city,
			$this->zip,
			$this->country,
		]));
	}


	public function setStreet(?string $street): void
	{
		$this->street = $street;
	}


	public function getStreet(): ?string
	{
		return $this->street;
	}


	public function setCity(?string $city): void
	{
		$this->city = $city;
	}


	public function getCity(): ?string
	{
		return $this->city;
	}


	public function setZip(?string $zip): void
	{
		if (!empty($zip)) {
			$zip = str_replace(' ', '', $zip);
		}

		$this->zip = $zip;
	}


	public function getZip(): ?string
	{
		return $this->zip;
	}


	public function setCountry(?string $country): void
	{
		if (!empty($country)) {
			$country = strtoupper($country);
		}

		$this->country = $country;
	}


	public function getCountry(): ?string
	{
		return $this->country;
	}
}
