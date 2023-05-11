<?php

const
CRM_ENTITY_LEAD = "lead",
CRM_ENTITY_CONTACT = "contact",
CRM_ENTITY_COMPANY = "company",

METHOD_GET = "GET",
METHOD_POST = "POST",
METHOD_PATCH = "PATCH",

FIELD_ID_PHONE = 500393,
FIELD_ID_CITY = 588827,
FIELD_ID_SOURCE = 588809,
RUID = 6402732,
SOURCE_VALUE = 1315826,
TAG_ID = 472540,

FIELD_IDS_ROISTAT = [592655, 608411, 743051];

require ROOT . "/functions/display-errors.php";
require ROOT . "/vendor/autoload.php";
require ROOT . "/logs/logs.php";

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

require ROOT . "/functions/connectToCrm.php";
require ROOT . "/functions/refreshToken.php";
require ROOT . "/functions/crmMethods.php";
