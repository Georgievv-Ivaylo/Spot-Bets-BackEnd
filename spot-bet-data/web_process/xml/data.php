<?php
require_once '../../db.php';

$xml = simplexml_load_file('../../xml_upload/UpcomingEvents.xml');

$toTable = [
  'TennisEvent' => 'tennis',
  'FootballEvent' => 'soccer',
  'Match Result' => 'match_results',
  'Match Result ID' => 'match_result',
  'Correct Score Sets' => 'correct_scores',
  'Correct Score Sets ID' => 'correct_score',
  'Correct Score' => 'correct_scores',
  'Correct Score ID' => 'correct_score',
  'Total Goals' => 'goals',
  'Total Goals ID' => 'goal',
  'Penalty in Match' => 'penalties',
  'Penalty in Match ID' => 'penalty'
];

$toColumn = [
  'ID' => 'id',
  'EventTime' => 'event_time',
  'Home' => 'home',
  'Away' => 'away',
  'Number' => 'position',
  'OddsDecimal' => 'odds',
  'Participant' => 'participant',
  'Description' => 'description'
];

foreach ($xml as $xmlK => $xmlV) {
  $mainTable = [];
  $table = $toTable[$xmlK];
  $mainTable['table'] = $table;
  $mainTable['props'] = '';
  $mainTable['length'] = '';
  foreach ($xmlV->attributes() as $xmlAttrK => $xmlAttrV) {
    if (!empty($mainTable['props'])) $mainTable['props'] .= ',';
    $mainTable['props'] .= $mainTable['table'] .'.'. $toColumn[$xmlAttrK];
    if (!empty($mainTable['length'])) $mainTable['length'] .= ',';
    $mainTable['length'] .= '?';
    $mainTable['values'][] = (string) $xmlAttrV;
  }

  foreach ($xmlV as $xmlSubK => $xmlSubV) {
    $thisTable = [];

    if (empty($thisTable)) {
      foreach ($xmlSubV->attributes() as $xmlAttrK => $xmlAttrV) {
        if ($xmlAttrK === 'Name') {
          $thisTable['table'] = $toTable[(string) $xmlAttrV];
          $thisTable['foreignId'] = $toTable[(string) $xmlAttrV .' ID'];
        } else {
          $thisTable[$toColumn[$xmlAttrK]] = (string) $xmlAttrV;
        }
      }
    }
    $thisTable['props'] = $mainTable['props'] .','. $mainTable['table'] .'.'. $thisTable['table'] .','. $mainTable['table'] .'.position_'. $thisTable['foreignId'];
    $thisTable['length'] = $mainTable['length'] .',?,?';
    $thisTable['values'] = $mainTable['values'];
    $thisTable['values'][] = $thisTable['id'];
    $thisTable['values'][] = $thisTable['position'];
    foreach ($xmlSubV as $xmlSubSubK => $xmlSubSubV) {
      $insertTable = [];
      $insertTable['table'] = $thisTable['table'];
      $insertTable['props'] = $thisTable['foreignId']. '_id';
      $insertTable['props'] .= ',game_type';
      $insertTable['length'] = '?,?';
      $insertTable['values'][] = $thisTable['id'];
      $insertTable['values'][] = $mainTable['table'];
      foreach ($xmlSubSubV->attributes() as $xmlAttrK => $xmlAttrV) {
        $insertTable[$toColumn[$xmlAttrK]] = (string) $xmlAttrV;
        if (!empty($mainTable['props'])) $insertTable['props'] .= ',';
        $insertTable['props'] .= $insertTable['table'] .'.'. $toColumn[$xmlAttrK];
        if (!empty($mainTable['length'])) $insertTable['length'] .= ',';
        $insertTable['length'] .= '?';
        $insertTable['values'][] = (string) $xmlAttrV;
      }

      insertData($insertTable['table'], $insertTable['props'], $insertTable['length'], $insertTable['values'], $db);
    }
    insertData($mainTable['table'], $thisTable['props'], $thisTable['length'], $thisTable['values'], $db);
  }
}

function insertData(string $table, string $tableProps, string $tablePropsLength, array $tablePropsValues, $db) {
	$query = '
    INSERT
	    INTO '. $table .' (
		         '. $tableProps .'
           ) VALUES ('. $tablePropsLength .')
	';
	$values = $tablePropsValues;
	$stmt = $db->prepare($query);
	$stmt->execute($values);
}

header('Content-Type: application/json');
echo json_encode($xml);
