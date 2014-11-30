<?php


use paslandau\DomUtility\DomConverter;
use paslandau\DomUtility\DomConverterInterface;
use paslandau\DomUtility\Exceptions\DocumentConversionException;
use paslandau\DomUtility\Exceptions\ElementNotFoundException;
use paslandau\DomUtility\TidyWrapper;
use paslandau\DomUtility\XmlUtil;
use paslandau\WebUtility\EncodingConversion\EncodingConverter;

class DomConverterTest extends PHPUnit_Framework_TestCase {
    private $types = [
        "html4",
        "html5",
        "xml"
    ];

    public function getResponseString($encoding,$type,$malformed){

        mb_internal_encoding("utf-8");


        $content = "Just a little piece of text with some german umlauts like äöüßÄÖÜ and maybe some more UTF-8 characters";
        $malformedString = "";
        if($malformed){
            $malformedString = "<p class='unclosed'>malformed<span>";
        }
        $select = "<div id=\"foo\"><input type=\"hidden\" value=\"äöü\" /></div>";
        $content .= $select;

        //todo: add test when no encoing info is provided
        switch ($type) {
            case "html4" : {
                $meta = "<meta http-equiv='content-type' content='text/html; charset={$encoding}'>";
                $content = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head>{$meta}<title>Umlauts everywhere öäüßÖÄÜ</title>$malformedString</head><body>$content</body></html>";
                break;
            }
            case "html5" : {
                $meta = "<meta charset='{$encoding}'>";
                $content = "<!DOCTYPE html><html><head>{$meta}<title>Umlauts everywhere öäüßÖÄÜ</title>$malformedString</head><body>$content</body></html>";
                break;
            }
            case "xml" : {
                $meta = " encoding='{$encoding}'";
                $content = "<?xml version='1.0'{$meta}?>$malformedString<foo><bar></bar>$content</foo>";
                break;
            }
                default: throw new Exception("Uknown $type");
        }
        if($encoding !== null){
            $content = mb_convert_encoding($content,$encoding,mb_internal_encoding());
        }
        return $content;
    }


    //todo: re-activate html5 tests when http://stackoverflow.com/questions/27218460/how-to-upgrade-libxml2-on-travis-ci-build is answered
    public function test_convert(){
        $converter = new EncodingConverter("utf-8",true,true);
        $tidy = new TidyWrapper(new tidy());
        $converters = [
            "html4-no-converter-no-tidy" => new DomConverter(DomConverterInterface::HTML,null,null),
            "html4-converter-no-tidy" => new DomConverter(DomConverterInterface::HTML,$converter,null),
            "html4-no-converter-tidy" => new DomConverter(DomConverterInterface::HTML,null,$tidy),
            "html4-converter-tidy" => new DomConverter(DomConverterInterface::HTML,$converter,$tidy),
//            "html5-no-converter-no-tidy" => new DomConverter(DomConverterInterface::HTML,null,null),
            "html5-converter-no-tidy" => new DomConverter(DomConverterInterface::HTML,$converter,null),
//            "html5-no-converter-tidy" => new DomConverter(DomConverterInterface::HTML,null,$tidy),
            "html5-converter-tidy" => new DomConverter(DomConverterInterface::HTML,$converter,$tidy),
            "xml-no-converter-no-tidy" => new DomConverter(DomConverterInterface::XML,null,null),
            "xml-converter-no-tidy" => new DomConverter(DomConverterInterface::XML,$converter,null),
            "xml-no-converter-tidy" => new DomConverter(DomConverterInterface::XML,null,$tidy),
            "xml-converter-tidy" => new DomConverter(DomConverterInterface::XML,$converter,$tidy),
        ];

        $tests = [];
        $iso = "iso-8859-1";
        $utf8 = "utf-8";
        $query = "//div[@id='foo']//input/@value";
        $expectedUtf8 = "äöü";
        $expectedIso = mb_convert_encoding("äöü",$iso,$utf8);
        foreach($this->types as $type){
            $tests["$type-wellformed-$utf8"] = [
                "input" => $this->getResponseString($utf8,$type,false),
                "expected" => [
                    "$type-no-converter-no-tidy" => $expectedUtf8,
                    "$type-converter-no-tidy" => $expectedUtf8,
//                    "$type-no-converter-tidy" => $expectedUtf8, // not specified
                    "$type-converter-tidy" => $expectedUtf8,
                ]
            ];
            $tests["$type-malformed-$utf8"] = [
                "input" => $this->getResponseString($utf8,$type,true),
                "expected" => [
                    "$type-no-converter-no-tidy" => $expectedUtf8,
                    "$type-converter-no-tidy" => $expectedUtf8,
//                    "$type-no-converter-tidy" => $expectedUtf8, // not specified
                    "$type-converter-tidy" => $expectedUtf8,
                ]
            ];
            $tests["$type-wellformed-$iso"] = [
                "input" => $this->getResponseString($iso,$type,false),
                "expected" => [
                    "$type-no-converter-no-tidy" => $expectedUtf8, //$expectedIso, - loadHtml will get the encoding right even
                    "$type-converter-no-tidy" => $expectedUtf8,
//                    "$type-no-converter-tidy" => $expectedIso, // not specified
                    "$type-converter-tidy" => $expectedUtf8,
                ]
            ];
            $tests["$type-malformed-$iso"] = [
                "input" => $this->getResponseString($iso,$type,true),
                "expected" => [
                    "$type-no-converter-no-tidy" => $expectedUtf8, //$expectedIso, - loadHtml will get the encoding right even
                    "$type-converter-no-tidy" => $expectedUtf8, //$expectedIso, - loadHtml will get the encoding right even
//                    "$type-no-converter-tidy" => $expectedIso, // not specified
                    "$type-converter-tidy" => $expectedUtf8,
                ]
            ];
        }
        $type = "xml";
        $tests["$type-malformed-$utf8"]["expected"]["$type-no-converter-no-tidy"] = DocumentConversionException::class;
        $tests["$type-malformed-$utf8"]["expected"]["$type-converter-no-tidy"] = DocumentConversionException::class;
        $tests["$type-malformed-$iso"]["expected"]["$type-no-converter-no-tidy"] = DocumentConversionException::class;
        $tests["$type-malformed-$iso"]["expected"]["$type-converter-no-tidy"] = DocumentConversionException::class;

        foreach($tests as $name => $data) {
            foreach($data["expected"] as $converterType => $expected){
                if(!array_key_exists($converterType, $converters)){
                    continue;
                }
                $converter = $converters[$converterType];

                $parsedDoc = "[Parse Error]";
                try {
                    /** @var DomConverterInterface $converter */
                    $doc = $converter->convert($data["input"]);
                    $parsedDoc = $doc->saveXML();
                    $xpath = new DOMXPath($doc);

                    $actual = "[NOT FOUND]";
                    $nodes = $xpath->query($query);
                    if ($nodes->length > 0) {
                        $actual = $nodes->item(0)->nodeValue;
                    }
                }catch(Exception $e){
//                    echo $e;
                    $actual = get_class($e);
                }

                $msg = [
                    "Error at $name for converter type {$converterType}:",
                    "Internal encoding: ".mb_internal_encoding(),
                    "Input\n" . $data["input"] . "\n",
                    "Parsed Doc\n" . $parsedDoc . "\n",
                    "Excpected\n" . $expected . "\n",
                    "Actual\n" . $actual . "\n",
                ];
                $msg = implode("\n", $msg);
//                echo $msg;
                $this->assertEquals($expected,$actual,$msg);
            }
        }
    }
}
 