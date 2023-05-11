<?php


function checkData($hooksArrayData) {

    foreach ($hooksArrayData as $data) {


        if(array_key_exists("contacts", $data[0])) {
            

            if (count($data[0]["contacts"]["update"][0]["tags"]) === 1) {

                if (!empty((int)$data[0]["contacts"]["update"][0]["id"])) {

                    getDealsId((int)$data[0]["contacts"]["update"][0]["id"],
                        $data[0]["contacts"]["update"][0]["tags"][0]["name"]);
                    }


                } else if (count($data[0]["contacts"]["update"][0]["tags"]) > 1) {

                    getDealsId((int)$data[0]["contacts"]["update"][0]["id"],
                        $data[0]["contacts"]["update"][0]["tags"]);


                    }


        } else {

            getCompanieId((int)$data[0]["leads"]["update"][0]["id"]);

        }

                }


}







