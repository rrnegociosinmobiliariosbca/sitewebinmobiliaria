<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $inmueble = null;

    #[ORM\Column(length: 255)]
    private ?string $valor = null;

    #[ORM\Column(length: 255)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255)]
    private ?string $barrio = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo_contrato = null;

    #[ORM\Column(length: 255)]
    private ?string $observacion = null;

    #[ORM\Column(length: 255)]
    private ?string $ubicacion = null;

    /**
     * @var Collection<int, PropertyImage>
     */
    #[ORM\OneToMany(targetEntity: PropertyImage::class, mappedBy: 'property')]
    private Collection $propertyImages;

    /**
     * @var Collection<int, PropertyDetalle>
     */
    #[ORM\OneToMany(targetEntity: PropertyDetalle::class, mappedBy: 'property')]
    private Collection $propertyDetalles;

    #[ORM\Column(length: 255)]
    private ?string $tipo_inmueble = null;

    #[ORM\Column(length: 255)]
    private ?string $codigo_inmueble = null;

    public function __construct()
    {
        $this->propertyImages = new ArrayCollection();
        $this->propertyDetalles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInmueble(): ?string
    {
        return $this->inmueble;
    }

    public function setInmueble(string $inmueble): static
    {
        $this->inmueble = $inmueble;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): static
    {
        $this->valor = $valor;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getBarrio(): ?string
    {
        return $this->barrio;
    }

    public function setBarrio(string $barrio): static
    {
        $this->barrio = $barrio;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): static
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(string $ubicacion): static
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * @return Collection<int, PropertyImage>
     */
    public function getPropertyImages(): Collection
    {
        return $this->propertyImages;
    }

    public function addPropertyImage(PropertyImage $propertyImage): static
    {
        if (!$this->propertyImages->contains($propertyImage)) {
            $this->propertyImages->add($propertyImage);
            $propertyImage->setProperty($this);
        }

        return $this;
    }

    public function removePropertyImage(PropertyImage $propertyImage): static
    {
        if ($this->propertyImages->removeElement($propertyImage)) {
            // set the owning side to null (unless already changed)
            if ($propertyImage->getProperty() === $this) {
                $propertyImage->setProperty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PropertyDetalle>
     */
    public function getPropertyDetalles(): Collection
    {
        return $this->propertyDetalles;
    }

    public function addPropertyDetalle(PropertyDetalle $propertyDetalle): static
    {
        if (!$this->propertyDetalles->contains($propertyDetalle)) {
            $this->propertyDetalles->add($propertyDetalle);
            $propertyDetalle->setProperty($this);
        }

        return $this;
    }

    public function removePropertyDetalle(PropertyDetalle $propertyDetalle): static
    {
        if ($this->propertyDetalles->removeElement($propertyDetalle)) {
            // set the owning side to null (unless already changed)
            if ($propertyDetalle->getProperty() === $this) {
                $propertyDetalle->setProperty(null);
            }
        }

        return $this;
    }

    public function getTipoInmueble(): ?string
    {
        return $this->tipo_inmueble;
    }

    public function setTipoInmueble(string $tipo_inmueble): static
    {
        $this->tipo_inmueble = $tipo_inmueble;

        return $this;
    }

    public function getCodigoInmueble(): ?string
    {
        return $this->codigo_inmueble;
    }

    public function setCodigoInmueble(string $codigo_inmueble): static
    {
        $this->codigo_inmueble = $codigo_inmueble;

        return $this;
    }

    public function getTipoContrato(): ?string
    {
        return $this->tipo_contrato;
    }
    public function setTipoContrato(?string $tipo_contrato): static
    {
        $this->tipo_contrato = $tipo_contrato;

        return $this;
    }
}
