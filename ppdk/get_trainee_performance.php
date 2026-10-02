<?php
$conn = new mysqli('localhost', 'root', '', 'ppdk_db');
$trainee_id = $_GET['trainee_id'] ?? '';

// Dapatkan maklumat asas pelatih
$sql = "SELECT ic, dob, gender, oku_categories, assistive_tools_used, assistive_tools_needed FROM trainee WHERE trainee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $trainee_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($row = $result->fetch_assoc()) {
    $data = $row;
}

// Dapatkan prestasi motor skill
$sql2 = "SELECT week, roll_left_right, reach_forward, sit, crawl, creep, kneel, stand, climb_stairs, descend_stairs,  
        swallow_safely, wash_hands_before_after, bring_food_to_mouth, eat_with_hand, eat_with_spoon, drink_with_cup, clean_utensils, arrange_utensils,
        identify_front_back, remove_clothing, put_on_clothes, put_on_socks_shoes, button_up, comb_hair, apply_powder,
        know_when_toilet, tell_need_toilet, go_to_toilet, understand_toilet_use, clean_after_toilet, manage_menstruation,
        go_to_bathroom, pour_water_on_body, soap_and_clean_body, dry_body_with_towel, brush_teeth, put_toothpaste_on_brush,
        fold_clothes, wash_own_dishes, prepare_drink, wash_own_clothes, take_care_siblings, do_simple_chores,
        identify_basic_shapes, understand_basic_colors, understand_number_concept, write_some_numbers, write_some_letters, write_some_words, 
        write_own_name, know_own_name, differentiate_big_small, know_left_right, know_top_bottom, express_hungry_full, 
        understand_right_wrong, name_body_parts, tell_gender_when_shown, understand_family_relation, name_family_members, respond_to_greeting,
        play_independently, play_with_friends, vocalize_during_play, imitate_during_play, constructive_block_play, share_toys_with_friends, cooperate_in_group_play, take_turns_in_group_activity, tidy_up_toys,
        say_own_name, respond_to_name_call, look_at_speaker, understand_yes_no, answer_what_is_this, say_simple_things, tell_story_from_picture, answer_phone_call,
        wander_without_purpose, restless_or_cannot_stay_put, run_without_reason, spit_pinch_kick, passive_no_reaction, use_vulgar_language, use_incomprehensible_words,
        break_things, undress_inappropriately, deliberately_disobey, sleep_during_day, age_adjustment_difficulty, extreme_behaviour, understand_rules_discipline,
        show_affection_to_peers, argue_when_needs_unmet, resolve_conflict_peacefully, worry_when_friends_in_trouble, express_anger, use_rude_language, use_unintelligible_language, attempt_help_parents_teachers,
        pick_up_objects_efficiently, use_both_hands_well, stand_jump_on_one_leg, unscrew_container_lid, differentiate_by_size, differentiate_texture, cut_along_straight_line, understand_money_up_to_100, travel_independently_public_transport, have_savings_account_concept
         FROM trainee_performance 
         WHERE trainee_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $trainee_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$data['motor_skills'] = [];

while ($row2 = $result2->fetch_assoc()) {
    $week = $row2['week'];
    foreach ($row2 as $skill => $value) {
        if ($skill === 'week') continue;
        $data['motor_skills'][$skill][$week] = $value;
    }
}

echo json_encode($data);
