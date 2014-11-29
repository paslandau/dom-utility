<?php
namespace paslandau\DomUtility;

interface DomConverterInterface{
	const HTML = "HTML";
	const XML = "XML";
	/**
	 * @param string $str
	 * @return \DOMDocument
	 */
	public function convert($str);
}