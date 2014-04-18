<?php

include_once(dirname(__FILE__) . '/../Interfaces/iVideoMetadata.php');

abstract class NfoReader implements iVideoMetadata {

    //each child will parse the file and load their properties into the class
    abstract protected function parseFile();

    protected $doc;

    public function loadFromFile($nfoPath) {
        set_error_handler('NfoReaderHandleError');
        //verify that the file exists
        if (is_string($nfoPath) === false || file_exists($nfoPath) === false) {
            return false;
        }
        $obContents = "";
        //load the nfo file as an xml file 
        $this->doc = new DOMDocument();

        try {
            $loadFileSuccess = $this->doc->load($nfoPath);
        } catch (Exception $e) {
            $loadFileSuccess = false;
        }

        restore_error_handler();
        //if the file was successfully loaded, then we can try to parse the document
        if ($loadFileSuccess) {
            //if the output buffer is not empty, then the document had problems loading the document. 
            return $this->parseFile();
        } else {
            return false;
        }
    }

    /**
     * Shortcut function for retrieving values from tags
     */
    protected function val($tagName, $referenceNode = null) {
        $referenceNode = $referenceNode === null ? $this->doc : $referenceNode;
        return $this->getXmlTagValue($referenceNode, $tagName);
    }

    /**
     * Returns the first element's value found with the specified tag name
     * @param type $doc
     * @param type $tagName
     * @return - the value in the provided tag, or an empty string if the tag was not found
     */
    private function getXmlTagValue($node, $tagName) {
        $elements = $node->getElementsByTagName($tagName);
        if ($elements != null) {
            $item = $elements->item(0);
            if ($item != null) {
                $val = $item->nodeValue;
                if ($val != null) {
                    return $val;
                } else {
                    return "";
                }
            }
        }
    }

}

function NfoReaderHandleError($errno, $errstr, $errfile, $errline) {
    throw new Exception($errstr, 0);
}

?>
