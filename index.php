<?php
require_once "OpenLDBWS.php";

date_default_timezone_set('Europe/London');

if(!isset($_GET["crs"])) {
    echo "Please specify the crs as a query parameter.";
    die();
}

$rows = 10;
if(isset($_GET["rows"])) {
    $rows = (int)$_GET["rows"];
}

function parseService($serviceData) {
    $service = new stdClass();
    $service->std = $serviceData->std;
    $service->etd = $serviceData->etd;
    $service->toc = $serviceData->operator;
    $service->realtime = strtotime("$service->std today");


    if($serviceData->serviceType == "train") {
        $service->platform = isset($serviceData->platform) ? $serviceData->platform : "";
    } else {
        $service->platform = strtoupper($serviceData->serviceType);
    }

    $service->origin = $serviceData->origin->location->locationName;
    $service->destination = $serviceData->destination->location->locationName;
    if(isset($serviceData->destination->location->via)) {
        $service->via = $serviceData->destination->location->via;
    }
    $service->notes = [];
    if(isset($serviceData->length)){
        $service->notes[] = "This train is formed of $serviceData->length coaches.";
    }
    if(isset($serviceData->delayReason)){
        $service->notes[] = $serviceData->delayReason . ".";
    }

    $service->callingPoints = [];
    if(isset($serviceData->subsequentCallingPoints)) {
        if (is_array($serviceData->subsequentCallingPoints->callingPointList->callingPoint)) {
            foreach ($serviceData->subsequentCallingPoints->callingPointList->callingPoint as $callingPointData) {
                $service->callingPoints[] = $callingPointData->locationName . " (" . $callingPointData->st . ")";
            }
        } else {
            $service->callingPoints[] = $serviceData->subsequentCallingPoints->callingPointList->callingPoint->locationName . " (" . $serviceData->subsequentCallingPoints->callingPointList->callingPoint->st . ")";
        }
    }

    return $service;
}

$ldbws = new OpenLDBWS("TOKEN HERE");

$data = $ldbws->GetDepBoardWithDetails($rows,$_GET["crs"])->GetStationBoardResult;

$stationName = $data->locationName;

$generatedTime = date("H:i", strtotime($data->generatedAt));

$messages = [];

if(isset($data->nrccMessages)) {
    if(is_array($data->nrccMessages->message)) {
        foreach ($data->nrccMessages->message as $messageData) {
            $messages[] = rtrim(strip_tags($messageData->_), ".") . ".";
        }
    } else {
        $messages[] = rtrim(strip_tags($data->nrccMessages->message->_), ".") . ".";
    }
}

$services = [];

if(isset($data->trainServices)) {
    if(is_array($data->trainServices->service)) {
        foreach ($data->trainServices->service as $serviceData) {
            $services[] = parseService($serviceData);
        }
    } else {
        $services[] = parseService($data->trainServices->service);
    }
}
if(isset($data->busServices)) {
    if(is_array($data->busServices->service)) {
        foreach ($data->busServices->service as $serviceData) {
            $services[] = parseService($serviceData);
        }
    } else {
        $services[] = parseService($data->busServices->service);
    }
}
if(isset($data->ferryServices)) {
    if(is_array($data->ferryServices->service)) {
        foreach ($data->ferryServices->service as $serviceData) {
            $services[] = parseService($serviceData);
        }
    } else {
        $services[] = parseService($data->ferryServices->service);
    }
}

function cmpservices($s1, $s2) {
    if ($s1->realtime == $s2->realtime) {
        return 0;
    }
    return ($s1->realtime < $s2->realtime) ? -1 : 1;
}

usort($services, "cmpservices");

if(isset($_GET["debug"])) {
    header("Content-Type: application/json");
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    include "template.php";
}

