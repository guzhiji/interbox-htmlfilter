<?php

/**
 * a filter to remove unwanted elements or attributes in an html document
 * 
 * @version 0.9.20120103
 * @author Zhiji Gu <gu_zhiji@163.com>
 * @copyright &copy; 2010-2012 InterBox Core 1.2 for PHP, GuZhiji Studio
 * @package interbox.core.util
 */
class HTMLFilter {

    /**
     * configuration for allowed html elements and attributes
     * array(
     *     [element name]=>array(
     *         [attribute name],
     *         ...
     *     ),
     *     ...
     * )
     * @var array
     */
    private $html_config = array(
        "a" => array("href", "target", "title"),
        "img" => array("src", "border", "title", "alt", "width", "height"),
        "table" => array("border", "width", "height"),
        "tr" => array(),
        "td" => array("width", "height"),
        "th" => array("width", "height"),
        "br" => array(),
        "p" => array(),
        "b" => array(),
        "strong" => array(),
        "i" => array(),
        "em" => array(),
        "font" => array("face", "color", "size"),
        "h1" => array(),
        "h2" => array(),
        "h3" => array(),
        "h4" => array(),
        "h5" => array(),
        "h6" => array()
    );

    /**
     * configuration for allowed protocols and attributes where protocols appear
     * array(
     *     array(
     *         [attribute name],
     *         ...
     *     ),
     *     array(
     *         [protocol name],
     *         ...
     *     )
     * )
     * @var array
     */
    private $protocol_config = array(
        array(
            "src",
            "href"
        ),
        array(
            "http",
            "https",
            "ftp",
            "mailto"
        )
    );

    /**
     * the constructor
     * @param array $html_config
     * @param array $protocol_config 
     */
    function __construct($html_config=NULL, $protocol_config=NULL) {
        if (!empty($html_config))
            $this->html_config = $html_config;
        if (!empty($protocol_config))
            $this->protocol_config = $protocol_config;
    }

    /**
     * append the bufferd attribute value to the result
     * 
     * when onopentag is false (end of the tag has been reached), 
     * the tag is automatically closed by "&gt;";
     * when both onopentag and onclosetag are true (empty element), a "/" is appended
     * 
     * @param string $buffer    a temporary memory for the reading fragment; 
     *      here it should store the value of the attribute and 
     *      it should be cleared after appended to the result
     * @param string $result    a buffer for the final result
     * @param string $tagname   tag name
     * @param string $attname   attribute name
     * @param boolean $onopentag    status showing an open tag is being processed
     * @param boolean $onclosetag   status showing a close tag is being processed
     *      if true, don't accept any attributes
     * @param boolean $onatt    status showing attributes of an element are being processed
     * @param boolean $onattvalue   status showing a value of an attribute is being processed
     * @param int $quottype     type of the quotation mark that has been detected of the processing attribute value
     *      <ul>
     *      <li>0 - xxx=xxxx or showing unknown if no value is buffered</li>
     *      <li>1 - xxx='xxxx'</li>
     *      <li>2 - xxx="xxxx"</li>
     *      </ul>
     */
    private function appendAttValue(&$buffer, &$result, $tagname, $attname, $onopentag, $onclosetag, &$onatt, &$onattvalue, &$quottype) {
        if ($tagname != "") {//if the element is allowed
            if ($attname != "") {//if the attribute is allowed
                //format the buffered value
                $value = trim($buffer);
                if ($quottype == 2)
                    $value = trim($value, "\"");
                else if ($quottype == 1)
                    $value = trim($value, "'");

                //check the attribute name for protocols
                if (in_array($attname, $this->protocol_config[0])) {

                    //decode the value for html entities
                    $value = html_entity_decode($value);

                    //check if successful
                    //&#nnnn(;) without semi-colon is not supported by php html_entity_decode()
                    //but supported by mainstream browsers
                    $pos = strpos($value, "&#");
                    if ($pos !== FALSE) {
                        //simply clear the value because few may give such entities
                        //hackers more often do so
                        $value = "";
                    } else {
                        //read the protocol name
                        $pos = strpos($value, ":");
                        if ($pos) {//pos>0
                            $protocol = strtolower(substr($value, 0, $pos));
                            //check for validity
                            if (!in_array($protocol, $this->protocol_config[1])) {
                                $value = "";
                            }
                        }
                    }
                    //encode html entities
                    //issue: multi-byte string
                    //$value = htmlentities($value);
                    $value = str_replace("&", "&amp;", $value);
                    $value = str_replace("\"", "&quot;", $value);
                    $value = str_replace("<", "&lt;", $value);
                    $value = str_replace(">", "&gt;", $value);
                }

                //append value
                $result.="\"{$value}\"";
            }
            //the element is allowed
            //so close the tag if necessary
            if (!$onopentag) {
                $result.=">"; //close the opening tag
                $onatt = FALSE;
            } else if ($onclosetag) {
                $result.=" /"; //close by half, wait until a ">" is read
                $onatt = FALSE;
            }
        } else {
            //if the element is not allowed
            if (!$onopentag || $onclosetag) {
                //"&gt;" and "/" become unnecessary
                //the tag is closed 
                //and the attributes are no longer read
                $onatt = FALSE;
            }
        }

        $buffer = "";

        $onattvalue = FALSE;
        $quottype = 0;
    }

    /**
     * append the bufferd attribute name to the result
     * 
     * when onopentag is false (end of the tag has been reached), 
     * the tag is automatically closed by "&gt;";
     * when both onopentag and onclosetag are true (empty element), a "/" is appended;
     * in both cases above, the tag is closed or closed by half before 
     * an attribute value is read, therefore, according to the standards,
     * a value is generated, 
     * e.g. 
     * &lt;input type=&quot;radio&quot; name=&quot;id&quot; checked /&gt;
     * ==&gt;
     * &lt;input type=&quot;radio&quot; name=&quot;id&quot; checked=&quot;checked&quot; /&gt;;
     * if the tag is not closed, set onattvalue TRUE to start reading attribute value
     * 
     * @param string $buffer
     * @param string $result
     * @param string $tagname
     * @param string $attname
     * @param bool $onopentag
     * @param bool $onclosetag
     * @param bool $onatt
     * @param bool $onattvalue 
     * @see appendAttValue()
     */
    private function appendAttName(&$buffer, &$result, $tagname, &$attname, $onopentag, $onclosetag, &$onatt, &$onattvalue) {
        if ($tagname != "") {//if the element is allowed
            //format buffered attribute name
            $_attname = strtolower(trim($buffer));
            //validate the attribute name
            if (in_array($_attname, $this->html_config[$tagname])) {

                $attname = $_attname;
                //append attribute name
                $result.=" {$_attname}=";

                if (!$onopentag || $onclosetag) {
                    $result.="\"{$_attname}\"";
                }
            } else {
                //if the attribute is not allowed
                $attname = "";
            }
            if (!$onopentag) {//close the opening tag
                $result.=">";
                $onattvalue = FALSE;
                $onatt = FALSE;
            } else if ($onclosetag) {//close the opening tag by half
                $result.=" /";
                $onattvalue = FALSE;
                $onatt = FALSE;
            } else {//start reading attribute value
                $onattvalue = TRUE;
            }
        }
        $buffer = "";
    }

    /**
     * append the bufferd tag name to the result
     * 
     * when onopentag is false (end of the tag has been reached), 
     * the tag is automatically closed by "&gt;";
     * when both onopentag and onclosetag are true (empty element), a "/" is appended;
     * otherwise, set onatt TRUE to start reading attributes
     * 
     * @param string $buffer
     * @param string $result
     * @param string $tagname
     * @param bool $onopentag
     * @param bool $onclosetag
     * @param bool $onatt 
     * @see appendAttValue()
     */
    private function appendTagName(&$buffer, &$result, &$tagname, $onopentag, $onclosetag, &$onatt) {
        //format buffered tag/element name
        $_tagname = strtolower(trim($buffer));
        //validate the element
        if (array_key_exists($_tagname, $this->html_config)) {
            $tagname = $_tagname;
            //append the tag name
            $result.="<" . $_tagname;

            if (!$onopentag) {//close the opening tag
                $result.=">";
            } elseif ($onclosetag) {//close the opening tag by half
                $result.=" /";
            } else {//start reading attributes
                $onatt = TRUE;
            }
        } else {
            //if the element is not allowed
            $tagname = "";
            if ($onopentag && !$onclosetag) {
                //start reading attributes
                //but will not append them to the result
                //only change status by doing so
                $onatt = TRUE;
            }
        }
        $buffer = "";
    }

    /**
     * filter the input html
     * 
     * @param string $html  HTML to be processed
     * @return string 
     */
    public function filter($html) {

        //initialize
        $onopentag = FALSE;
        $onclosetag = FALSE;
        $onatt = FALSE;
        $onattvalue = FALSE;
        $wasspace = FALSE;
        $quottype = 0;
        $attname = "";
        $tagname = "";
        $result = "";
        $buffer = "";

        //iterate chars
        //SOLUTION 1
        //$html=preg_split("/(?<!^)(?!$)/u",$html);
        //foreach ($html as $char) {
        //
        //SOLUTION 2 memory-consuming
        //$html = str_split($html);
        //foreach ($html as $char) {
        //
        //SOLUTION 3
        $l = strlen($html);
        for ($i = 0; $i < $l; ++$i) {
            $char = substr($html, $i, 1);

            if ($onclosetag) {
                if ($char == ">") {
                    //end reading the closing tag
                    if ($onopentag) {
                        //<xxx />
                        //      ^
                        if ($tagname != "")
                            $result.=">";
                        $onopentag = FALSE;
                    }else {
                        //</xxx>
                        //     ^
                        $_tagname = strtolower(trim($buffer));
                        if (array_key_exists($_tagname, $this->html_config))
                            $result.="</{$_tagname}>"; //append the closing tag
                        $buffer = "";
                    }
                    $onclosetag = FALSE;
                }else if (!$onopentag) {
                    //read the closing tag
                    //</xxx>
                    switch ($char) {//skip invalid chars
                        case "/"://<//xxx>
                        case "\""://</xx"xx>
                        case "<"://</xx<xxx>
                        case "\r":
                        case "\n":
                        case "\t":
                        case " "://</ xx >
                            break;
                        default:
                            $buffer.=$char;
                    }
                }
                //else do nothing
                //<xxx / >
                //      ^
            } else if ($onopentag) {//<xxx xxx="xxxx">
                if ($onattvalue) {// "xxxx"
                    switch ($char) {
                        case ">"://strict: end reading the attribute and the tag
                            // "xxxx>"
                            //      ^ supposed to be &gt;
                            //or
                            // xxx=xxxx>
                            //         ^
                            $onopentag = FALSE;
                            $this->appendAttValue(
                                    $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue, $quottype
                            );
                            break;
                        case "'":
                            if ($quottype == 1) {
                                //end reading the attribute value
                                // 'xxxx'
                                //      ^
                                $this->appendAttValue(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue, $quottype
                                );
                            } else if ($quottype == 0 && trim($buffer) == "") {
                                // 'xxxx'
                                // ^
                                $quottype = 1;
                            } else {
                                // "xx'xx"
                                //    ^
                                $buffer.=$char;
                            }
                            break;
                        case "\"":
                            if ($quottype == 2) {
                                //end reading the attribute value
                                // "xxxx"
                                //      ^
                                $this->appendAttValue(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue, $quottype
                                );
                            } else if ($quottype == 0 && trim($buffer) == "") {
                                // "xxxx"
                                // ^
                                $quottype = 2;
                            } else {
                                //'xx"xx'
                                //   ^
                                $buffer.=$char;
                            }
                            break;
                        case "/":
                            if ($quottype > 0) {
                                // xxx="xx/x"
                                //        ^
                                $buffer.=$char;
                            } else {
                                // xxx=xxxx/>
                                //         ^
                                $onclosetag = TRUE;
                                $this->appendAttValue(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue, $quottype
                                );
                            }
                            break;
                        case " ":
                            if ($quottype == 0 && trim($buffer) != "") {
                                //end reading the attribute value
                                // xxx=xxxx xxx=xxxx
                                //         ^
                                $this->appendAttValue(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue, $quottype
                                );
                            } else {//read attribute value
                                // xxx=" xxxx"
                                //      ^
                                //or
                                // xxx="xx xx"
                                //        ^
                                //or
                                // xxx= xxxx
                                //     ^
                                $buffer.=$char;
                            }
                            break;
                        default://read attribute value
                            $buffer.=$char;
                    }
                } else if ($onatt) {// xxx = "xxxx"
                    switch ($char) {
                        case " ":
                        case "\r":
                        case "\n":
                        case "\t":
                            if ($buffer != "") {
                                //accept attribute name
                                // xxx = "xxxx"
                                //    ^
                                $this->appendAttName(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue
                                );
                            }
                            //else do nothing
                            //<xxx  >
                            //     ^
                            //or
                            //<xxx  xxx
                            //     ^
                            break;
                        case ">"://end reading the tag
                            $onopentag = FALSE;
                            if (trim($buffer) != "") {
                                //<xxx xxx>
                                //        ^
                                //add default value
                                //<xxx xxx="xxx">
                                $this->appendAttName(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue
                                );
                            } else {
                                //<xxx >
                                //     ^
                                $onatt = FALSE;
                                if ($tagname != "") {
                                    $result.=">";
                                }
                            }
                            break;
                        case "="://start reading attribute value
                            // xxx = "xxxx"
                            //     ^
                            $onattvalue = TRUE;
                            $this->appendAttName(
                                    $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue
                            );
                            break;
                        case "/":
                            $onclosetag = TRUE;
                            if ($buffer != "") {
                                //<xxx xxx/>
                                //        ^
                                $this->appendAttName(
                                        $buffer, $result, $tagname, $attname, $onopentag, $onclosetag, $onatt, $onattvalue
                                );
                            } else {
                                //<xxx />
                                //     ^
                                $onatt = FALSE;
                            }

                            break;
                        default://read attribute name
                            $buffer.=$char;
                    }
                } else {//<xxx ...
                    switch ($char) {
                        case " ":
                        case "\r":
                        case "\n":
                        case "\t":

                            if (trim($buffer) != "") {
                                //<xxx xxx="xxxx">
                                //    ^
                                $this->appendTagName(
                                        $buffer, $result, $tagname, $onopentag, $onclosetag, $onatt
                                );
                            }
                            //else do nothing
                            //< xxx xxx="xxxx">
                            // ^
                            break;
                        case "/":

                            $onclosetag = TRUE;
                            if (trim($buffer) == "") {
                                //</xxx>
                                // ^
                                $onopentag = FALSE;
                            } else {
                                //<xxx/>
                                //    ^
                                $this->appendTagName(
                                        $buffer, $result, $tagname, $onopentag, $onclosetag, $onatt
                                );
                            }
                            break;
                        case ">":

                            //end the opening tag
                            $onopentag = FALSE;
                            if (trim($buffer) != "") {
                                //<xxx>
                                //    ^
                                $this->appendTagName(
                                        $buffer, $result, $tagname, $onopentag, $onclosetag, $onatt
                                );
                            }
                            //else do nothing
                            //<>
                            // ^
                            break;
                        default:

                            //start reading tag name
                            //<xxx xxx="xxxx">
                            // ^
                            $buffer.=$char;
                    }
                }
            } else {
                switch ($char) {
                    case " ":
                    case "\r":
                    case "\n":
                    case "\t":
                        if (!$wasspace) {//for compression
                            $result.=" ";
                            $wasspace = TRUE;
                        }
                        break;
                    case "<":
                        //<xxx>
                        //^
                        $onopentag = TRUE;
                        $wasspace = FALSE;
                        break;
                    default:
                        $result.=$char;
                        $wasspace = FALSE;
                }
            }
        }
        return $result;
    }

}

?>
