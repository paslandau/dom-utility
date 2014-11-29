<?php

namespace paslandau\DomUtility;


class TidyWrapper {

    /**
     * @var \tidy
     */
    private $tidy;

    function __construct(\tidy $tidy)
    {
        $this->tidy = $tidy;
    }

    /**
     * Repairs a string represenation of a HTML/XML document
     * @param string $str
     * @param null|string $encoding [optional]. Default: null.
     * @return string
     */
    public function repairString($str, $encoding = null, $isXml = false){

        if($isXml){
            $options = array(
                'output-xml' => true,
                'input-xml' => true
            );
        }else{
            $options = $this->getHtml5Options();
        }
        if($encoding === null){
            $encoding = mb_internal_encoding();
        }
            $tidyEnc = self::toTidyEncoding($encoding);
            $result= $this->tidy->repairString($str, $options, $tidyEnc);
        return $result;
    }

    /**
     * @see http://stackoverflow.com/a/6484549/413531 -- hack to work with html5
     * @return array
     */
    private function getHtml5Options(){
        $options = array(
            "numeric-entities" => false,
            'new-blocklevel-tags' => 'article,header,footer,section,nav',
            'new-inline-tags' => 'video,audio,canvas,ruby,rt,rp',
            'new-empty-tags' => 'source'
        );
        return $options;
    }

    /**
     * Converts $encoding in an encoding string that is known by tidy.
     * @param $encoding
     * @return string
     */
    public static function toTidyEncoding($encoding){
        $knownEncodings = array(
            "ascii" => "ascii",
            "iso-8859-9" => "latin0",
            "iso-8859-1" => "latin1",
            "utf-8" => "utf8",
            "iso-2022" => "iso2022",
            "cp-1252" => "win1252",
            "utf-16" => "utf16",
            "utf-16be" => "utf16be",
            "utf-16le" => "utf16le",
            "big-5" => "big5",
            "sjis" => "shiftjis",
        );
        $encoding = mb_strtolower($encoding);
        if(array_key_exists($encoding, $knownEncodings)){
            return $knownEncodings[$encoding];
        }
        if(in_array($encoding,$knownEncodings)){
            return $encoding;
        }
        $all = array_merge(array_keys($knownEncodings),$knownEncodings);
        throw new \UnexpectedValueException("Encoding '$encoding' is unknown. Allowed valued are: ".implode(", ",$all));
    }
} 