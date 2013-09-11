<?php

abstract class NfoReader {

    protected $doc;

    public function loadFromFile($nfoPath) {
        //verify that the file exists
        if (file_exists($nfoPath) === false) {
            return false;
        }
        //load the nfo file as an xml file 
        $this->doc = new DOMDocument();
        $success = $this->doc->load($nfoPath);
        $this->parseFile();
        if ($success == false) {
            //fail gracefully, since we will just use dummy information
            return false;
        } else {
            return true;
        }
    }

    //each child will parse the file and load their properties into the class
    abstract protected function parseFile();

    /**
     * Shortcut function for retrieving values from tags
     */
    protected function val($tagName) {
        return $this->getXmlTagValue($this->doc, $tagName);
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
?>
