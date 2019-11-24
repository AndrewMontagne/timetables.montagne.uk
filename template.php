<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <META http-equiv="Content-Type" content="text/html">
    <title><?=$stationName;?> Train Times</title>
    <META HTTP-EQUIV="expires" CONTENT="now">
    <META charset="utf-8">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" href="static/webcis.css" type="text/css">
    <style>
        #nrelogo {
            position: fixed;
            right: 10px;
            bottom: 10px;
            width: 480px;
            z-index: -1;
        }
        tr {
            background-color: rgba(0,0,0,0.5);
            text-shadow: 0px 0px 3px black;
        }
    </style>
</head>
<body onload="init_scroll();init_notice();init_refresh();">
<script type="text/javascript" src="static/webcis_scroll.js"></script>
<img src="static/nre.png" id="nrelogo">
<div class="wrapper">
    <div class="heading">
        <div class="departures">
            Departures <span class="station">from <?=$stationName;?></span></div>
        <div class="last_updated">
            Train info updated: <span class="update_time"><?=$generatedTime?></span></div>
        <?php if(count($messages) > 0):?>
        <div class="interleave" id="special_notice">
            <?=implode(" ", $messages); ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="contents">
        <table>
            <tr class="header_small">
                <th class="time">Time</th>
                <th class="dest">Destination</th>
                <th class="plat">Plat</th>
                <th class="incident"></th>
                <th class="exp">Expected</th>
            </tr>
            <?php $i = 0; foreach ($services as $service): ?>
            <tr>
                <td class="time"><?=$service->std;?></td>
                <td class="dest"><?=$service->destination;?></td>
                <td class="plat"><?=$service->platform;?></td>
                <td class="incident"></td>
                <td class="exp on_time"><?=$service->etd;?></td>
            </tr>
            <tr class="bottom_row">
                <td class="indent" id="indent<?=$i;?>"></td>
                <td class="calls_at" colspan="4">
                    <div class="scrollable" id="scroll<?=$i;?>">
                        <?php if(isset($service->via)): ?>
                            <div class="via"><?=$service->via;?></div>
                        <?php endif; ?>
                        <span class="ca_header">Calling at:</span>
                        <?=implode(", ", $service->callingPoints);?>
                        <span class="toc">
                            (<?=$service->toc;?>)
                        </span>
                    </div>
                    <?php if(count($service->notes) > 0):?>
                    <div class="tyrell">
                        Note: <?=implode(" ", $service->notes);?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php $i++; endforeach;?>
    </div>
    </td>
    </tr>
    </table>
</div>
</div>
</body>
</html>
