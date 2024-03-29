<!doctype HTML>
<html>
<head>
    <title><?=$stationName;?> Train Times</title>
    <META HTTP-EQUIV="expires" CONTENT="now">
    <META charset="utf-8">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <link rel="stylesheet" href="static/webcis.css" type="text/css">
    <style>
        #nrelogo {
            position: absolute;
            right: 10px;
            bottom: 10px;
            width: 480px;
            background-color: #0006;
        }
        tr {
            background-color: #000;
        }
    </style>
</head>
<body onload="init_scroll();init_notice();init_refresh();">
<script type="text/javascript" src="static/webcis_scroll.js"></script>

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
                <td class="dest">
                    <?php foreach($service->destinations as $destination) { ?>
                    <div>
                        <span><?= $destination->name ?></span>
                        <?php if(isset($destination->via)): ?>
                            <div class="via"><?=$destination->via;?></div>
                        <?php endif; ?>
                    </div>
                    <?php } ?>
                </td>
                <td class="plat"><?=$service->platform;?></td>
                <td class="incident"></td>
                <td class="exp <?php
                    switch ($service->etd) {
                        case "Cancelled":
                            echo("cancelled");
                            break;
                        case "On time":
                            echo("on-time");
                            break;
                        case "Delayed":
                            echo("delayed");
                            break;
                    } ?>"><span><?=$service->etd;?></span></td>
            </tr>
            <tr class="bottom_row">
                <td class="indent" id="indent<?=$i;?>"></td>
                <td class="calls_at" colspan="4">
                    <div class="scrollable" id="scroll<?=$i;?>">
                        <?php foreach($service->callingPoints as $idx=>$callingService) { ?>
                        <div>
                        <span class="ca_header">Calling at:</span>
                        <?=implode(", ", $callingService->callingAt);?>
                        <?php if($idx==0): ?>
                        <span class="toc">
                            (<?=$service->toc;?>)
                        </span>
                        <?php endif; ?>
                        </div>
                        <?php } ?>
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
<div>
    <img src="static/nre.png" id="nrelogo">
</div>
</body>
</html>
