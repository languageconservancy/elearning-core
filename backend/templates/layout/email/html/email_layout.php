<?php

use Cake\Core\Configure;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <title><?= $this->fetch('title') ?></title>
    </head>
    <body>
        <table align="center" border="0" cellpadding="0"
            cellspacing="0" style="padding:0; margin:0; background-color:#ecedee" width="100%">
            <tbody>
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0"
                            dir="ltr" style="max-width:680px; margin-left:auto;
                                margin-right:auto; width:100%; border-collapse:collapse;
                                border-spacing:0; table-layout:fixed" width="100%">
                            <tbody>
                                <tr>
                                    <td style="text-align:center; width:680px;
                                        color:#333f48;background-color:#ffffff;
                                        font-family:'Open Sans',Calibri,Arial,sans-serif;border-top-style:solid;">
                                        <table border="0" cellpadding="0" cellspacing="0" dir="ltr"
                                            style="max-width:100%;margin-left:auto;margin-right:auto;
                                                width:100%;border-collapse:collapse;border-spacing:0;
                                                table-layout:fixed" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center;width:100%;
                                                        padding-top:20px;padding-bottom:20px;
                                                        padding-left:40px;padding-right:40px;
                                                        color:#333f48;
                                                        font-family:'Open Sans',Calibri,Arial,sans-serif">
                                                        <a href="<?= Configure::read('FROENTEND_LINK'); ?>" target="_blank" style="text-decoration: none;">
                                                            <img src="<?= Configure::read('FROENTEND_LINK') . 'assets/images/icon_02.png'; ?>" alt="<?= Configure::read('App.name'); ?> Logo"
                                                                style="max-width:50px; height:auto; display:block; margin:0 auto;" />
                                                            <h2 style="margin-top:10px;"><?= Configure::read('App.name'); ?></h2>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <table border="0" cellpadding="0" cellspacing="0" height="141"
                                            style="max-width:100%;margin-left:auto;margin-right:auto;
                                                border-collapse:collapse;border-spacing:0px;
                                                table-layout:fixed" width="680">
                                            <tbody>
                                                <?= $this->fetch('content') ?>
                                            </tbody>
                                        </table>
                                        <table border="0" cellpadding="0" cellspacing="0" dir="ltr"
                                            style="max-width:100%;margin-left:auto;margin-right:auto;
                                                width:100%;border-collapse:collapse;border-spacing:0;
                                                table-layout:fixed;background: #047eb9;" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align:center;width:100%;padding-top:5px;
                                                        padding-bottom:0px;color:#fff;
                                                        font-family:'Open Sans',Calibri,Arial,sans-serif">
                                                        <table border="0" cellpadding="0" cellspacing="0"
                                                            height="50" style="border-collapse:collapse;
                                                                border-spacing:0px;margin-top:10px" width="665">
                                                            <tbody>
                                                                <tr style="font-size:10px;line-height:10px">
                                                                    <td style="text-align:center;font-size:10px;
                                                                        line-height:10px;padding-bottom:7px;
                                                                        color:#fff;
                                                                        font-family:'Open Sans',Calibri,Arial,
                                                                            sans-serif">
                                                                        <p>
                                                                            <span style="font-size:11px">
                                                                                Copyright © <?php echo date('Y'); ?>
                                                                                <?= Configure::read('App.name'); ?>.
                                                                            </span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
