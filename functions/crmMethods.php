<?php


/*function getEntities(string $entity_type): array
{


    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/$entity_type";

    return json_decode(connect($link), true);


}*/


//  Выводит по id сущность, можно передать любую. Сделку, компанию и тд
function getEntity(string $entity_type, int $id): array
{
    switch ($entity_type) {
        case CRM_ENTITY_CONTACT:
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/contacts/$id?with=leads";
            break;
        case CRM_ENTITY_LEAD:
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/leads/$id?with=contacts";
            break;
        case CRM_ENTITY_COMPANY:
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/companies/$id?with=contacts";
            break;
    }


    $result = json_decode(connect($link), true);

    if (empty($result)) {
        return [];
    } else {
        return $result;
    }





}


//  Ищет сущность по строке, можно передать любую. Сделку, компанию и тд.
//  Строка ДОЛЖНА БЫТЬ ОТ 4 СИМВОЛОВ
function searchEntity(string $entity_type, string $search): array
{
    if (empty($search) && (mb_strlen($search) < 4)) {
        return [];
    }

    switch ($entity_type) {
        case CRM_ENTITY_CONTACT:
            $query = [
                "with" => "leads",
                "query" => $search
            ];
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/contacts?" . http_build_query($query);
            break;
        case CRM_ENTITY_LEAD:
            $query = [
                "with" => "contacts",
                "query" => $search
            ];
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/leads?" . http_build_query($query);
            break;
        case CRM_ENTITY_COMPANY:
            $query = [
                "with" => "contacts",
                "query" => $search
            ];
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/companies?" . http_build_query($query);
            break;
    }


    $result = json_decode(connect($link), true);

    if (empty($result)) {
        return [];
    } else {
        return $result;
    }

}

// Создаем сущность
function addEntity(string $entity_type, array $data) {
    switch ($entity_type) {
        case CRM_ENTITY_CONTACT:
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/contacts";
            break;
        case CRM_ENTITY_LEAD:
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/leads";
            break;
        case CRM_ENTITY_COMPANY:
            $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/companies";
            break;
    }

    $result = json_decode(connect($link, METHOD_POST, [$data]), true);

    if (empty($result)) {
        return [];
    } else {
        return $result;
    }
}


// добавление задачи для компаний
function addTask(int $leadId)
{



    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/tasks";

    $endWorkDayStr = date("Y-m-d 20:00:00");
    $endWorkDayUnix= strtotime($endWorkDayStr);

    $now = time();

    $dayNumber = date("N");

    if($dayNumber == 5) {
        $res = strtotime(date("Y-m-d 20:00:00", strtotime($endWorkDayStr.'+ 3 days')));
    } else if($dayNumber == 6) {
        $res = strtotime(date("Y-m-d 20:00:00", strtotime($endWorkDayStr.'+ 2 days')));

    } else if($now >= $endWorkDayUnix) {
        $res = strtotime(date("Y-m-d 20:00:00", strtotime($endWorkDayStr.'+ 1 days')));

    } else {
        $before = $endWorkDayUnix - $now;
        $res = $now + $before;
    }


    $queryData = array(
        [
            "text" => "Связаться с клиентом",
            "entity_id" => $leadId,
            "complete_till" => $res,
            "entity_type" => "leads"

        ]
    );
    connect($link, METHOD_POST, $queryData);

}



function addNote(string $common, int $entity_id, string $client, string $specialist) {

    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/$common/$entity_id/notes";
    $queryData = array(

            [

                "note_type" => "common",
                "params" => [
                    "text" => "Переписка с клиентом: \n $client \n \n Комментарий от специалиста: \n  $specialist"
                ]


        ]




    );

    return json_decode(connect($link, METHOD_POST, $queryData), true);










}