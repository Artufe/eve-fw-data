<?php
require_once("../classes/systems.php");
require_once("classes/collection.php");
require_once("classes/station.php");
date_default_timezone_set('UTC');
header("Content-type:application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die();
}

if (
  !isset($_POST["text"])
) {
  http_response_code(400);
  die();
}

$arrText = explode(' ', trim($_POST["text"]));

if (strtolower($arrText[0]) == 'market') {
  if (strtolower($arrText[1]) == 'collection') {
    $collection = new Collection($_POST["token"], $_POST["channel_id"], $arrText[2]);
    switch ($arrText[3]) {
      case 'add':
        $price = array_pop($arrText);
        $quantity = array_pop($arrText);
        $itemName = $collection->add(implode(' ', array_slice($arrText, 4, count($arrText) - 4)), $quantity, $price);
        if (is_null($itemName)) {
          publicMessage ('No matching item found. Nothing added to collection.');
          die();
        }
        else {
          publicMessage ($quantity. ' ' . $itemName . ' added.');
          die();
        }
        break;
      case 'remove':
        $quantity = array_pop($arrText);
        $itemName = $collection->remove(implode(' ', array_slice($arrText, 4, count($arrText) - 4)), $quantity);
        if (is_null($itemName)) {
          publicMessage ('No matching item found in collection. Nothing removed.');
          die();
        }
        else {
          publicMessage ($quantity. ' ' . $itemName . ' removed.');
          die();
        }
        break;
      case 'empty':
        $collection->removeAll();
        publicMessage ('All items removed.');
        die();
      case 'delete':
        $collection->delete();
        publicMessage ('Collection deleted.');
        die();
      case 'list':
        publicMessage ($collection->getList());
        die();
      default:
        echo ('command '.$arrText[3]. ' not found.');
        die();
        break;
    }
  } 
  elseif (strtolower($arrText[1]) == 'require') {
    $collectionName = $arrText[2];
    $station = implode(' ', array_slice($arrText, 3, count($arrText) - 3));
    $station = new Station($_POST["token"], $_POST["channel_id"], $station);
    if (is_null($station->get())){
      publicMessage ('Station not found.');
      die();
    }

    publicMessage ($station->addOrder($collectionName));
    die();
  }
  elseif (strtolower($arrText[1]) == 'cancel') {
    $collectionName = $arrText[2];
    $station = implode(' ', array_slice($arrText, 3, count($arrText) - 3));
    $station = new Station($_POST["token"], $_POST["channel_id"], $station);
    if (is_null($station->get())){
      publicMessage ('Station not found.');
      die();
    }

    publicMessage ($station->cancelOrder($collectionName));
    die();
  }
  elseif (strtolower($arrText[1]) == 'get') {
    $station = implode(' ', array_slice($arrText, 2, count($arrText) - 2));
    $station = new Station($_POST["token"], $_POST["channel_id"], $station);
    if (is_null($station->get())){
      publicMessage ('Station not found.');
      die();
    }

    $station->cacheMarket();
    die();
  }
  else {
    echo ('command '.$arrText[1]. ' not found.');
    die();
  }
}
else {
  $systems = new Systems();
  foreach ($systems->get()->systems as $index => $system) {
    if (strtolower($system->solarSystemName) == strtolower(trim($_POST["text"]))){
      $percent = $system->victoryPoints / $system->victoryPointThreshold * 100;
      $percent = number_format($percent, 2, '.', '');
      publicMessage (
        $system->solarSystemName. ' is held by the '.$system->occupyingFactionName.' and is '.$percent.'% contested.'
      );
      die();
    }
  }
}

echo ('command or system '.$arrText[0]. ' not found.');

function publicMessage($message){
  echo json_encode((object)[
    "response_type" => "in_channel",
    "text" => $message
  ], JSON_PRETTY_PRINT);
}