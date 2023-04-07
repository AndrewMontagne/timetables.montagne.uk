<?php
require_once "OpenLDBWS.php";

date_default_timezone_set('Europe/London');

if(!isset($_GET["crs"])) {
    include "station_picker.php";
}

$rows = 10;
if(isset($_GET["rows"])) {
    $rows = (int)$_GET["rows"];
}

function forceArray($obj) {
    if(is_array($obj)) {
        return $obj;
    } else {
        return [$obj];
    }
}

function togFormation($formData) {
    $carriages = [];

    $unitIndex = 0;

    $lastUnitLetter = "A";

    foreach($formData as $formCar) {
        $letter = substr($formCar->number, 0, 1);
        if(ctype_alpha($letter) && $letter != $lastUnitLetter) {
            $unitIndex++;
            $lastUnitLetter = $letter;
        }

        $carriage = new stdClass();
        $carriage->class = $formCar->coachClass;
        $carriage->hasToilet = FALSE;

        if (isset($formCar->toilet)) {
            $carriage->hasToilet = $formCar->toilet->_!="None";
            $carriage->toiletType = $formCar->toilet->_;
            $carriage->toiletInService = $formCar->toilet->status=="InService";
        }

        $carriage->loading = 0;
        if (isset($formCar->loading)) {
            $carriage->loading = $formCar->loading;
        }

        $carriage->unit = $unitIndex;

        $carriages[] = $carriage;
    }
    return $carriages;
}

function terminals($darwinTerminals) {
    $darwinTerminals = forceArray($darwinTerminals);

    $terminalsOut = [];

    foreach($darwinTerminals as $darwinTerminal) {
        $terminal = new stdClass();
        $terminal->crs = $darwinTerminal->crs;
        $terminal->name = $darwinTerminal->locationName;
        if(isset($darwinTerminal->via)) {
            $terminal->via = $darwinTerminal->via;
        }

        $terminalsOut[] = $terminal;
    }

    return $terminalsOut;
}

function callingPoints($callingPortionsRaw) {
    $callingPortions = forceArray($callingPortionsRaw);

    $portionsOutList = [];

    foreach($callingPortions as $portionIndex=>$portion) {

        $assoc = new stdClass();
        $assoc->serviceType = $portion->serviceType;
        $assoc->changeRequired = $portion->serviceChangeRequired;
        $assoc->isCancelled = $portion->assocIsCancelled;
        $assoc->callingAt = [];

        $callingPointList = forceArray($portion->callingPoint);

        foreach($callingPointList as $callingPointData) {
            if (!isset($callingPointData->et) || $callingPointData->et == "On time") {
                $dt = $callingPointData->st;
            } else {
                $dt = $callingPointData->et;
            }

            $assoc->callingAt[] = $callingPointData->locationName . " (" . $dt . ")";
        }

        $portionsOutList[] = $assoc;
    }

    return $portionsOutList;
}

function parseService($serviceData) {
    $service = new stdClass();
    $service->std = $serviceData->std;
    $service->etd = $serviceData->etd;
    $service->toc = $serviceData->operator;
    $service->realtime = strtotime("$service->std today");

    if(isset($serviceData->formation->coaches)){
        $service->formation = togFormation($serviceData->formation->coaches->coach);
    }

    if(isset($serviceData->length)) {
        $service->length = $serviceData->length;
    } else if (isset($service->formation)) {
        $service->length = count($service->formation);
    }

    if($serviceData->serviceType == "train") {
        $service->platform = isset($serviceData->platform) ? $serviceData->platform : "";
    } else {
        $service->platform = strtoupper($serviceData->serviceType);
    }

    $service->origins = terminals($serviceData->origin->location);
    $service->destinations = terminals($serviceData->destination->location);

    $service->notes = [];
    if(isset($service->length)){
        $service->notes[] = "This train is formed of $service->length coaches.";
    }

    if(isset($serviceData->cancelReason)){
        $service->notes[] = $serviceData->cancelReason . ".";
    }

    if(isset($serviceData->delayReason)){
        $service->notes[] = $serviceData->delayReason . ".";
    }

    $service->callingPoints = [];
    if(isset($serviceData->subsequentCallingPoints)) {
        $service->callingPoints = callingPoints($serviceData->subsequentCallingPoints->callingPointList);
    }

    return $service;
}

$ldbws = new OpenLDBWS(file_get_contents('access_token.txt'));

$data = $ldbws->GetDepBoardWithDetails($rows,strtoupper($_GET["crs"]))->GetStationBoardResult;

$stationName = $data->locationName;

$generatedTime = date("H:i", strtotime($data->generatedAt));

$messages = [];

if(isset($data->nrccMessages)) {
    $messagesRaw = forceArray($data->nrccMessages->message);

    foreach ($messagesRaw as $messageData) {
        $messages[] = rtrim(str_ireplace("more details can be found in latest travel news", "", strip_tags($messageData->_)), ". ") . ".";
    }
}

$services = [];

if(isset($data->trainServices)) {
    $trainServicesRaw = forceArray($data->trainServices->service);

    foreach ($trainServicesRaw as $serviceData) {
        $services[] = parseService($serviceData);
    }
}

if(isset($data->busServices)) {
    $busServicesRaw = forceArray($data->busServices->service);

    foreach ($busServicesRaw as $serviceData) {
        $services[] = parseService($serviceData);
    }
}

if(isset($data->ferryServices)) {
    $ferryServicesRaw = forceArray($data->ferryServices->service);

    foreach ($ferryServicesRaw as $serviceData) {
        $services[] = parseService($serviceData);
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

