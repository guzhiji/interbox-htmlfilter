<%@ Page Language="C#" AutoEventWireup="true" ValidateRequest="false" CodeFile="Default.aspx.cs"
    Inherits="_Default" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title>HTML Filter Test</title>
</head>
<body>
    <form id="form1" runat="server">
    <div>
        <asp:TextBox ID="htmlinput" runat="server" Height="200px" TextMode="MultiLine" Width="40%">&lt;img 
        src=foo.jpg&gt; &lt;img src=&#39;foo.jpg&#39;&gt; &lt;img src=&quot;foo.jpg&quot;&gt; &lt;img src=foo.jpg &gt; &lt;img 
        src=&#39;foo.jpg&#39; &gt; &lt;img src=&quot;foo.jpg&quot; &gt; &lt;img src=&#39;foo.jpg&gt; &lt;img src=&quot;foo.jpg&gt; &lt;img 
        src=&#39;foo.jpg &gt; &lt;img src=&quot;foo.jpg &gt; &lt;img src=foo.jpg&#39;&gt; &lt;img src=foo.jpg&quot;&gt; &lt;img 
        src=foo.jpg&#39; &gt; &lt;img src=foo.jpg&quot; &gt; &lt;img src=foo.jpg onclick=alert(&#39;hi&#39;)&gt; &lt;img 
        src=&#39;foo.jpg&#39; onclick=alert(&#39;hi&#39;)&gt; &lt;img src=&quot;foo.jpg&quot; onclick=alert(&#39;hi&#39;)&gt; &lt;img 
        src=&#39;foo.jpg&#39;onclick=alert(&#39;hi&#39;)&gt; &lt;img src=&quot;foo.jpg&quot;onclick=alert(&#39;hi&#39;)&gt; &lt;img 
        src=&#39;foo.jpg&#39;\tonclick=&#39;alert(&#39;hi&#39;)&#39;&gt; &lt;img 
        src=&quot;foo.jpg&quot;\x00onclick=&quot;alert(&#39;hi&#39;)&quot;&gt; &lt;script&gt;a()&lt;/script&gt; 
        &lt;&lt;script&gt;a()&lt;/script&gt; &lt;&lt;script&gt;&gt;a()&lt;/script&gt; &lt;&lt;script&gt;a()&lt;/script&gt;&gt; &lt;!-- &lt; 
        --&gt;script&gt;a()&lt;/script&gt; &lt;script&gt;&lt;&lt;/script&gt;script&gt;a()&lt;/script&gt; &lt;&lt;script&gt;script&gt;&gt; 
        &lt;&lt;script&lt;script&gt;&gt; &lt;a href=&quot;http://foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&quot;https://foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;ftp://foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&quot;mailto:foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;javascript:foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&quot;java script:foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;java\tscript:foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&quot;java\nscript:foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;java\rscript:foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&quot;java\x00script:foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;jscript:foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&quot;vbscript:foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;jAvAsCrIpT:foo&quot;&gt;bar&lt;/a&gt; &lt;a href=&lt;script&gt;ja&lt;/script&gt;vascript:foo&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;&amp;#106;&amp;#97;&amp;#118;&amp;#97;&amp;#115;&amp;#99;&amp;#114;&amp;#105;&amp;#112;&amp;#116;&amp;#57;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#0000106;&amp;#0000097;&amp;#0000118;&amp;#0000097;&amp;#0000115;&amp;#0000099;&amp;#0000114;&amp;#0000105;&amp;#0000112;&amp;#0000116;&amp;#0000057;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#x6A;&amp;#x61;&amp;#x76;&amp;#x61;&amp;#x73;&amp;#x63;&amp;#x72;&amp;#x69;&amp;#x70;&amp;#x74;&amp;#x30;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#X6A;&amp;#X61;&amp;#X76;&amp;#X61;&amp;#X73;&amp;#X63;&amp;#X72;&amp;#X69;&amp;#X70;&amp;#X74;&amp;#X30;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#106;&amp;#97;&amp;#118;&amp;#97;&amp;#115;&amp;#99;&amp;#114;&amp;#105;&amp;#112;&amp;#116;&amp;#58;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#0000106;&amp;#0000097;&amp;#0000118;&amp;#0000097;&amp;#0000115;&amp;#0000099;&amp;#0000114;&amp;#0000105;&amp;#0000112;&amp;#0000116;&amp;#0000058;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#x6A;&amp;#x61;&amp;#x76;&amp;#x61;&amp;#x73;&amp;#x63;&amp;#x72;&amp;#x69;&amp;#x70;&amp;#x74;&amp;#x3A;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a 
        href=&quot;&amp;#X6A;&amp;#X61;&amp;#X76;&amp;#X61;&amp;#X73;&amp;#X63;&amp;#X72;&amp;#X69;&amp;#X70;&amp;#X74;&amp;#X3A;foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a href=&quot;&amp;#106&amp;#97&amp;#118&amp;#97&amp;#115&amp;#99&amp;#114&amp;#105&amp;#112&amp;#116&amp;#58;foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;&amp;#0000106&amp;#0000097&amp;#0000118&amp;#0000097&amp;#0000115&amp;#0000099&amp;#0000114&amp;#0000105&amp;#0000112&amp;#0000116&amp;#0000058foo&quot;&gt;bar&lt;/a&gt; 
        &lt;a href=&quot;&amp;#x6A&amp;#x61&amp;#x76&amp;#x61&amp;#x73&amp;#x63&amp;#x72&amp;#x69&amp;#x70&amp;#x74&amp;#x3A;foo&quot;&gt;bar&lt;/a&gt; &lt;a 
        href=&quot;&amp;#X6A&amp;#X61&amp;#X76&amp;#X61&amp;#X73&amp;#X63&amp;#X72&amp;#X69&amp;#X70&amp;#X74&amp;#X3Afoo&quot;&gt;bar&lt;/a&gt;</asp:TextBox>
        <asp:TextBox ID="htmloutput" runat="server" Height="200px" TextMode="MultiLine" Width="40%"></asp:TextBox>
    </div>
    <p>
        <asp:Button ID="Button1" runat="server" OnClick="Button1_Click" Text="Button" />
        <asp:Label ID="Label1" runat="server" Text="Execution Time=0 ms"></asp:Label>
    </p>
    </form>
</body>
</html>
