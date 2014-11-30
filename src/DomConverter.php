<?php
namespace paslandau\DomUtility;

use paslandau\DomUtility\Exceptions\DocumentConversionException;
use paslandau\WebUtility\EncodingConversion\EncodingConverterInterface;

class DomConverter implements DomConverterInterface{
	/**
	 * @see DomConverterInterface::*
	 * @var string
	 */
	private $domType;
	/**
	 * 
	 * @var EncodingConverterInterface|null
	 */
	private $encodingConverter;
	
	/**
	 * @var TidyWrapper|null
	 */
	private $tidy;
	
	/**
	 * Caution: Using tidy without $specifying the $encodingConverter may yield unexpected results!
     *
	 * @param String $domType - @see DomConverterInterface::*
	 * @param EncodingConverterInterface $encodingConverter [optional]. Default: null.
	 * @param TidyWrapper $tidy [optional]. Default: null.
	 * @throws \InvalidArgumentException
	 */
	public function __construct($domType, EncodingConverterInterface $encodingConverter = null, TidyWrapper $tidy = null){
		$types = (new \ReflectionClass(__CLASS__))->getConstants();
		if(!in_array($domType, $types)){
			throw new \InvalidArgumentException("domType '$domType' is unknown. Possible values: ".implode(", ",$types));
		}
		$this->domType = $domType;
		$this->encodingConverter = $encodingConverter;
		$this->tidy = $tidy;
	}
	
	/**
	 * @param string $str
	 * @return \DOMDocument
	 */
	public function convert($str){
        $encoding = null;
        if($this->encodingConverter !== null){
            $headers = ["Content-Type" => "text/html"];
            if($this->domType === self::XML){
                $headers = ["Content-Type" => "application/xml"];
            }
            $newStr = $this->encodingConverter->convert($headers,$str);
            if($newStr !== null){
                $str = $newStr->getTargetContent();
            }
            $encoding = $this->encodingConverter->getTargetEncoding();
        }
        if($this->tidy !== null){
            $str = $this->tidy->repairString($str, $encoding, ($this->domType === self::XML));
        }
		$doc = $this->getDoc($str);
		return $doc;
	}

	private function getDoc($str){
		$doc = null;
		switch($this->domType){
			case DomConverterInterface::HTML:{
				$doc = $this->GetHtmlDoc($str);
			}
			break;
			case DomConverterInterface::XML:{
				$doc = $this->GetXmlDoc($str);
			}
			break;
			default:{
				throw new \UnexpectedValueException("domType '{$this->domType}' is unknown");
			}
		}
		return $doc;
	}
	
	/**
	 * 
	 * @param string $str
	 * @throws DocumentConversionException
	 * @return \DOMDocument
	 */
	private function GetHtmlDoc($str){
		$doc = null;
		if($this->encodingConverter !== null){
            $encoding = $this->encodingConverter->getTargetEncoding();
			$doc = new \DOMDocument(null,$encoding);
		}else{
			$doc = new \DOMDocument();
		}
        if(!@$doc->loadHTML($str)){
            throw new DocumentConversionException("Unable to transform given string into an HTML Document");
        }
		return $doc;
	}
	

	/**
	 * 
	 * @param string $str
	 * @throws DocumentConversionException
	 * @return \DOMDocument
	 */
	private function GetXmlDoc($str){
        $doc = new \DOMDocument();
        if(!@$doc->loadXML($str)){
            throw new DocumentConversionException("Unable to transform given string into an XML Document");
        }
		return $doc;
	}
}