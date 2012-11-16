
<%@page contentType="text/html" pageEncoding="UTF-8"%>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>HTML Filter Test</title>
    </head>
    <body>
        <jsp:useBean id="filter" scope="page" class="interbox.core.util.HTMLFilter" />
        <%
            long start = 0;
            String s = null;
            request.setCharacterEncoding("utf-8");
            if (request.getMethod().equalsIgnoreCase("post")) {
                s = request.getParameter("htmlinput");
            }
        %>
        <form method="post">
            <div>
                <textarea name="htmlinput" style="width: 40%;"><%
                    if (s != null) {
                    %><%=s.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;")%><%
                        }
                    %></textarea>
                <textarea name="htmloutput" style="width: 40%;"><%
                    if (s != null) {
                        start = System.currentTimeMillis();
                    %><%=filter.filter(s).replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;")%><%
                        }
                    %></textarea>
            </div>
            <div>
                <input type="submit" />  execution time=<%=start > 0 ? System.currentTimeMillis() - start : 0%>
            </div>
        </form>
    </body>
</html>
