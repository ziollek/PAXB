<?php

namespace PAXB\Xml\Binding;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PAXB\Xml\Binding\Annotations\XmlAnnotation;
use PAXB\Xml\Binding\Annotations\XmlAttribute;
use PAXB\Xml\Binding\Annotations\XmlElement;
use PAXB\Xml\Binding\Annotations\XmlElementWrapper;
use PAXB\Xml\Binding\Metadata\ClassMetadata;
use PAXB\Xml\Binding\Structure\Attribute;
use PAXB\Xml\Binding\Structure\Element;

class AnnotationLoader {

    const ANNOTATIONS_NAMESPACE = 'PAXB\Xml\Binding\Annotations';

    const MODE_EMPTY     = 'DEFAULT';
    const MODE_ELEMENT   = 'ELEMENT';
    const MODE_ATTRIBUTE = 'ATTRIBUTE';
    const MODE_VALUE     = 'VALUE';
    const MODE_TRANSIENT = 'TRANSIENT';

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }


    /**
     * @param ClassMetadata $classMetadata
     */
    public function loadClassMetadata(ClassMetadata $classMetadata)
    {
        $classMetadata = $this->processClassAnnotations($classMetadata);
        $classMetadata = $this->processFieldsMetadata($classMetadata);

        return $classMetadata;
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return ClassMetadata
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function processClassAnnotations(ClassMetadata $classMetadata)
    {
        $annotations = $this->reader->getClassAnnotations($classMetadata->getReflection());
        $classTokens = explode('\\', $classMetadata->getClassName());
        //set default name
        $classMetadata->setName(end($classTokens));

        if (is_array($annotations)) {
            foreach ($annotations as $annotation) {
                switch (get_class($annotation)) {
                    case 'PAXB\Xml\Binding\Annotations\XmlElement':
                        /** @var \PAXB\Xml\Binding\Annotations\XmlElement $annotation */
                        if (!empty($annotation->name)) {
                            $classMetadata->setName($annotation->name);
                        }
                        break;
                    default:
                        if ($annotation instanceof XmlAnnotation) {
                            throw AnnotationException::semanticalError(
                                get_class($annotation) . ' not expected as class annotation'
                            );
                        }
                        break;
                }
            }
        }

        return $classMetadata;
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return ClassMetadata
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function processFieldsMetadata(ClassMetadata $classMetadata)
    {
        foreach ($classMetadata->getReflection()->getProperties() as $property) {

            $element   = null;
            $attribute = null;
            $state     = self::MODE_EMPTY;

            $annotations = $this->reader->getPropertyAnnotations($property);

            if (is_array($annotations)) {
                foreach ($annotations as $annotation) {
                    switch (get_class($annotation)) {
                        case 'PAXB\Xml\Binding\Annotations\XmlElement':
                            $state   = $this->changeMode($state, self::MODE_ELEMENT);

                            $element = $this->processElementAnnotation($element, $property, $annotation);
                            $classMetadata->addElement($property->getName(), $element);
                            break;

                        case 'PAXB\Xml\Binding\Annotations\XmlElementWrapper':
                            $state   = $this->changeMode($state, self::MODE_ELEMENT);

                            $element = $this->processElementWrapperAnnotation($annotation, $element, $property);
                            $classMetadata->addElement($property->getName(), $element);
                            break;

                        case 'PAXB\Xml\Binding\Annotations\XmlPhpCollection':
                            $state   = $this->changeMode($state, self::MODE_ELEMENT);

                            $element = $this->getCurrentElement($element, $property);
                            $element->setPhpCollection(true);
                            $classMetadata->addElement($property->getName(), $element);
                            break;

                        case 'PAXB\Xml\Binding\Annotations\XmlTransient':
                            $state   = $this->changeMode($state, self::MODE_TRANSIENT);
                            break;

                        case 'PAXB\Xml\Binding\Annotations\XmlAttribute':
                            $state   = $this->changeMode($state, self::MODE_TRANSIENT);

                            $attribute = $this->processAttribute($property, $annotation);
                            $classMetadata->addAttributes($property->getName(), $attribute);
                            break;

                        case 'PAXB\Xml\Binding\Annotations\XmlValue':
                            $state   = $this->changeMode($state, self::MODE_TRANSIENT);

                            $currentValue = $classMetadata->getValueElement();
                            if (!empty($currentValue)) {
                                throw AnnotationException::semanticalError(
                                    'Cannot set more than one field of complex element as XmlValue'
                                );
                            }
                            $classMetadata->setValueElement($property->getName());
                            break;

                        default:
                            if ($annotation instanceof XmlAnnotation) {
                                throw AnnotationException::semanticalError(
                                    get_class($annotation) . ' not expected as property annotation'
                                );
                            }
                            break;

                    }
                }
            }
            if ($state == self::MODE_EMPTY) {
                $classMetadata->addElement($property->getName(), $this->getDefaultElement($property));
            }
        }
        return $classMetadata;
    }

    /**
     * @param string $actualState
     * @param string $destinationState
     *
     * @return string
     */
    private function changeMode($actualState, $destinationState)
    {
        if ($actualState != self::MODE_EMPTY) {
            if ($actualState != self::MODE_ELEMENT || $actualState != $destinationState) {
                throw AnnotationException::semanticalError(
                    'Cannot use simultaneous '. $actualState.', ' . $destinationState. ' annotations '
                );
            }
        }

        return $destinationState;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return Element
     */
    public function getDefaultElement($property)
    {
        return new Element($property->getName(), Element::FIELD_SOURCE);
    }

    /**
     * @param Element             $element
     * @param \ReflectionProperty $property
     *
     * @return Element
     */
    public function getCurrentElement($element, $property)
    {
        if (is_null($element)) {
            $element = $this->getDefaultElement($property);
        }

        return $element;
    }

    /**
     * @param Element             $element
     * @param \ReflectionProperty $property
     * @param XmlElement          $annotation
     *
     * @return Element
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function processElementAnnotation($element, $property, $annotation)
    {
        $element = $this->getCurrentElement($element, $property);
        if (!empty($annotation->name)) {
            $element->setName($annotation->name);
        }
        if (!empty($annotation->type)) {
            if (!class_exists($annotation->type)) {
                throw AnnotationException::semanticalError(
                    'Cannot found defined type: ' . $annotation->type
                );
            }
            $element->setTypeValue($annotation->type);
        }

        return $element;
    }

    /**
     * @param XmlElementWrapper   $annotation
     * @param Element             $element
     * @param \ReflectionProperty $property
     *
     * @return Element
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function processElementWrapperAnnotation($annotation, $element, $property)
    {
        if (empty($annotation->name)) {
            throw AnnotationException::semanticalError(
                'Cannot use XmlElementWrapper without name'
            );
        }

        $element = $this->getCurrentElement($element, $property);
        $element->setWrapperName($annotation->name);
        return $element;
    }

    /**
     * @param \ReflectionProperty $property
     * @param XmlAttribute        $annotation
     *
     * @return Attribute
     */
    private function processAttribute($property, $annotation)
    {
        $attribute = new Attribute($property->getName(), Attribute::FIELD_SOURCE);
        if (!empty($annotation->name)) {
            $attribute->setName($annotation->name);
        }

        return $attribute;
    }

}