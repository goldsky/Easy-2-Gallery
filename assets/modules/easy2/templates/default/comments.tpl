<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=[+easy2:charset+]">
        <title>[+easy2:title+]</title>
        <style type="text/css">
            html, body {margin:0;padding:0;}
            body {background:#fff}
            h2, h3 {font:bold 14px Arial;color:#6FAD21}
            h2 {color:#ED1807;}
            input, textarea, td, div {font:11px Arial; color:#535353;}
            div.e2com_container,div.row0,div.row1 {margin:0 0 10px 0; padding:2px; border:1px solid #E4E4E4;}
            textarea {width:100%}
            .row1 {background:#F7F7F7;}
            .pnums {font:11px Tahoma; color:gray;text-align:center; margin-top:10px;}
            .pnums a {background:#F4F4F4;border:1px solid #E8E8E8;color:gray; text-decoration:none;color:#7D7D7D;}
            .pnums a:hover {background:#B3EC6C;border:1px solid #83D71C;color:white;text-decoration:none}
            .pnums a, .pnums b {padding:2px 4px;}
            .captcha_cell {vertical-align:top;}
            .captcha_cell img {position:absolute;}
        </style>
    </head>
    <body>
        [+easy2:comment_body+]
        [+easy2:comment_pages+]
        <form action="" method="post">
            <div class="e2com_container">
                <table cellspacing="0" cellpadding="2" border="0" width="98%">
                    <tr>
                        <td colspan="4"><b>[+easy2:comment_add+]</b> ( [+easy2:waitforapproval+] )</td>
                    </tr>
                    <tr>
                        <td width="40"><b>[+easy2:name+]:</b></td>
                        <td><input name="name" type="text"></td>
                        <td width="40"><b>[+easy2:email+]:</b></td>
                        <td><input name="email" type="text"></td>
                    </tr>
                    <tr>
                        <td colspan="4"><b>[+easy2:usercomment+]:</b><br><textarea name="comment" rows="3" cols="100%"></textarea></td>
                    </tr>
                    <tr>
                        <td>[+easy2:recaptcha+]</td>
                    </tr>
                    <tr>
                        <td colspan="4"><input type="submit" value="[+easy2:send_btn+]"></td>
                    </tr>
                </table>
            </div>
            <input type="hidden" name="ip_address">
        </form>
    </body>
</html>