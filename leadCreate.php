<?php

const ROOT = __DIR__;

require ROOT . "/functions/require.php";



$input = $_POST;

if (!empty($_GET["test"])) {
    $input = [
        "url" => "konkurenty.gk-usi.ru",
        "ROISTAT_ID" => 15976486,
        //    в служебное поле сделки будет записываться
        "lastname" => "(empty)",
        "city" => "Ростов-на-Дону",
        "mkr" => "Левобережье",
        "phone" => "+7 (444) 444 44 33",
        "name" => "Тест имя",
        "qc" => "Тест переписка",
        "asmm" => "Тест комментарий"
    ];




}

$input_trim_lower = [];
foreach ($input as $key => $value) {
    $input_trim_lower[$key] = trim(mb_strtolower($value));
}
$pipeline_id = null;
$status_id = null;


if ($input_trim_lower["mkr"] == "вересаево") {
    $pipeline_id = 1399426;
    $status_id = 22090243;
} elseif ($input_trim_lower["mkr"] == "левобережье") {
    $pipeline_id = 4242663;
    $status_id = 46035738;
}



if (!empty($pipeline_id)) {

    // ЧАСТЬ 1 - НАХОДИМ КОНТАКТ И АКТИВНЫЙ ЛИД В НЕМ
    $contact_id = null;
    $we_have_active_lead = false;
    $phone_to_search = preg_replace("/[^\d]/siu", "", $input["phone"]);
    if (mb_strlen($phone_to_search) == 11) {
        $phone_to_search = substr($phone_to_search, 1);
    }
    $existing_contacts = searchEntity(CRM_ENTITY_CONTACT, $phone_to_search);


//    echo "<h3>Ответ на поиск контактов</h3><pre>";
//    echo json_print($existing_contacts);
//    echo "</pre>";
    //die();



    if (!empty($existing_contacts["_embedded"]["contacts"])) {

        $contact_id = $existing_contacts["_embedded"]["contacts"][0]["id"];

        foreach ($existing_contacts["_embedded"]["contacts"] as $existing_contact) {

            if (!empty($existing_contact["_embedded"]["leads"])) {
                foreach ($existing_contact["_embedded"]["leads"] as $existing_lead_link) {
                    $existing_lead = getEntity(CRM_ENTITY_LEAD, $existing_lead_link["id"]);
                    if (in_array($existing_lead["pipeline_id"], [1399426, 4242663]) && !in_array($existing_lead["status_id"], [142, 143])) {

                        echo "<h3>Есть активный лид {$existing_lead["id"]}</h3><pre>";
                        $we_have_active_lead = true;
                        break;
                    }
                }
                if ($we_have_active_lead) {
                    break;
                }
            }
        }
    }






    // ДАЛЬШЕ ТОЛЬКО ЕСЛИ НЕТ АКТИВНОГО ЛИДА
    if (!$we_have_active_lead) {
        // ЧАСТЬ 2 - ЕСЛИ КОНТАКТА НЕТ, СОЗДАЕМ
        if (empty($contact_id)) {
            $contact_add_data = [
                "created_at" => time(),
                "name" => $input["name"],
                "responsible_user_id" => RUID,
                "custom_fields_values" => []
            ];
            $contact_add_data["custom_fields_values"][] = [
//                делает ключ с 0 индексом
                "field_id" => FIELD_ID_PHONE,
                "values" => [["value" => $input["phone"], "enum_code" => "MOB"]]
            ];
//            echo "<h3>Данные для создания контакта</h3><pre>";
//            echo json_print($contact_add_data);
//            echo "</pre>";
            $contact_add = addEntity(CRM_ENTITY_CONTACT, $contact_add_data);
//            echo "<h3>Ответ на создание контакта</h3><pre>";
//            echo json_print($contact_add);
//            echo "</pre>";
            $contact_id = intval($contact_add["_embedded"]["contacts"][0]["id"]);
        }

        // ЧАСТЬ 3 - СОЗДАЕМ СДЕЛКУ
        $lead_add_data = [
            "created_at" => time(),
            "name" => "Лид по конкурентам",
            "responsible_user_id" => RUID,
            "tags" => "konkurenty.gk-usi.ru",
            "pipeline_id" => $pipeline_id,
            "status_id" => $status_id,
            "custom_fields_values" => []
        ];
        $lead_add_data["_embedded"]["contacts"][]["id"] = $contact_id;

        $lead_add_data["_embedded"]["tags"][]["id"] = TAG_ID;

        $lead_add_data["custom_fields_values"][] = ["field_id" => FIELD_ID_SOURCE, "values" => [["enum_id" => SOURCE_VALUE]]];
        $lead_add_data["custom_fields_values"][] = ["field_id" => FIELD_ID_CITY, "values" => [["value" => $input["city"]]]];
        foreach (FIELD_IDS_ROISTAT as $field_id_roistat) {
            $lead_add_data["custom_fields_values"][] = ["field_id" => $field_id_roistat, "values" => [["value" => strval($input["ROISTAT_ID"])]]];
        }

        echo "<h3>Данные для создания сделки</h3><pre>";
        echo json_print($lead_add_data);
        echo "</pre>";

        $lead_add = addEntity(CRM_ENTITY_LEAD, $lead_add_data);
        echo "<h3>Ответ на создание сделки</h3><pre>";
        echo print_r($lead_add);
        echo "</pre>";






        addNote("leads", $lead_add["_embedded"]["leads"][0]["id"], $input["qc"], $input["asmm"]);

        addTask($lead_add["_embedded"]["leads"][0]["id"]);





    } else {

        if(!empty($existing_lead["id"])) {
            addNote("leads", $existing_lead["id"], $input["qc"], $input["asmm"]);
            addTask($existing_lead["id"]);

        }


    }







}