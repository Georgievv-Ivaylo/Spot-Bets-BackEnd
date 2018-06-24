<?php
require_once '../../db.php';
$data = [];

$markets = [
	'match_results' => 'match_result',
	'correct_scores' => 'correct_score',
	'goals' => 'goal',
	'penalties' => 'penalty'
];

$query = '
  SELECT soccer.id,
         soccer.home,
         soccer.away,
         soccer.event_time,
         soccer.match_results,
         soccer.position_match_result,
         soccer.correct_scores,
         soccer.position_correct_score,
         soccer.goals,
         soccer.position_goal,
         soccer.penalties,
         soccer.position_penalty
    FROM soccer
   WHERE soccer.status != "close"
';
$stmt = $db->prepare($query);
$stmt->execute([]);
$soccerRaw = $stmt->fetchAll();
$data['Football'] = groupData($soccerRaw, $markets, 'Football', $db);

$query = '
  SELECT tennis.id,
         tennis.home,
         tennis.away,
         tennis.event_time,
         tennis.match_results,
         tennis.position_match_result,
         tennis.correct_scores,
         tennis.position_correct_score
    FROM tennis
   WHERE tennis.status != "close"
';
$stmt = $db->prepare($query);
$stmt->execute();
$tennisRaw = $stmt->fetchAll();
$data['Tennis'] = groupData($tennisRaw, $markets, 'Tennis', $db);

function groupData($extractData, $markets, $titleData, $db) {
	$collectedData = [];
	$jsData = [];
	if ($extractData && count($extractData) >= 1) {
		foreach ($extractData as $key => $value) {
			$value['title'] = $titleData;
			if (empty($collectedData[$value['id']]['mainInfo'])) $collectedData[$value['id']]['mainInfo'] = $value;
			foreach ($markets as $marketK => $marketV) {
				if (!empty($value[$marketK])) {
					$thisMarket = [];
					$thisMarket['position'] = $value['position_'. $marketV];
					$thisMarket['id'] = $marketK;
					$thisMarket['elements'] = getMarket($marketK, $marketV .'_id', $value[$marketK], $db);
					$collectedData[$value['id']]['market'][] = $thisMarket;
				}
			}
		}

		foreach ($collectedData as $key => $value) {
			$jsData[] = $value;
		}
	}
	return $jsData;
}

function getMarket($market, $marketIdName, $marketId, $db) {
	$query = '
	  SELECT '. $market .'.id,
	         '. $market .'.'. $marketIdName .',
	         '. $market .'.participant,
	         '. $market .'.odds,
	         '. $market .'.description,
	         '. $market .'.position
	    FROM '. $market .'
	   WHERE '. $market .'.'. $marketIdName .' = ?
	';
	$stmt = $db->prepare($query);
	$stmt->execute([$marketId]);
	$bets = $stmt->fetchAll();
	if (count($bets) >= 1) return $bets;
	return $bets;
}


echo json_encode($data);
// header('Content-Type: application/json');
// echo json_encode($data);
