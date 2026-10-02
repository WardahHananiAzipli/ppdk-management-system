<?php

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Kuala_Lumpur');

/* ===================== Helper ===================== */
function json_ok($payload) {
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function json_error($message, $code = 500) {
  http_response_code($code);
  echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function columnExists(mysqli $db, string $table, string $column): bool {
  $sql = "SELECT 1 
          FROM information_schema.COLUMNS 
          WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
          LIMIT 1";
  $stmt = $db->prepare($sql);
  if (!$stmt) return false;
  $stmt->bind_param('ss', $table, $column);
  $stmt->execute();
  $stmt->store_result();
  $exists = $stmt->num_rows > 0;
  $stmt->close();
  return $exists;
}

/* ===================== DB ===================== */
$mysqli = @new mysqli('localhost', 'root', '', 'ppdk_db');
if ($mysqli->connect_error) json_error('Ralat DB: ' . $mysqli->connect_error, 500);
$mysqli->set_charset('utf8mb4');

/* ===================== Params ===================== */
$type       = isset($_GET['type']) ? trim($_GET['type']) : 'table';
$trainee_id = isset($_GET['trainee_id']) ? trim($_GET['trainee_id']) : '';
$week       = isset($_GET['week']) ? trim($_GET['week']) : '';

/* ===================== Columns (daripada jadual trainee_performance) ===================== */
$columns = [
  'performance_id','trainee_id','week',
  'roll_left_right','reach_forward','sit','crawl','creep','kneel','stand',
  'climb_stairs','descend_stairs',
  'swallow_safely','wash_hands_before_after','bring_food_to_mouth',
  'eat_with_hand','eat_with_spoon','drink_with_cup',
  'clean_utensils','arrange_utensils','identify_front_back',
  'remove_clothing','put_on_clothes','put_on_socks_shoes','button_up','comb_hair','apply_powder',
  'know_when_toilet','tell_need_toilet','go_to_toilet','understand_toilet_use','clean_after_toilet','manage_menstruation',
  'go_to_bathroom','pour_water_on_body','soap_and_clean_body','dry_body_with_towel',
  'brush_teeth','put_toothpaste_on_brush',
  'fold_clothes','wash_own_dishes','prepare_drink','wash_own_clothes',
  'take_care_siblings','do_simple_chores',
  'identify_basic_shapes','understand_basic_colors','understand_number_concept',
  'write_some_numbers','write_some_letters','write_some_words','write_own_name','know_own_name',
  'differentiate_big_small','know_left_right','know_top_bottom',
  'express_hungry_full','understand_right_wrong',
  'name_body_parts','tell_gender_when_shown','understand_family_relation','name_family_members',
  'respond_to_greeting','play_independently','play_with_friends','vocalize_during_play','imitate_during_play',
  'constructive_block_play','share_toys_with_friends','cooperate_in_group_play','take_turns_in_group_activity','tidy_up_toys',
  'say_own_name','respond_to_name_call','look_at_speaker','understand_yes_no','answer_what_is_this',
  'say_simple_things','tell_story_from_picture','answer_phone_call',
  'wander_without_purpose','restless_or_cannot_stay_put','run_without_reason','spit_pinch_kick',
  'passive_no_reaction','use_vulgar_language','use_incomprehensible_words','break_things','undress_inappropriately',
  'deliberately_disobey','sleep_during_day','age_adjustment_difficulty','extreme_behaviour',
  'understand_rules_discipline','show_affection_to_peers','argue_when_needs_unmet','resolve_conflict_peacefully',
  'worry_when_friends_in_trouble','express_anger','use_rude_language','use_unintelligible_language',
  'attempt_help_parents_teachers','pick_up_objects_efficiently','use_both_hands_well','stand_jump_on_one_leg',
  'unscrew_container_lid','differentiate_by_size','differentiate_texture','cut_along_straight_line',
  'understand_money_up_to_100','travel_independently_public_transport','have_savings_account_concept'
];

/* ===================== Routing ===================== */
try {
  if ($type === 'trainee_list') {

    $hasFullName = columnExists($mysqli, 'trainee_performance', 'full_name');

    if ($hasFullName) {
      $sql = "SELECT DISTINCT trainee_id, full_name 
              FROM trainee_performance
              WHERE trainee_id IS NOT NULL AND trainee_id <> ''
              ORDER BY full_name ASC, trainee_id ASC";
      $stmt = $mysqli->prepare($sql);
    } else {
      $sql = "SELECT DISTINCT trainee_id 
              FROM trainee_performance
              WHERE trainee_id IS NOT NULL AND trainee_id <> ''
              ORDER BY trainee_id ASC";
      $stmt = $mysqli->prepare($sql);
    }
    if (!$stmt) json_error('Ralat SQL: ' . $mysqli->error);

    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) {
      $rows[] = [
        'trainee_id' => $r['trainee_id'],
        'full_name'  => $hasFullName ? ($r['full_name'] ?? $r['trainee_id']) : $r['trainee_id']
      ];
    }
    $stmt->close();
    json_ok($rows);
  }

  $selectList = implode(',', array_map(fn($c) => "`$c`", $columns));
 
  $sql = "SELECT $selectList
          FROM trainee_performance tp
          WHERE 1=1";
  $params = [];
  $types  = '';

  if ($trainee_id !== '') {
    $sql   .= " AND tp.trainee_id = ?";
    $types .= 's';
    $params[] = $trainee_id;
  }
  if ($week !== '') {
    $weekInt = (int)$week;
    if ($weekInt > 0) {
      $sql   .= " AND tp.week = ?";
      $types .= 'i';
      $params[] = $weekInt;
    }
  }

  $sql .= " ORDER BY tp.trainee_id ASC, tp.week ASC, tp.performance_id ASC";

  $stmt = $mysqli->prepare($sql);
  if (!$stmt) json_error('Ralat SQL: ' . $mysqli->error);

  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }

  $stmt->execute();
  $res = $stmt->get_result();

  $data = [];
  while ($row = $res->fetch_assoc()) {
    foreach ($columns as $c) {
      if (!array_key_exists($c, $row) || is_null($row[$c])) {
        $row[$c] = ''; 
      }
    }
    $data[] = $row;
  }
  $stmt->close();

  json_ok(['data' => $data]);

} catch (Throwable $e) {
  json_error('Ralat: ' . $e->getMessage(), 500);
} finally {
  if (isset($mysqli) && $mysqli instanceof mysqli) $mysqli->close();
}
