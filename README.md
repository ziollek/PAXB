# PAXB


PAXB - very basic implementation of annotation driven xml binding

## Supported annotations

This lib supports listed bellow annotations:
- XmlAttribute(name="")
- XmlElement(name="", type="") - type is required for unmarshaling fields containing objects
- XmlElementWrapper(name) - name required
- XmlTransient
- XmlValue

These annotations could be used only as property annotations or class annotation(XmlElement only)

Sample code

## Marshalling example

/**
 * @XmlElement(name="root")
 */
class SampleEntity {

    /**
     * @XmlElement(name="attribute-value", type="AttributeValueEntity")
     */
    private $nestedEntity;

    private $text;

    /**
     * @XmlElementWrapper(name="number-list")
     */
    private $number = array();


    public function __construct($number = array(), $nestedEntity = null, $text = "")
    {
        $this->number = $number;
        $this->nestedEntity = $nestedEntity;
        $this->text = $text;
    }
}

class AttributeValueEntity {

    /**
     * @XmlAttribute
     */
    private $attribute;

    /**
     * @XmlElement
     */
    private $value;

    /**
     * @param string $attribute
     * @param string $value
     */
    public function __construct($attribute = "", $value = "")
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}

Marshalling code:

$sampleEntity = new SampleEntity(
    array(1,2,3),
    new AttributeValueEntity('sample attribure', 'sample value'),
    'Sample text'
);

echo PAXB\Setup::getMarshaller()->marshall($sampleEntity, true);

Output:

<?xml version="1.0"?>
<root>
    <attribute-value attribute="sample attribure">
        <value>sample value</value>
    </attribute-value>
    <text>Sample text</text>
    <number-list>
        <number>1</number>
        <number>2</number>
        <number>3</number>
    </number-list>
</root>

## Unmarshalling example

$xmlInput = '...'; //as above
/** @var SampleEntity $sampleEntity */
$sampleEntity = PAXB\Setup::getUnmarshaller()->unmarshall($xmlInput, 'SampleEntity');


## Running examples

1. Install composer: php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
2. Install dependencies: ./composer.phar install
3. run demo script: php demo/demo-marshall.php or php demo/demo-unmarshall.php

