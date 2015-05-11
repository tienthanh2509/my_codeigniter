<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Đăng ký</title>

        <style type="text/css">

            ::selection { background-color: #E13300; color: white; }
            ::-moz-selection { background-color: #E13300; color: white; }

            body {
                background-color: #fff;
                margin: 40px;
                font: 13px/20px normal Helvetica, Arial, sans-serif;
                color: #4F5155;
            }

            a {
                color: #003399;
                background-color: transparent;
                font-weight: normal;
            }

            h1 {
                color: #444;
                background-color: transparent;
                border-bottom: 1px solid #D0D0D0;
                font-size: 19px;
                font-weight: normal;
                margin: 0 0 14px 0;
                padding: 14px 15px 10px 15px;
            }

            code {
                font-family: Consolas, Monaco, Courier New, Courier, monospace;
                font-size: 12px;
                background-color: #f9f9f9;
                border: 1px solid #D0D0D0;
                color: #002166;
                display: block;
                margin: 14px 0 14px 0;
                padding: 12px 10px 12px 10px;
            }

            #body {
                margin: 0 15px 0 15px;
            }

            p.footer {
                text-align: right;
                font-size: 11px;
                border-top: 1px solid #D0D0D0;
                line-height: 32px;
                padding: 0 10px 0 10px;
                margin: 20px 0 0 0;
            }

            #container {
                margin: 10px;
                border: 1px solid #D0D0D0;
                box-shadow: 0 0 8px #D0D0D0;
            }
        </style>
    </head>
    <body>

        <div id="container">
            <h1>Đăng ký tài khoản</h1>

            <div id="body">
                <table width="100%" border="0">
                    <tr>
                        <td width="75%"><form method="post" enctype="application/x-www-form-urlencoded" name="form_dang_ky" id="form_dang_ky" title="Đăng ký" accept-charset="UTF-8">
                                <fieldset>
                                    <legend>Đăng ký</legend>
                                    <?php
                                    if (!empty($error))
                                    {
                                        foreach ($error as $value)
                                        {
                                            echo '<p class="bg-danger">' . $value . '</p>';
                                        }
                                    }
                                    ?>
                                    <?php echo validation_errors(); ?>
                                    <table width="85%" border="0">
                                        <tr>
                                            <td width="33%">Tên đăng nhập:</td>
                                            <td width="67%"><div align="right">
                                                    <input name="user_name" type="text" id="user_name" placeholder="Bí danh" value="<?php echo set_value('user_name'); ?>" size="50">
                                                </div></td>
                                        </tr>
                                        <tr>
                                            <td>Email:</td>
                                            <td><div align="right">
                                                    <input name="user_email" type="email" id="user_email" placeholder="Đại chỉ email" value="<?php echo set_value('user_email'); ?>" size="50">
                                                </div></td>
                                        </tr>
                                        <tr>
                                            <td>Mật khẩu:</td>
                                            <td><div align="right">
                                                    <input name="user_password_new" type="password" id="user_password_new" placeholder="Mật khẩu" value="<?php echo set_value('user_password_new'); ?>" size="50">
                                                </div></td>
                                        </tr>
                                        <tr>
                                            <td>Nhập lại mật khẩu:</td>
                                            <td><div align="right">
                                                    <input name="user_password_repeat" type="password" id="user_password_repeat" placeholder="Xác nhận lại mật khẩu đã nhập" value="<?php echo set_value('user_password_repeat'); ?>" size="50">
                                                </div></td>
                                        </tr>
                                        <tr>
                                            <td><p>Captcha</p></td>
                                            <td><div align="right">
                                                    <?php echo $recaptcha_body; ?>
                                                </div></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><div align="right">
                                                    <input type="submit" name="btn_submit" id="btn_submit" value="Đăng ký">
                                                </div></td>
                                        </tr>
                                    </table>
                                </fieldset>
                            </form></td>
                        <td width="25%"><fieldset>
                                <legend>OpenID</legend>
                            </fieldset></td>
                    </tr>
                </table>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds.</p>
        </div>

        <?php echo $recaptcha_script; ?>
    </body>
</html>