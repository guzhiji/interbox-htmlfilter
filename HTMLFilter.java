package interbox.core.util;

import java.util.HashMap;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * a filter to remove unwanted elements or attributes in an html document
 * 
 * @version 0.2.20120103
 * @author Zhiji Gu <gu_zhiji@163.com>
 * @copyright &copy; 2010-2012 InterBox Core 1.2 for Java, GuZhiji Studio
 */
final public class HTMLFilter {

    /**
     * a temporary memory for the reading fragment;
     * here it should store the value of the attribute and 
     * it should be cleared after appended to the result
     */
    private StringBuilder buffer;
    /**
     * a buffer for the final result
     */
    private StringBuilder result;
    /**
     * status showing an open tag is being processed
     */
    private boolean onOpenTag;
    /**
     * status showing a close tag is being processed
     * if true, don't accept any attributes
     */
    private boolean onCloseTag;
    /**
     * status showing attributes of an element are being processed
     */
    private boolean onAtt;
    /**
     * status showing a value of an attribute is being processed
     */
    private boolean onAttValue;
    /**
     * for compression use,
     * if the previous character is a space, following
     * spaces will be omitted
     */
    private boolean wasSpace;
    /**
     * type of the quotation mark that has been detected of the processing
     * attribute value
     * <ul>
     * <li>0 - xxx=xxxx or representing unknown if no value is buffered</li>
     * <li>1 - xxx='xxxx'</li>
     * <li>2 - xxx="xxxx"</li>
     * </ul>
     */
    private int quotType;
    /**
     * attribute name
     */
    private String attName;
    /**
     * tag name
     */
    private String tagName;
    /**
     * configuration for allowed html elements and attributes
     */
    private Map<String, Set<String>> html_config;
    /**
     * configuration for attributes where protocols appear
     */
    private Set<String> protocol_att;
    /**
     * configuration for allowed protocols
     */
    private Set<String> protocol_list;

    /**
     * the constructor
     */
    public HTMLFilter() {
        html_config = new HashMap<String, HashSet<String>>();
        protocol_att = new HashSet<String>();
        protocol_list = new HashSet<String>();

        // default config
        Set<String> attlist;
        attlist = new HashSet<String>();
        attlist.put("href");
        attlist.put("target");
        attlist.put("title");
        html_config.put("a", attlist);
        attlist = new HashSet<String>();
        attlist.put("src");
        attlist.put("width");
        attlist.put("height");
        attlist.put("border");
        attlist.put("alt");
        attlist.put("title");
        html_config.put("img", attlist);
        attlist = new HashSet<String>();
        attlist.put("border");
        attlist.put("width");
        attlist.put("height");
        html_config.put("table", attlist);
        html_config.put("th", new HashSet<String>());
        html_config.put("tr", new HashSet<String>());
        html_config.put("td", new HashSet<String>());
        html_config.put("br", new HashSet<String>());
        html_config.put("p", new HashSet<String>());
        html_config.put("b", new HashSet<String>());
        html_config.put("i", new HashSet<String>());
        html_config.put("strong", new HashSet<String>());
        html_config.put("em", new HashSet<String>());
        html_config.put("h1", new HashSet<String>());
        html_config.put("h2", new HashSet<String>());
        html_config.put("h3", new HashSet<String>());
        html_config.put("h4", new HashSet<String>());
        html_config.put("h5", new HashSet<String>());
        html_config.put("h6", new HashSet<String>());
        attlist = new HashSet<String>();
        attlist.put("face");
        attlist.put("size");
        attlist.put("color");
        html_config.put("font", attlist);

        protocol_att.put("href");
        protocol_att.put("src");

        protocol_list.put("http");
        protocol_list.put("https");
        protocol_list.put("ftp");
        protocol_list.put("mailto");

    }

    /*
     * public HTMLFilter(HTMLFilter_Config configobj) {
     * 	html_config = configobj.html_config;
     * 	protocol_att = configobj.protocol_att;
     * 	protocol_list = configobj.protocol_list;
     * }
     */
    /**
     * convert html entity numbers into characters they represent
     * 
     * note that this method is still dependent on regular expression
     * 
     * @param String s  html with entity numbers
     * @return String   html without entity numbers
     */
    private String decodeHTMLEntities(String html) {
        try {
            StringBuffer str = new StringBuffer();

            //regular expression to capture html entity numbers
            //e.g. &#58; or &#58 or &#x3A; or &#x3A or &#X3A; or &#X3A
            //The ones without semi-colon are not to the standard 
            //but work on browsers.
            Pattern p = Pattern.compile("&#([0-9a-fA-FXx]+);?");
            Matcher m = p.matcher(html);
            int ch;
            while (m.find()) {
                String match = m.group(1);
                if (match.startsWith("x")) {
                    //hex
                    //0xnnnn
                    ch = Integer.decode("0" + match).intValue();
                } else {
                    //dec
                    if (match.startsWith("0")) {
                        //0000058
                        //redundant '0's cause problems
                        int l = match.length();
                        int i;
                        for (i = 1; i < l; i++) {
                            if (match.charAt(i) != '0') {
                                break;
                            }
                        }
                        if (i < l) {
                            //remove '0's
                            match = match.substring(i, l);
                        } else {
                            //0000000
                            match = "0";
                        }
                    }
                    ch = Integer.decode(match).intValue();
                }
                //replace the html entity number with its character
                m.appendReplacement(str, String.valueOf((char) ch));
            }
            m.appendTail(str);

            return str.toString();
        } catch (Exception e) {
            //possibly syntax error
            //simply return an empty string
            return "";
        }
    }

    /**
     * remove a specified character on both sides
     * 
     * @param String value  string to be trimed
     * @param char ch   character to remove if exists on either side of value
     */
    private String trim(String value, char ch) {

        int pos, pos2;
        value = value.trim();//remove spaces
        String str = String.valueOf(ch);//convert to string

        if (value.startsWith(str)) {
            pos = 1;//omit the first character
        } else {
            pos = 0;
        }

        if (value.endsWith(str)) {
            pos2 = value.length() - 1;//omit the last character
        } else {
            pos2 = value.length();
        }
        return value.substring(pos, pos2);

    }

    /**
     * append the bufferd attribute value to the result
     * 
     * when onopentag is false (end of the tag has been reached),
     * the tag is automatically closed by "&gt;"; 
     * when both onopentag and onclosetag are true (empty element), 
     * a "/" is appended
     */
    private void appendAttValue() {

        if (!tagName.isEmpty()) {//if the element is allowed
            if (!attName.isEmpty()) {//if the attribute is allowed
                //format the buffered value
                String value = buffer.toString().trim();
                if (quotType == 2) {
                    value = trim(value, '"');
                } else if (quotType == 1) {
                    value = trim(value, '\'');
                }
                //check the attribute name for protocols
                if (protocol_att.containsKey(attName)) {

                    //decode the value for html entities
                    value = decodeHTMLEntities(value);

                    // int pos = value.indexOf("&#");

                    // if (pos != -1) {

                    // value = "";

                    // } else {

                    //read the protocol name
                    int pos = value.indexOf(":");
                    if (pos > 0) {
                        String protocol = value.substring(0, pos).toLowerCase();

                        // remove invalid chars
                        // e.g. java\tscript:, java script:
                        // Pattern p = Pattern.compile("[^a-zA-Z]");
                        // Matcher m = p.matcher(protocol);
                        // m.replaceAll("");

                        //check for validity
                        if (!protocol_list.containsKey(protocol)) {
                            value = "";
                        }
                    }

                    // }

                    //encode html entities
                    value = value.replaceAll("&", "&amp;");
                    value = value.replaceAll("\"", "&quot;");
                    value = value.replaceAll("<", "&lt;");
                    value = value.replaceAll(">", "&gt;");
                }

                // append value
                result.append("\"").append(value).append("\"");
            }
            //the element is allowed
            //so close the tag if necessary
            if (!onOpenTag) {
                result.append('>');//close the opening tag
                onAtt = false;
            } else if (onCloseTag) {
                result.append(" /");//close by half, wait until a ">" is read
                onAtt = false;
            }
        } else {
            //if the element is not allowed
            if (!onOpenTag || onCloseTag) {
                //"&gt;" and "/" become unnecessary
                //the tag is closed 
                //and the attributes are no longer read
                onAtt = false;
            }
        }

        buffer.delete(0, buffer.length());
        onAttValue = false;
        quotType = 0;
    }

    /**
     * append the bufferd attribute name to the result
     * 
     * when onopentag is false (end of the tag has been reached), the tag is
     * automatically closed by "&gt;"; 
     * when both onopentag and onclosetag are true (empty element), a "/" 
     * is appended; 
     * in both cases above, the tag is closed or closed by half before 
     * an attribute value is read, therefore, according to the standards, 
     * a value is generated, 
     * e.g. 
     * &lt;input type=&quot;radio&quot; name=&quot;id&quot; checked /&gt; 
     * ==&gt; 
     * &lt;input type=&quot;radio&quot; name=&quot;id&quot;
     * checked=&quot;checked&quot;/&gt;; 
     * if the tag is not closed, set onattvalue TRUE to start reading 
     * attribute value
     */
    private void appendAttName() {

        if (!tagName.isEmpty()) {//if the element is allowed

            //format buffered attribute name
            String aname = buffer.toString().trim().toLowerCase();

            //validate the attribute name
            if (html_config.get(tagName).containsKey(aname)) {

                attName = aname;
                //append attribute name
                result.append(' ').append(aname).append('=');

                if (!onOpenTag || onCloseTag) {
                    result.append('"').append(aname).append('"');
                }

            } else {
                //if the attribute is not allowed
                attName = "";
            }

            if (!onOpenTag) {//close the opening tag
                result.append('>');
                onAttValue = false;
                onAtt = false;
            } else if (onCloseTag) {//close the opening tag by half
                result.append(" /");
                onAttValue = false;
                onAtt = false;
            } else {//start reading attribute value
                onAttValue = true;
            }
        }

        buffer.delete(0, buffer.length());

    }

    /**
     * append the bufferd tag name to the result
     * 
     * when onopentag is false (end of the tag has been reached), the tag is
     * automatically closed by "&gt;";
     * when both onopentag and onclosetag are true (empty element), a "/" 
     * is appended; 
     * otherwise, set onatt TRUE to start reading attributes
     */
    private void appendTagName() {
        //format buffered tag/element name
        String tname = buffer.toString().trim().toLowerCase();
        //validate the element
        if (html_config.containsKey(tname)) {

            tagName = tname;
            //append the tag name
            result.append("<").append(tname);

            if (!onOpenTag) {//close the opening tag
                result.append('>');
            } else if (onCloseTag) {//close the opening tag by half
                result.append(" /");
            } else {//start reading attributes
                onAtt = true;
            }

        } else {
            //if the element is not allowed
            tagName = "";
            if (onOpenTag && !onCloseTag) {
                //start reading attributes
                //but will not append them to the result
                //only change status by doing so
                onAtt = true;
            }

        }

        buffer.delete(0, buffer.length());

    }

    /**
     * check if buffer is empty
     * @return boolean
     */
    private boolean isBufferEmpty() {
        return buffer.toString().trim().isEmpty();
    }

    private void processOnAttValue(char current) {

        switch (current) {

            case ' ':
                if (quotType == 0 && !isBufferEmpty()) {
                    // end reading the attribute value
                    // xxx=xxxx xxx=xxxx
                    //         ^
                    appendAttValue();

                } else {// read attribute value
                    // xxx=" xxxx"
                    //      ^
                    // or
                    // xxx="xx xx"
                    //        ^
                    // or
                    // xxx= xxxx
                    //     ^
                    buffer.append(current);
                }
                break;
            case '>':// strict: end reading the attribute and the tag
                // "xxxx>"
                //      ^ supposed to be &gt;
                // or
                // xxx=xxxx>
                //         ^
                onOpenTag = false;
                appendAttValue();
                break;
            case '\'':
                if (quotType == 1) {
                    // end reading the attribute value
                    // 'xxxx'
                    //      ^
                    appendAttValue();

                } else if (quotType == 0 && isBufferEmpty()) {
                    // 'xxxx'
                    // ^
                    quotType = 1;
                } else {
                    // "xx'xx"
                    //    ^
                    buffer.append(current);
                }
                break;
            case '"':
                if (quotType == 2) {
                    // end reading the attribute value
                    // "xxxx"
                    //      ^
                    appendAttValue();

                } else if (quotType == 0 && isBufferEmpty()) {
                    // "xxxx"
                    // ^
                    quotType = 2;
                } else {
                    // 'xx"xx'
                    //    ^
                    buffer.append(current);
                }
                break;
            case '/':
                if (quotType > 0) {
                    // xxx="xx/x"
                    //        ^
                    buffer.append(current);

                } else {
                    // xxx=xxxx/>
                    //         ^
                    onCloseTag = true;
                    appendAttValue();
                }
                break;
            default:// read attribute value
                buffer.append(current);

        }

    }

    private void processOnCloseTag(char current) {

        if (current == '>') {

            // end reading the ending tag
            if (onOpenTag) {
                // <xxx />
                //       ^
                if (!tagName.isEmpty()) {
                    result.append('>');
                }
                onOpenTag = false;
            } else {
                // </xxx>
                //      ^
                String _tagName = buffer.toString().trim().toLowerCase();

                if (html_config.containsKey(_tagName)) {
                    result.append("</").append(_tagName).append(">");
                }
                buffer.delete(0, buffer.length());
            }
            onCloseTag = false;

        } else if (!onOpenTag) {

            // read the ending tag
            // </xxx>
            switch (current) {// skip invalid chars
                case '/':// <//xxx>
                case '"':// </xx"xx>
                case '<':// </xx<xxx>
                case '\r':
                case '\n':
                case '\t':
                case ' ':// </ xx >
                    break;

                default:
                    buffer.append(current);

            }

        }
        // else do nothing
        // <xxx / >
        //       ^
    }

    private void processRestAtt(char current) {

        switch (current) {

            case ' ':
            case '\r':
            case '\n':
            case '\t':
                if (!isBufferEmpty()) {
                    // accept attribute name
                    // xxx = "xxxx"
                    //    ^
                    appendAttName();

                }
                // else do nothing
                // <xxx  >
                //      ^
                // or
                // <xxx  xxx
                //      ^
                break;
            case '>':// end reading the tag
                onOpenTag = false;
                if (!isBufferEmpty()) {
                    // <xxx xxx>
                    //         ^
                    // add default value
                    // <xxx xxx="xxx">
                    appendAttName();
                } else {
                    // <xxx >
                    //      ^
                    onAtt = false;
                    if (!tagName.isEmpty()) {
                        result.append('>');
                    }
                }
                break;
            case '=':// start reading attribute value
                // xxx = "xxxx"
                //     ^
                onAttValue = true;
                appendAttName();
                break;
            case '/':
                onCloseTag = true;
                if (!isBufferEmpty()) {
                    // <xxx xxx/>
                    //         ^
                    appendAttName();
                } else {
                    // <xxx />
                    //      ^
                    onAtt = false;
                }

                break;
            default:// read attribute name
                buffer.append(current);
        }

    }

    private void processRestOpenTag(char current) {

        switch (current) {

            case ' ':
            case '\r':
            case '\n':
            case '\t':

                if (!isBufferEmpty()) {
                    // <xxx xxx="xxxx">
                    //     ^
                    appendTagName();
                }
                // else do nothing
                // < xxx xxx="xxxx">
                //  ^

                break;
            case '/':

                onCloseTag = true;
                if (isBufferEmpty()) {
                    // </xxx>
                    //  ^
                    onOpenTag = false;
                } else {
                    // <xxx/>
                    //     ^
                    appendTagName();
                }

                break;
            case '>':

                // end the starting tag
                onOpenTag = false;
                if (!isBufferEmpty()) {
                    // <xxx>
                    //     ^
                    appendTagName();
                }
                // else do nothing
                // <>
                //  ^

                break;
            default:

                // start reading tag name
                // <xxx xxx="xxxx">
                //  ^
                buffer.append(current);

        }

    }

    private void processRest(char current) {
        switch (current) {
            case ' ':
            case '\r':
            case '\n':
            case '\t':
                if (!wasSpace) {
                    result.append(' ');
                    wasSpace = true;
                }
                break;
            case '<':
                // <xxx>
                // ^
                onOpenTag = true;
                wasSpace = false;
                break;
            default:
                result.append(current);
                wasSpace = false;
        }
    }

    /**
     * filter the input html
     * 
     * @param html  HTML to be processed
     * @return String
     */
    public String filter(String html) {

        // initialize
        buffer = new StringBuilder();
        result = new StringBuilder();
        onOpenTag = false;
        onCloseTag = false;
        onAtt = false;
        onAttValue = false;
        wasSpace = false;
        quotType = 0;
        attName = "";
        tagName = "";

        //iterate characters in the input html
        // char[] allChars = html.toCharArray();
        // for (char ch : allChars) {
        char ch;
        int l = html.length();
        for (int i = 0; i < l; i++) {
            ch = html.charAt(i);
            if (onCloseTag) {
                // </xxx> or .../>
                processOnCloseTag(ch);

            } else if (onOpenTag) {
                // <xxx xxx="xxxx">
                if (onAttValue) {
                    // ..."xxxx"
                    processOnAttValue(ch);

                } else if (onAtt) {
                    // ... xxx="xxxx" xxx="xxxx"
                    processRestAtt(ch);

                } else {
                    //<xxx ...
                    processRestOpenTag(ch);

                }

            } else {
                // </xxx1>...<xxx2>
                processRest(ch);

            }
        }
        return result.toString();
    }
}
