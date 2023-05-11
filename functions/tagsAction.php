<?php



function getValues(string $value): array {


    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/$value";

    return json_decode(connect($link), true);


}




//  Выводит по id сущность, можно передать любую. Сделку, компанию и тд
function getValue(string $value, int $id): array
{

    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/$value/$id";


    $result = json_decode(connect($link), true);

    switch (is_null($result)) {
        case (true):
            return [];

        default:
            return $result;





    }



}

////    устанавливает тег из списка
function selectTag(string $value, int $leadId, $tagName)

{



    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/$value";

    $getId = file_get_contents(ROOT . "/spisokId.json");
    $getId = json_decode($getId, true);

    $arrayCheck = ["СПБ", "МСК", "КРД", "НВС", "РНД", "2 и более"];


    if(is_array($tagName)) {
        $wrongTag = [];
        $trigger = 0;

//      если массив, то добавляется в checkTags -> если есть тег, не входящий в arrayCheck,
//        то прибавляется к счетчику -> если длина массива checkTags 1 и что-то добавилось в
//        счетчик, то берется единственный элемент из массива.

//        если длина массива 2 и более и независимо от того, что добавилось в счетчик - будет
//        тег равен 2 и более


        foreach ($tagName as $value) {
            if(!in_array($value["name"], $arrayCheck)) {
                $trigger ++;
            } else {
                $wrongTag[] = $value["name"];
            }

        }
        if(count($wrongTag) === 1 && $trigger > 0) {
            $tagName = $wrongTag[0];

        } else if(count($wrongTag) > 1) {
            $tagName = "2 и более";
        }
    }


    if (in_array($tagName, $arrayCheck)) {
        $queryData = array(

            [
                "id" => $leadId,
                "custom_fields_values" => [
                    [
                        "field_id" => $getId[0]["fieldId"],
                        "values" => [
                            ["value" => $tagName


                            ]
                        ]
                    ]
                ]
            ]
        );

        connect($link, 'PATCH', $queryData);
    }


}



// Получение компании по id, проверка тегов и вызов функции на установку тега
function getCompanyById(int $companyId, int $leadId)
{

    $res = getValue("companies", $companyId);

    if(!empty($res["_embedded"])) {

        if (count($res["_embedded"]["tags"]) > 1) {
            selectTag("leads", $leadId, $res["_embedded"]["tags"]);
        } else if (count($res["_embedded"]["tags"]) === 1) {
            $tagName = $res["_embedded"]["tags"][0]["name"];
            selectTag("leads", $leadId, $tagName);
        }

    }


}


//    Вывод всех сущностей по id компании, которые ей принадлежат
function getDealsId(int $companyId, $tagName)
{


    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/companies/$companyId/links";
    $out = json_decode(connect($link), true);


    $elements = $out["_embedded"]["links"];


    foreach ($elements as $value) {

// проверка на lead, добавление тега
        if ($value["to_entity_type"] === "leads") {
            selectTag("leads", $value["to_entity_id"], $tagName);

        }
    }

}

// по id сделки получает id компании, к которой эта сделка привязана, самих данных компании тут нет
// только линка с id
function getCompanieId(int $leadId)
{

    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/leads/$leadId/links";
    $out = json_decode(connect($link), true);


    if ($out["_embedded"]["links"]) {
        getCompanyById($out["_embedded"]["links"][0]["to_entity_id"], $leadId);

    }


}


// добавление задачи для компаний, у которых 2 и более тегов
function addTask(int $companyId)
{


    $link = "https://{$_ENV["SUBDOMAIN"]}.amocrm.ru/api/v4/tasks";

    $queryData = array(
        [
            "text" => "У компании стоит больше одного тега, оставьте один",
            "entity_id" => $companyId,
            "complete_till" => 0,
            "entity_type" => "companies"

        ]
    );
    connect($link, 'POST', $queryData);

}

// проверяет на количество тегов, из этого далее выполняются функции

function tagCheck(array $companies)
{

    for ($i = 0; $i < count($companies); $i++) {


        if (count($companies[$i]["_embedded"]["tags"]) > 1) {

            addTask($companies[$i]["id"]);
            getDealsId($companies[$i]["id"], "2 и более");
        } else if (count($companies[$i]["_embedded"]["tags"]) === 1) {

            getDealsId($companies[$i]["id"], $companies[$i]["_embedded"]["tags"][0]["name"]);

        }


    }


}



