using System;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Xml.Linq;
using InterBox.Core.Util;
public partial class _Default : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {

    }
    protected void Button1_Click(object sender, EventArgs e)
    {
        HTMLFilter filter = new HTMLFilter();
        System.Diagnostics.Stopwatch t = new System.Diagnostics.Stopwatch();
        t.Start();
        htmloutput.Text = filter.filter(htmlinput.Text);
        t.Stop();
        Label1.Text = "Execution Time=" + t.ElapsedMilliseconds + " ms";
    }
}
