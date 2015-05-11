<!DOCTYPE html>
<!--
Phạm Tiến Thành
tienthanh.dqc@gmail.com
+841679227582
-->
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Đăng nhập vào hệ thống</title>

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
            <h1>Đăng nhập</h1>

            <div id="body">
                <table width="100%" border="0">
                    <tr>
                        <td width="74%"><fieldset>
                                <form id="form_dang_nhap" name="form_dang_nhap" method="post">
                                    <legend>Đăng nhập</legend>
                                    <?php
                                    if (!empty($error)) {
                                        foreach ($error as $value) {
                                            echo '<p class="bg-danger">' . $value . '</p>';
                                        }
                                    }
                                    ?>
                                    <?php echo validation_errors(); ?>
                                    <table width="400" border="0">
                                        <tr>
                                            <td>Tên tài khoản</td>
                                            <td><input type="text" name="user_name" id="user_name" value="<?php echo set_value('user_name'); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td>Mật khẩu</td>
                                            <td><input type="password" name="user_password" id="user_password" value="<?php echo set_value('user_password'); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td><input type="checkbox" name="user_rememberme" id="user_rememberme" <?php echo set_checkbox('user_rememberme'); ?>>
                                                <label for="user_rememberme">Nhớ đăng nhập trong 2 tuần</label></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td><input type="submit" name="btn_submit" id="btn_submit" value="Đăng nhập"></td>
                                        </tr>
                                    </table>
                                </form>
                                <legend></legend>
                            </fieldset>
                        </td>
                        <td width="26%">&nbsp;</td>
                    </tr>
                </table>
                <p>&nbsp;</p>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo (ENVIRONMENT === 'development') ? 'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
        </div>

    </body>
</html>