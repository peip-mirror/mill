<?php
namespace Mill\Parser\Annotations;

use Mill\Container;
use Mill\Exceptions\Annotations\InvalidVendorTagSuppliedException;
use Mill\Parser\Annotation;

class VendorTagAnnotation extends Annotation
{
    const ARRAYABLE = [
        'vendor_tag'
    ];

    /** @var string Name of this vendor tag. */
    protected $vendor_tag;

    /**
     * {@inheritdoc}
     * @throws InvalidVendorTagSuppliedException If a found vendor tag is not present in your config file.
     */
    protected function parser(): array
    {
        $vendor_tag = trim($this->docblock);

        if (!empty($vendor_tag)) {
            // Validate the supplied vendor tag with what has been configured as allowable.
            $vendor_tags = $this->application->getConfig()->getVendorTags();
            if (!in_array($vendor_tag, $vendor_tags)) {
                /** @var string $method */
                $method = $this->method;
                throw InvalidVendorTagSuppliedException::create($vendor_tag, $this->class, $method);
            }
        }

        return [
            'vendor_tag' => $vendor_tag
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function interpreter(): void
    {
        $this->vendor_tag = $this->required('vendor_tag');
    }

    /**
     * @return string
     */
    public function getVendorTag(): string
    {
        return $this->vendor_tag;
    }

    /**
     * @param string $vendor_tag
     * @return VendorTagAnnotation
     */
    public function setVendorTag(string $vendor_tag): self
    {
        $this->vendor_tag = $vendor_tag;
        return $this;
    }
}
