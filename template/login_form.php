 <?php  

        function getMembership($contact_id = '') {

            $appid = "2_1648_216Go8r65";
            $key = "HuAnkRKqox3Ls8m";
            $content_type =  'application/json';

            $condition = '[{ "field":{"field":"contact_id"},"op":"=","value":{"value": "'. $contact_id .'"} }]';
            $listFields = "membership_level";


            //[{ "field":{"field":"lreferrer"},"op":"=","value":{"value": "79002"} }] ;
            $args = "?condition=". $condition . "&listFields=" . $listFields . "&Api-Appid=". $appid ."&Api-Key=" . $key;


            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/WordPressMemberships' . $args);
            curl_setopt ($ch, CURLOPT_CAINFO, "/xampp/htdocs/ontraport/cacert.pem");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_HEADER, false);

            //curl_setopt ($ch, CURLOPT_POST, true);
            //curl_setopt ($ch, CURLOPT_POSTFIELDS, $args);

            $output = curl_exec($ch);

            if( $output === FALSE) {
                return 'cURL Error: ' . curl_error($ch);
            }


            curl_close($ch);


            $result = json_decode($output, true);

            return $result;

        }

          
        


