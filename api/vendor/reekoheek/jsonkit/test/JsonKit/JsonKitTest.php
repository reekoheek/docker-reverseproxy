<?php

namespace JsonKit;

class CustomObject implements \JsonKit\JsonSerializer
{
    public function jsonSerialize()
    {
        return "Static Value";
    }
}

class JsonKitTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $obj = new CustomObject();

        $json = \JsonKit\JsonKit::encode($obj);

        $this->assertEquals(json_decode($json), "Static Value");

        $complicatedObject = array(
            'objects' => array(
                $obj, $obj, $obj
            ),
            'objectMap' => array(
                'anObject' => $obj,
                'nestedObject' => array(
                    'anotherObject' => $obj,
                ),
            ),
        );

        $trueObject = new \stdClass();
        $trueObject->nestedObjectOnTrueObject = $obj;
        $complicatedObject['objectMap']['trueObject'] = $trueObject;

        $json = \JsonKit\JsonKit::encode($complicatedObject);

        $this->result = json_decode($json, 1);
    }

    public function testEncode()
    {
        $obj = array(
            'name' => 'putra',
            'age' => 17
        );

        $json = \JsonKit\JsonKit::encode($obj);

        $a = json_decode($json);

        $this->assertEquals((array) $obj, (array) $a, 'Source object should be equals to the json_decoded json string result');
    }

    public function testEncodeObject1()
    {
        $this->assertEquals($this->result['objects'][2], "Static Value");
    }

    public function testEncodeObject2()
    {
        $this->assertEquals($this->result['objectMap']['anObject'], "Static Value");
    }

    public function testEncodeObject3()
    {
        $this->assertEquals($this->result['objectMap']['nestedObject']['anotherObject'], "Static Value");
    }

    public function testEncodeObject4()
    {
        $this->assertEquals($this->result['objectMap']['trueObject']['nestedObjectOnTrueObject'], "Static Value");
    }
}
