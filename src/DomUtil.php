<?php
  namespace paslandau\DomUtility;

use paslandau\DomUtility\Exceptions\ElementNotFoundException;

class DomUtil{

    /**
     * Gets all namespaces declared in the document.
     * @see http://stackoverflow.com/a/2470433/413531
     * @param \DOMDocument $doc
     * @return string[] - Array of all declared namespaces.
     */
    public static function getAllNamespaces(\DOMDocument $doc)
    {
        $context = $doc->documentElement;
        $xpath = new \DOMXPath($doc);
        $ns = array();
        foreach ($xpath->query('namespace::*', $context) as $node) {
            $ns[] = $node->nodeValue;
        }
        return $ns;
    }

    /**
     * Checks wether $expression matches at least one node in $xpath.
     * @param \DOMXPath $xpath
     * @param string $expression
     * @param \DOMNode $contextnode [optional]. Default: null.
     * @return bool - true if the expression matches at least one node.
     */
	public static function elementExists(\DOMXPath $xpath, $expression, \DOMNode $contextnode =null){
		$nodes = $xpath->query($expression, $contextnode);
		return ($nodes->length > 0);
	}
	
	/**
	 * Get a string representation of the given XML-Element
	 * @param [\DomNode|\DomNodeList] $element
	 * @return string
	 */
	public static function toString($element){
		$xml = "";
		if($element instanceof \DOMNodeList){
			foreach($element as $child)
			$xml .= self::toString($child);
		}
		else{
			$xml = $element->ownerDocument->saveXML($element);
		}
		return $xml;
	}

    /**
     * Gets the InnerHTML of the node that is returned by the $query in $xpath
     * @param \DOMXPath $xpath
     * @param string $query
     * @param \DOMNode contextnode - [optional] Default: null. The optional contextnode can be specified for doing relative XPath queries. By default, the queries are relative to the root element.
     * @throws ElementNotFoundException
     * @return string - the InnerHTML
     */
	public static function getInnerHtml(\DOMXPath $xpath, $query, $contextnode = null){
		$nodes = $xpath->query($query, $contextnode);
		if($nodes->length === 0){
			throw new ElementNotFoundException("Could not find a matching node for xpath '$query'");
		}
        return self::toString($nodes->item(0)->childNodes);
	}
	
	/**
	 * Gets the OuterHtml of the nodes that is returned by the $query in $xpath
     * @param \DOMXPath $xpath
     * @param string $query
     * @param \DOMNode contextnode - [optional] Default: null. The optional contextnode can be specified for doing relative XPath queries. By default, the queries are relative to the root element.
     * @throws ElementNotFoundException
     * @return string - the OuterHTML
     */
	public static function getOuterHtml(\DOMXPath $xpath, $query, $contextnode = null){
        $nodes = $xpath->query($query, $contextnode);
        if($nodes->length === 0){
            throw new ElementNotFoundException("Could not find a matching node for xpath '$query'");
        }
        return self::toString($nodes->item(0));
	}
	
	/**
	* Gets the Text (nodeValue) of the node that is returned by the $query in $xpath.
     * @param \DOMXPath $xpath
     * @param string $query
     * @param \DOMNode contextnode - [optional] Default: null. The optional contextnode can be specified for doing relative XPath queries. By default, the queries are relative to the root element.
     * @throws ElementNotFoundException
     * @return string - the text
     */
	public static function getText(\DOMXPath $xpath, $query, $contextnode = null){
        $nodes = $xpath->query($query, $contextnode);
        if($nodes->length === 0){
            throw new ElementNotFoundException("Could not find a matching node for xpath '$query'");
        }
        return $nodes->item(0)->textContent;
	}
	
	/**
	 * Build a string to be used a search-string for a specific class,
	 * taking into account that classes can be combined. E.g.:
	 * - div class="myClass"> 
	 * - div class="myClass lol">
	 * - div class="lol myClass rofl">
	 * - div class="lol myClass">   
	 * @param string $el. E.g "@class" or "text()"
	 * @param string $search. The text to search, e.g. "myClass"
	 * @return string. E.g. $expression = "@class='fHa' or contains(./@class,' fHa ') or starts-with(./@class,'fHa ') or substring(@class, string-length(@class) - string-length(' fha')+1) = ' fha'";
	 */
	public static function getContainsXpathExpression($el, $search){
        $endsWith = self::getEndsWithXpathExpression($el, " ".$search);
		$expression = "{$el}='{$search}' or contains(./{$el},' {$search} ') or starts-with(./{$el},'{$search} ') or $endsWith"; // substring >> ends-with
		return $expression;
	}

    /**
     * @param $el
     * @param $search
     * @return string
     */
    public static function getEndsWithXpathExpression($el, $search){
        $s = "substring({$el}, string-length({$el}) - string-length('{$search}')+1) = '{$search}'";
        return $s;
    }
}
?>