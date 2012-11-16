/**************************************************************************
 * copyright: &copy; 2010-2012 InterBox Core 1.2 for C#, GuZhiji Studio
 * author: Zhiji Gu <gu_zhiji@163.com>
 * version: 0.3.20120103
 **************************************************************************/
using System;
using System.Collections.Generic;
using System.Text;
using System.Text.RegularExpressions;
using System.Collections;
using System.Web;

namespace InterBox.Core.Util
{
	/// <summary>
	///  a filter to remove unwanted elements or attributes in an html document
	/// </summary>
	public sealed class HTMLFilter
	{
		/// <summary>
		/// a temporary memory for the reading fragment; 
		/// here it should store the value of the attribute and 
		/// it should be cleared after appended to the result
		/// </summary>
		private StringBuilder buffer;

		/// <summary>
		/// a buffer for the final result
		/// </summary>
		private StringBuilder result;

		/// <summary>
		/// status showing if an open tag is being processed
		/// </summary>
		private bool onOpenTag;

		/// <summary>
		/// status showing a close tag is being processed
		/// if true, don't accept any attributes
		/// </summary>
		private bool onCloseTag;

		/// <summary>
		/// status showing attributes of an element are being processed
		/// </summary>
		private bool onAtt;

		/// <summary>
		/// status showing a value of an attribute is being processed 
		/// </summary>
		private bool onAttValue;

		/// <summary>
		/// for compression use
		/// if the previous character is a space, following spaces will be omitted
		/// </summary>
		private bool wasSpace;

		/// <summary>
		/// type of the quotation mark that has been detected of the processing attribute value 
		/// <ul>
		/// <li>0 - xxx=xxxx or representing unknown if no value is buffered</li>
		/// <li>1 - xxx='xxxx'</li>
		/// <li>2 - xxx="xxxx"</li>
		/// </ul>
		/// </summary>
		private int quotType;

		/// <summary>
		/// attribute name
		/// </summary>
		private string attName;

		/// <summary>
		/// tag name 
		/// </summary>
		private string tagName;

		/// <summary>
		/// configuration for allowed html elements and attributes 
		/// </summary>
		private Dictionary<string, Dictionary<string, bool>> html_config;

		/// <summary>
		/// configuration for attributes where protocols appear 
		/// </summary>
		private Dictionary<string, bool> protocol_att;

		/// <summary>
		/// configuration for allowed protocols
		/// </summary>
		private Dictionary<string, bool> protocol_list;

		/// <summary>
		/// the constructor 
		/// </summary>
		public HTMLFilter ()
		{
			html_config = new Dictionary<string, Dictionary<string, bool>> ();
			protocol_att = new Dictionary<string, bool> ();
			protocol_list = new Dictionary<string, bool> ();
			
			Dictionary<string, bool> attlist;
			
			//default config
			attlist = new Dictionary<string, bool> ();
			attlist.Add ("href", true);
			attlist.Add ("target", true);
			attlist.Add ("title", true);
			html_config.Add ("a", attlist);
			
			attlist = new Dictionary<string, bool> ();
			attlist.Add ("src", true);
			attlist.Add ("width", true);
			attlist.Add ("height", true);
			attlist.Add ("border", true);
			attlist.Add ("alt", true);
			attlist.Add ("title", true);
			html_config.Add ("img", attlist);
			
			attlist = new Dictionary<string, bool> ();
			attlist.Add ("border", true);
			attlist.Add ("width", true);
			attlist.Add ("height", true);
			html_config.Add ("table", attlist);
			
			html_config.Add ("th", new Dictionary<string, bool> ());
			html_config.Add ("tr", new Dictionary<string, bool> ());
			html_config.Add ("td", new Dictionary<string, bool> ());
			html_config.Add ("br", new Dictionary<string, bool> ());
			html_config.Add ("p", new Dictionary<string, bool> ());
			html_config.Add ("b", new Dictionary<string, bool> ());
			html_config.Add ("i", new Dictionary<string, bool> ());
			html_config.Add ("strong", new Dictionary<string, bool> ());
			html_config.Add ("em", new Dictionary<string, bool> ());
			html_config.Add ("h1", new Dictionary<string, bool> ());
			html_config.Add ("h2", new Dictionary<string, bool> ());
			html_config.Add ("h3", new Dictionary<string, bool> ());
			html_config.Add ("h4", new Dictionary<string, bool> ());
			html_config.Add ("h5", new Dictionary<string, bool> ());
			html_config.Add ("h6", new Dictionary<string, bool> ());
			
			attlist = new Dictionary<string, bool> ();
			attlist.Add ("face", true);
			attlist.Add ("size", true);
			attlist.Add ("color", true);
			html_config.Add ("font", attlist);
			
			protocol_att.Add ("href", true);
			protocol_att.Add ("src", true);
			
			protocol_list.Add ("http", true);
			protocol_list.Add ("https", true);
			protocol_list.Add ("ftp", true);
			protocol_list.Add ("mailto", true);
			
		}
		/*
		public HTMLFilter (HTMLFilter_Config config)
		{
			html_config = config.html_config;
			protocol_att = config.protocol_att;
			protocol_list = config.protocol_list;
		}
		 */

		/// <summary>
		/// append the bufferd attribute value to the result
		/// 
		/// When onopentag is false (end of the tag has been reached), 
		/// the tag is automatically closed by "&gt;";
		/// when both onopentag and onclosetag are true (empty element), a "/" is appended 
		/// </summary>
		private void AppendAttValue ()
		{
			
			if (!tagName.Equals ("")) {
				//if the element is allowed
				if (!attName.Equals ("")) {
					//if the attribute is allowed
					//format the buffered value
					string avalue = buffer.ToString ().Trim ();
					if (quotType == 2) {
						avalue = avalue.Trim ('"');
					} else if (quotType == 1) {
						avalue = avalue.Trim ('\'');
					}
					//check the attribute name for protocols
					if (protocol_att.ContainsKey (attName)) {
						
						//decode the value for html entities
						//avalue = decodeHTMLEntities (avalue);
						avalue = HttpUtility.HtmlDecode (avalue);
						
						//check if successful
						//&#nnnn(;) without semi-colon is not supported by .NET HttpUtility.HtmlDecode()
						//but supported by mainstream browsers
						int pos = avalue.IndexOf ("&#");
						
						if (pos != -1) {
							//simply clear the value because few may give such entities
							//hackers more often do so
							avalue = "";
							
						} else {
							//read the protocol name
							pos = avalue.IndexOf (":");
							if (pos > 0) {
								string protocol = avalue.Substring (0, pos).ToLower ();
								
								//remove invalid chars
								//e.g. java\tscript:, java script:
								//Regex reg = new Regex("[^a-zA-Z]");
								//protocol = reg.Replace(protocol, "");
								
								//check for validity
								if (!protocol_list.ContainsKey (protocol)) {
									avalue = "";
								}
							}
							
						}
						// encode again
						//avalue = avalue.Replace ("&", "&amp;");
						//avalue = avalue.Replace ("\"", "&quot;");
						//avalue = avalue.Replace ("<", "&lt;");
						//avalue = avalue.Replace (">", "&gt;");
						
						avalue = HttpUtility.HtmlEncode (avalue);
						
					}
					
					//append value
					result.Append ("\"" + avalue + "\"");
				}
				//the element is allowed
				//so close the tag if necessary
				if (!onOpenTag) {
					result.Append ('>');
					//close the opening tag
					// end the starting tag
					onAtt = false;
				} else if (onCloseTag) {
					result.Append (" /");
					//close by half, wait until a ">" is read
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
			
			buffer.Remove (0, buffer.Length);
			onAttValue = false;
			quotType = 0;
		}

		/// <summary>
		/// append the bufferd attribute name to the result
		/// 
		/// when onopentag is false (end of the tag has been reached), 
		/// the tag is automatically closed by "&gt;";
		/// when both onopentag and onclosetag are true (empty element), a "/" is appended;
		/// in both cases above, the tag is closed or closed by half before 
		/// an attribute value is read, therefore, according to the standards,
		/// a value is generated, 
		/// e.g. 
		/// &lt;input type=&quot;radio&quot; name=&quot;id&quot; checked /&gt;
		/// ==&gt;
		/// &lt;input type=&quot;radio&quot; name=&quot;id&quot; checked=&quot;checked&quot; /&gt;;
		/// if the tag is not closed, set onattvalue TRUE to start reading attribute value
		/// </summary>
		private void AppendAttName ()
		{
			
			if (!tagName.Equals ("")) {
				//if the element is allowed
				//format buffered attribute name
				string aname = buffer.ToString ().Trim ().ToLower ();
				//validate the attribute name
				if (html_config[tagName].ContainsKey (aname)) {
					
					attName = aname;
					//append attribute name
					result.Append (" " + aname + "=");
					
					if (!onOpenTag || onCloseTag) {
						result.Append ("\"" + aname + "\"");
					}
					
				} else {
					
					//if the attribute is not allowed
					attName = "";
					
				}
				
				if (!onOpenTag) {
					//close the opening tag
					result.Append (">");
					onAttValue = false;
					onAtt = false;
				} else if (onCloseTag) {
					//close the opening tag by half
					result.Append (" /");
					onAttValue = false;
					onAtt = false;
				} else {
					//start reading attribute value
					onAttValue = true;
				}
			}
			
			buffer.Remove (0, buffer.Length);
			
		}

		/// <summary>
		/// append the bufferd tag name to the result
		/// 
		/// when onopentag is false (end of the tag has been reached), 
		/// the tag is automatically closed by "&gt;";
		/// when both onopentag and onclosetag are true (empty element), a "/" is appended;
		/// otherwise, set onatt TRUE to start reading attributes
		/// </summary>
		private void AppendTagName ()
		{
			
			//format buffered tag/element name
			string tname = buffer.ToString ().Trim ().ToLower ();
			//validate the element
			if (html_config.ContainsKey (tname)) {
				
				tagName = tname;
				//append the tag name
				result.Append ("<" + tname);
				
				if (!onOpenTag) {
					//close the opening tag
					result.Append ('>');
				} else if (onCloseTag) {
					//close the opening tag by half
					result.Append (" /");
				} else {
					//start reading attributes
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
			
			buffer.Remove (0, buffer.Length);
			
		}

		/// <summary>
		/// check if buffer is empty
		/// </summary>
		/// <returns>
		/// A <see cref="System.Boolean"/>, true if it's empty
		/// </returns>
		private bool isBufferEmpty ()
		{
			return buffer.ToString ().Trim ().Equals ("");
		}

		private void processOnAttValue (char current)
		{
			
			switch (current) {
			
			case ' ':
				if (quotType == 0 && !isBufferEmpty ()) {
					// end reading the attribute value
					// xxx=xxxx xxx=xxxx
					//         ^
					AppendAttValue ();
					
				} else {
					// read attribute value
					// xxx=" xxxx"
					//      ^
					// or
					// xxx="xx xx"
					//        ^
					// or
					// xxx= xxxx
					//     ^
					buffer.Append (current);
				}
				break;
			case '>':
				// strict: end reading the attribute and the tag
				// "xxxx>"
				//      ^ supposed to be &gt;
				// or
				// xxx=xxxx>
				//         ^
				onOpenTag = false;
				AppendAttValue ();
				break;
			case '\'':
				if (quotType == 1) {
					// end reading the attribute value
					// 'xxxx'
					//       ^
					AppendAttValue ();
					
				} else if (quotType == 0 && isBufferEmpty ()) {
					// 'xxxx'
					// ^
					quotType = 1;
				} else {
					// "xx'xx"
					//    ^
					//  xx'xx
					//    ^
					buffer.Append (current);
				}
				break;
			case '"':
				if (quotType == 2) {
					// end reading the attribute value
					// "xxxx"
					//      ^
					AppendAttValue ();
					
				} else if (quotType == 0 && isBufferEmpty ()) {
					// "xxxx"
					// ^
					quotType = 2;
				} else {
					// 'xx"xx'
					//    ^
					// or
					// xx"xx
					//   ^
					buffer.Append (current);
				}
				break;
			case '/':
				if (quotType > 0) {
					// xxx="xx/x"
					//        ^
					buffer.Append (current);
					
				} else {
					// xxx=xxxx/>
					//         ^
					onCloseTag = true;
					AppendAttValue ();
				}
				break;
			default:
				// read attribute value
				buffer.Append (current);
				break;
				
			}
			
		}

		private void processOnCloseTag (char current)
		{
			
			if (current == '>') {
				
				// end reading the ending tag
				if (onOpenTag) {
					// <xxx />
					//       ^
					if (!tagName.Equals (""))
						result.Append ('>');
					onOpenTag = false;
				} else {
					// </xxx>
					//      ^
					string _tagName = buffer.ToString ().Trim ().ToLower ();
					
					if (html_config.ContainsKey (_tagName))
						result.Append ("</" + _tagName + ">");
					buffer.Remove (0, buffer.Length);
				}
				onCloseTag = false;
				
			} else if (!onOpenTag) {
				
				// read the ending tag
				// </xxx>
				switch (current) {
				// skip invalid chars
				case '/':
				// <//xxx>
				case '"':
				// </xx"xx>
				case '<':
				// </xx<xxx>
				case '\r':
				case '\n':
				case '\t':
				case ' ':
					// </ xx >
					break;
				default:
					
					buffer.Append (current);
					break;
					
				}
				
			}
			// else do nothing
			// <xxx / >
			//       ^
		}

		private void processRestAtt (char current)
		{
			
			switch (current) {
			
			case ' ':
			case '\r':
			case '\n':
			case '\t':
				if (!isBufferEmpty ()) {
					// accept attribute name
					// xxx = "xxxx"
					//    ^
					AppendAttName ();
					
				}
				// else do nothing
				// <xxx  >
				//      ^
				// or
				// <xxx  xxx
				//      ^
				break;
			case '>':
				// end reading the tag
				onOpenTag = false;
				if (!isBufferEmpty ()) {
					// <xxx xxx>
					//         ^
					// add default value
					// <xxx xxx="xxx">
					AppendAttName ();
				} else {
					// <xxx >
					//      ^
					onAtt = false;
					if (!tagName.Equals ("")) {
						result.Append ('>');
					}
				}
				break;
			case '=':
				// start reading attribute value
				// xxx = "xxxx"
				//     ^
				onAttValue = true;
				AppendAttName ();
				break;
			case '/':
				onCloseTag = true;
				if (!isBufferEmpty ()) {
					// <xxx xxx/>
					//         ^
					AppendAttName ();
				} else {
					// <xxx />
					//      ^
					onAtt = false;
				}
				
				break;
			default:
				// read attribute name
				buffer.Append (current);
				break;
			}
			
		}

		private void processRestOpenTag (char current)
		{
			
			switch (current) {
			
			case ' ':
			case '\r':
			case '\n':
			case '\t':
				
				if (!isBufferEmpty ()) {
					// <xxx xxx="xxxx">
					//     ^
					AppendTagName ();
				}
				// else do nothing
				// < xxx xxx="xxxx">
				//  ^
				
				break;
			case '/':
				
				onCloseTag = true;
				if (isBufferEmpty ()) {
					// </xxx>
					//  ^
					onOpenTag = false;
				} else {
					// <xxx/>
					//     ^
					AppendTagName ();
				}
				
				break;
			case '>':
				
				// end the starting tag
				onOpenTag = false;
				if (!isBufferEmpty ()) {
					// <xxx>
					//     ^
					AppendTagName ();
				}
				// else do nothing
				// <>
				//  ^
				
				break;
			default:
				
				// start reading tag name
				// <xxx xxx="xxxx">
				//  ^
				buffer.Append (current);
				break;
				
			}
			
		}

		private void processRest (char current)
		{
			switch (current) {
			case ' ':
			case '\r':
			case '\n':
			case '\t':
				if (!wasSpace) {
					result.Append (' ');
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
				result.Append (current);
				wasSpace = false;
				break;
			}
		}

		/// <summary>
		/// filter the input html
		/// </summary>
		/// <param name="html">
		/// A <see cref="System.String"/>, user input html
		/// </param>
		/// <returns>
		/// A <see cref="System.String"/>, filtered html
		/// </returns> 
		public string filter (string html)
		{
			
			// initialize
			buffer = new StringBuilder ();
			result = new StringBuilder ();
			onOpenTag = false;
			onCloseTag = false;
			onAtt = false;
			onAttValue = false;
			wasSpace = false;
			quotType = 0;
			attName = "";
			tagName = "";
			
			//iterate characters in the input html
			foreach (char ch in html) {
				if (onCloseTag) {
					// </xxx> or .../>
					processOnCloseTag (ch);
					
				} else if (onOpenTag) {
					// <xxx xxx="xxxx">
					if (onAttValue) {
						// ..."xxxx"
						processOnAttValue (ch);
						
					} else if (onAtt) {
						// ... xxx="xxxx" xxx="xxxx"
						processRestAtt (ch);
						
					} else {
						//<xxx ...
						processRestOpenTag (ch);
						
					}
					
				} else {
					// </xxx1>...<xxx2>
					processRest (ch);
					
				}
			}
			return result.ToString ();
		}
	}
}
