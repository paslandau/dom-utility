<?php


use paslandau\DomUtility\Exceptions\ElementNotFoundException;
use paslandau\DomUtility\DomUtil;

class DomUtilTest extends PHPUnit_Framework_TestCase {

    private function getDomXpath(){
        $xmlString = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><foo><bar>Just a little piece of text with some german umlauts like äöüßÄÖÜ and maybe some more UTF-8 characters</bar><baz id=\"bar\"><baz-child>1</baz-child><baz-child>2</baz-child><bar>foo</bar></baz><contains-test class=\"test\" /><contains-test class=\"te starts with\" /><contains-test class=\"contains te string\" /><contains-test class=\"ends with te\" /></foo>";
        $doc = new DOMDocument();
        $doc->loadXml($xmlString);
        $xpath = new DOMXPath($doc);
        return $xpath;
    }

    public function test_getAllNamespaces(){
        $xmlString = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><foo xmlns=\"http://www.example.org/schema/list\"/>";
        $docSingle = new DOMDocument();
        $docSingle->loadXml($xmlString);

        $xmlString = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><foo xmlns=\"http://www.example.org/schema/list\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:custom=\"http://www.example.org/schema/custom\" />";
        $docMultiple = new DOMDocument();
        $docMultiple->loadXml($xmlString);
        $tests = [
            "no" =>
                [
                    "input" => $this->getDomXpath()->document,
                    "expected" => ["http://www.w3.org/XML/1998/namespace"]
                ],
            "multi" =>
                [
                    "input" => $docMultiple,
                    "expected" => ["http://www.w3.org/XML/1998/namespace", "http://www.example.org/schema/custom", "http://www.w3.org/2001/XMLSchema-instance", "http://www.example.org/schema/list"]
                ],
            "single" =>
                [
                    "input" => $docSingle,
                    "expected" => ["http://www.w3.org/XML/1998/namespace", "http://www.example.org/schema/list"]
                ],
        ];

        foreach($tests as $name => $data){
            $res = DomUtil::getAllNamespaces($data["input"]);
//            echo "\"".implode("\", \"",$res)."\"\n";
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_elementExists(){

        $input = $this->getDomXpath();

        $tests = [
            "found" =>
            [
                "query" => "//baz[@id='bar']",
                "context" => null,
                "expected" => true
            ],
            "found-context" =>
                [
                    "query" => ".//baz[@id='bar']",
                    "context" => $input->query("//foo")->item(0),
                    "expected" => true
                ],
            "not-found" =>
                [
                    "query" => "//baz[@id='barBoo']",
                    "context" => null,
                    "expected" => false
                ],
            "not-found-context" =>
                [
                    "query" => ".//baz[@id='bar']",
                    "context" => $input->query("//foo/bar")->item(0),
                    "expected" => false
                ],
        ];

        foreach($tests as $name => $data){
            $res = DomUtil::elementExists($input, $data["query"],$data["context"]);
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_toString(){

        $input = $this->getDomXpath();

        $tests = [
            "single" =>
                [
                    "element" => $input->query("//baz")->item(0),
                    "expected" => "<baz id=\"bar\"><baz-child>1</baz-child><baz-child>2</baz-child><bar>foo</bar></baz>"
                ],
            "list" =>
                [
                    "element" =>  $input->query("//baz-child"),
                    "expected" => "<baz-child>1</baz-child><baz-child>2</baz-child>"
                ],
        ];

        foreach($tests as $name => $data){
            try {
                $res = DomUtil::toString($data["element"]);
            }catch(Exception $e){
//                echo $e;
                $res = get_class($e);
            }
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_getInnerHtml(){

        $input = $this->getDomXpath();

        $tests = [
            "single" =>
                [
                    "query" => "//baz",
                    "expected" => "<baz-child>1</baz-child><baz-child>2</baz-child><bar>foo</bar>"
                ],
            "list" =>
                [
                    "query" =>  "//baz-child",
                    "expected" => "1"
                ],
            "not-found" =>
                [
                    "query" =>  "//not-found",
                    "expected" => ElementNotFoundException::class,
                ],
        ];

        foreach($tests as $name => $data){
            try {
                $res = DomUtil::getInnerHtml($input,$data["query"]);
            }catch(Exception $e){
                $res = get_class($e);
            }
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_getOuterHtml(){

        $input = $this->getDomXpath();

        $tests = [
            "single" =>
                [
                    "query" => "//baz",
                    "expected" => "<baz id=\"bar\"><baz-child>1</baz-child><baz-child>2</baz-child><bar>foo</bar></baz>"
                ],
            "list" =>
                [
                    "query" =>  "//baz-child",
                    "expected" => "<baz-child>1</baz-child>"
                ],
            "not-found" =>
                [
                    "query" =>  "//not-found",
                    "expected" => ElementNotFoundException::class,
                ],
        ];

        foreach($tests as $name => $data){
            try {
                $res = DomUtil::getOuterHtml($input,$data["query"]);
            }catch(Exception $e){
                $res = get_class($e);
            }
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_getText(){

        $input = $this->getDomXpath();

        $tests = [
            "single" =>
                [
                    "query" => "//foo/bar",
                    "expected" => "Just a little piece of text with some german umlauts like äöüßÄÖÜ and maybe some more UTF-8 characters"
                ],
            "list" =>
                [
                    "query" =>  "//baz-child",
                    "expected" => "1"
                ],
            "not-found" =>
                [
                    "query" =>  "//not-found",
                    "expected" => ElementNotFoundException::class,
                ],
        ];

        foreach($tests as $name => $data){
            try {
                $res = DomUtil::getText($input,$data["query"]);
            }catch(Exception $e){
                $res = get_class($e);
            }
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_getEndsWithXpathExpression(){

        $el = "id";
        $search = "test";

        $tests = [
            "default" =>
                [
                    "expected" => "substring({$el}, string-length({$el}) - string-length('{$search}')+1) = '{$search}'"
                ],
        ];

        foreach($tests as $name => $data){
            $res = DomUtil::getEndsWithXpathExpression($el,$search);
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_getContainsXpathExpression(){

        $el = "@id";
        $search = "test";

        $tests = [
            "default" =>
                [
                    "expected" => "{$el}='{$search}' or contains(./{$el},' {$search} ') or starts-with(./{$el},'{$search} ') or substring({$el}, string-length({$el}) - string-length(' {$search}')+1) = ' {$search}'"
                ],
        ];

        foreach($tests as $name => $data){
            $res = DomUtil::getContainsXpathExpression($el,$search);
            $this->assertEquals($data["expected"],$res,"Error in test $name");
        }
    }

    public function test_ShouldSelectByContains(){

        $el = "@class";
        $search = "te";

        $input = $this->getDomXpath();

        $tests = [
            "contains" =>
                [
                    "query" => "//*[".DomUtil::getContainsXpathExpression($el,$search)."]",
                    "expected" => 3
                ],
            "all" =>
                [
                    "query" => "//*[contains($el,'$search')]",
                    "expected" => 4
                ],
        ];

        foreach($tests as $name => $data){
            $res = $input->query($data["query"]);
            $this->assertEquals($data["expected"],$res->length,"Error in test $name");
        }
    }
}
 