<?php
/*
Links:
    1. https://github.com/thiagoalessio/tesseract-ocr-for-php
    2. https://github.com/tesseract-ocr/tesseract
    3. https://tesseract-ocr.github.io/tessdoc/Home.html
    4. https://github.com/UB-Mannheim/tesseract/wiki
    5. https://digi.bib.uni-mannheim.de/tesseract/

Notice:
    1. Add C:\Program Files\Tesseract-OCR to env variable.

Upload: 
    1. https://www.w3schools.com/php/php_file_upload.asp
*/

    require_once('./vendor/autoload.php');
    
    use thiagoalessio\TesseractOCR\TesseractOCR;
    
    function OcrTDIdCards(){

        if(isset($_FILES) && !empty($_FILES) && isset($_FILES["image"]) && !empty($_FILES["image"])){
        
            $image = $_FILES["image"];
            $file_name = $image["name"];
    
            $target_dir = "uploads/";
            $target_file = $target_dir.basename($file_name);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            $temp_name = $image["tmp_name"];
            
            $imageObj = new stdClass(); 
            $imageObj->isImage = null;
            $imageObj->type = null;
            $imageObj->text = null;
            $imageObj->base64Str = "";
            $imageObj->errors = null;
    
            $check = getimagesize($image["tmp_name"]);
            if($check !== false && $imageFileType === "png"){
                //move_uploaded_file($image["tmp_name"], $target_file);//za upload.
                $imageObj->isImage = true;
                $imageObj->type = "png";
    
                $content = new TesseractOCR($temp_name);
                $loadLanguages = $content->lang("srp_latn", "bos", "hrv");
                $text = $loadLanguages->run();
                
                $path = $temp_name;
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($image["tmp_name"]);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $imageObj->base64Str = $base64;
    
                $imageObj->text = $text;
    
                $uploadOk = 1;
                
                $imageObj->text = explode("\n", $imageObj->text);
    
                $standards = new stdClass(); 
                $standards->TD1 = false;
                $standards->TD2 = false;
                $standards->TD3 = false;
    
                //Za licnu kartu gde dokument ima tri reda.
                if(count($imageObj->text) === 3 && (strlen($imageObj->text[0]) >= 28 || strlen($imageObj->text[0]) >= 32) ){
                    $standards->TD1 = true;
                }
    
                //Za licnu kartu i pasos gde dokument ima dva reda.
                if(count($imageObj->text) === 2 && (strlen($imageObj->text[0]) >= 34 || strlen($imageObj->text[0]) <= 38) && substr($imageObj->text[0],0, 1) !== "V"  && substr($imageObj->text[0],0, 1) !== "P"){
                    $standards->TD2 = true;
                }
    
                //Za pasos gde dokument ima dva reda, a svi TD dokumenti imaju dva reda.
                if(count($imageObj->text) === 2 && (strlen($imageObj->text[0]) >= 42 || strlen($imageObj->text[0]) <= 46) && substr($imageObj->text[0],0, 1) === "P"){
                    $standards->TD3 = true;
                }
    
                $doc = new stdClass();
                $doc->firstName = "";
                $doc->lastName = "";
                $doc->countryCode = "";
                $doc->docType = "";
                $doc->docNum = "";
                $doc->jmbg = "";
                $doc->sex = "";
                $doc->expDate = "";
    
                $TD1Doc = new stdClass();
                $TD1Doc->line1 = new stdClass();
                
                $TD1Doc->line1->l1 = "";
                $TD1Doc->line1->l2 = "";
                $TD1Doc->line1->l3 = "";
                $TD1Doc->line1->l4 = "";
                $TD1Doc->line1->l5 = "";
    
                $TD1Doc->line2 = new stdClass();
                $TD1Doc->line2->l1 = "";
                $TD1Doc->line2->l2 = "";
                $TD1Doc->line2->l3 = "";
                $TD1Doc->line2->l4 = "";
                $TD1Doc->line2->l5 = "";
                $TD1Doc->line2->l6 = "";
                $TD1Doc->line2->l7 = "";
                $TD1Doc->line2->l8 = "";
    
                $TD1Doc->line3 = new stdClass();
                $TD1Doc->line3->l1 = "";
    
                if($standards->TD1 === true){
    
                    //linija1: (0-1), (2-4), (5-13), (14-14), (15-poslednji)
                    //linija2: (0-5), (6-6), (7-7), (8-13), (14-14), (15-17), (18-pretposlednji), (poslednji)
                    //linija3: (0-zadnjeg)(Surname, followed by two filler characters, followed by given names)
                   
                    $TD1Doc->line1->l1 = utf8_encode(substr($imageObj->text[0], 0, 2));//Tip dokumenta.
                    $TD1Doc->line1->l2 = utf8_encode(substr($imageObj->text[0], 2, 3));//Kod drzave.
                    $TD1Doc->line1->l3 = utf8_encode(substr($imageObj->text[0], 5, 9));//Broj dokumenta.
                    $TD1Doc->line1->l4 = utf8_encode(substr($imageObj->text[0], 14, 1));//Check cifra za broj dokumenta.
                    $TD1Doc->line1->l5 = utf8_encode(substr($imageObj->text[0], 15));//Opcioni podaci br.1, kod nas JMBG.
    
                    $TD1Doc->line2->l1 = utf8_encode(substr($imageObj->text[1], 0, 6));//Datum rodjenja.
                    $TD1Doc->line2->l2 = utf8_encode(substr($imageObj->text[1], 6, 1));//Check cifra za datum rodjenja.
                    $TD1Doc->line2->l3 = utf8_encode(substr($imageObj->text[1], 7, 1));//Pol. M-muski, F-zenski.
                    $TD1Doc->line2->l4 = utf8_encode(substr($imageObj->text[1], 8, 6));//Datum isteka dokumenta.
                    $TD1Doc->line2->l5 = utf8_encode(substr($imageObj->text[1], 14, 1));//Check cifra za datum isteka.
                    $TD1Doc->line2->l6 = utf8_encode(substr($imageObj->text[1], 15, 3));//Nacionalnost, tj. kod drzave.
                    $TD1Doc->line2->l7 = utf8_encode(substr($imageObj->text[1], 18, strlen($imageObj->text[1])-1-18));//Opcioni podaci br.2.
                    $TD1Doc->line2->l8 = utf8_encode(substr($imageObj->text[1], strlen($imageObj->text[1])-1));//Check cifra za prvu i drugu liniju.
    
                    $TD1Doc->line3->l1 = $imageObj->text[2];
    
                    $pos1 = stripos($TD1Doc->line3->l1, "<");
    
                    //$doc->firstName = implode(", ",array_filter(explode("<", substr($pos1, count($TD1Doc->line3->l1))), ""));
                    
                    $regExp = "/[^a-zA-Z]+/";
                    $doc->firstName = implode(", ",array_filter(preg_replace($regExp, "", explode("<", substr($TD1Doc->line3->l1, $pos1, strlen($TD1Doc->line3->l1)-$pos1)))));
                    $doc->lastName = substr($TD1Doc->line3->l1, 0, $pos1);
                    $doc->countryCode = implode("", explode("<", $TD1Doc->line1->l2));
                    $doc->docType = implode("", explode("<", $TD1Doc->line1->l1));
                    $doc->docNum = implode("", explode("<", $TD1Doc->line1->l3));
                    $doc->jmbg = implode("", explode("<", $TD1Doc->line1->l5));
                    $doc->sex = implode("", explode("<", $TD1Doc->line2->l3));
                    $doc->expDate = implode("", explode("<", $TD1Doc->line2->l4));
                    
                    $currYear = date("Y");
                    $altYear = substr($currYear."", 0, 2);
                    $altDate = $altYear.substr($doc->expDate, 0, 2)."-".substr($doc->expDate, 2, 2)."-".substr($doc->expDate, 4, 2);
                    $doc->expDate = $altDate;
    
                    $imageObj->TD1Doc = $TD1Doc;
                    $imageObj->doc = $doc;
                    
                }
    
                $TD2Doc = new stdClass();
    
                $TD2Doc->line1 = new stdClass();
                $TD2Doc->line1->l1 = "";
                $TD2Doc->line1->l2 = "";
                $TD2Doc->line1->l3 = "";
    
                $TD2Doc->line2 = new stdClass();
                $TD2Doc->line2->l1 = "";
                $TD2Doc->line2->l2 = "";
                $TD2Doc->line2->l3 = "";
                $TD2Doc->line2->l4 = "";
                $TD2Doc->line2->l5 = "";
                $TD2Doc->line2->l6 = "";
                $TD2Doc->line2->l7 = "";
                $TD2Doc->line2->l8 = "";
                $TD2Doc->line2->l9 = "";
                $TD2Doc->line2->l10 = "";
    
                if($standards->TD2 === true){
    
                    //linija1: (0-1), (2-4), (5-poslednji)
                    //linija2: (0-8), (9), (10-12), (13-18), (19), (20), (21-26), (27), (28-pretposlednji), (poslednji)
                    
                    $TD2Doc->line1->l1 = utf8_encode(substr($imageObj->text[0], 0, 2));//Tip dokumenta.
                    $TD2Doc->line1->l2 = utf8_encode(substr($imageObj->text[0], 2, 3));//Kod drzave.
                    $TD2Doc->line1->l3 = utf8_encode(substr($imageObj->text[0], 5, strlen($imageObj->text[0])-5));//Prezime i imena.
                    
                    $TD2Doc->line2->l1 = utf8_encode(substr($imageObj->text[1], 0, 9));//Broj dokumenta.
                    $TD2Doc->line2->l2 = utf8_encode(substr($imageObj->text[1], 9, 1));//Check cifra za broj dokumenta.
                    $TD2Doc->line2->l3 = utf8_encode(substr($imageObj->text[1], 10, 3));//Kod drzave, nacionalnost.
                    $TD2Doc->line2->l4 = utf8_encode(substr($imageObj->text[1], 13, 6));//Datum rodjenja.
                    $TD2Doc->line2->l5 = utf8_encode(substr($imageObj->text[1], 19, 1));//Check cifra za datum rodjenja.
                    $TD2Doc->line2->l6 = utf8_encode(substr($imageObj->text[1], 20, 1));//Pol. M-muski, F-zenski.
                    $TD2Doc->line2->l7 = utf8_encode(substr($imageObj->text[1], 21, 6));//Datum isteka dokumenta.
                    $TD2Doc->line2->l8 = utf8_encode(substr($imageObj->text[1], 27, 1));//Check cifra za datum isteka.
                    $TD2Doc->line2->l9 = utf8_encode(substr($imageObj->text[1], 28, strlen($imageObj->text[1])-1-28));//Opcioni podaci br.1.
                    $TD2Doc->line2->l10 = utf8_encode(substr($imageObj->text[1], strlen($imageObj->text[1])-1, 1));//Opsta check cifra za donju liniju.
    
                    $regExp = "/[^a-zA-Z]+/";
                    
                    $pos1 = stripos($TD2Doc->line1->l3, "<<");
                    $pos2 = strrpos($TD2Doc->line1->l3, "<<");
                    $doc->firstName = implode(", ", array_filter(explode("<", substr($TD2Doc->line1->l3, $pos1, $pos2))));
                    $doc->lastName = substr($TD2Doc->line1->l3, 0, $pos1);
                    $doc->countryCode = implode("", explode("<", $TD2Doc->line1->l2));
                    $doc->docType = implode("", explode("<", $TD2Doc->line1->l1));
                    $doc->docNum = implode("", explode("<", substr($TD2Doc->line2->l1, 0, $pos1)));
                    $doc->jmbg = implode("", explode("<", $TD2Doc->line2->l9));
                    $doc->sex = implode("", explode("<", $TD2Doc->line2->l6));
                    $doc->expDate = implode("", explode("<", $TD2Doc->line2->l7));
    
                    $currYear = date("Y");
                    $altYear = substr($currYear."", 0, 2);
                    $altDate = $altYear.substr($doc->expDate, 0, 2)."-".substr($doc->expDate, 2, 2)."-".substr($doc->expDate, 4, 2);
                    $doc->expDate = $altDate;
    
                    $imageObj->TD2Doc = $TD2Doc;
                    $imageObj->doc = $doc;
                    
                }
    
                $TD3Doc = new stdClass();
    
                $TD3Doc->line1 = new stdClass();
                $TD3Doc->line1->l1 = "";
                $TD3Doc->line1->l2 = "";
                $TD3Doc->line1->l3 = "";
    
                $TD3Doc->line2 = new stdClass();
                $TD3Doc->line2->l1 = "";
                $TD3Doc->line2->l2 = "";
                $TD3Doc->line2->l3 = "";
                $TD3Doc->line2->l4 = "";
                $TD3Doc->line2->l5 = "";
                $TD3Doc->line2->l6 = "";
                $TD3Doc->line2->l7 = "";
                $TD3Doc->line2->l8 = "";
                $TD3Doc->line2->l9 = "";
                $TD3Doc->line2->l10 = "";
                $TD3Doc->line2->l11 = "";
                
                if($standards->TD3 === true){
                    
                    //Linija1: (0-1), (2-4), (5-poslednji)
                    //Linija2: (0-8), (9), (10-12), (13-18), (19), (20), (21-26), (27), (28-pretpretposlednji), (pretposlednji), (poslednji)
                    
                    $TD3Doc->line1->l1 = utf8_encode(substr($imageObj->text[0], 0, 2));//Tip dokumenta.
                    $TD3Doc->line1->l2 = utf8_encode(substr($imageObj->text[0], 2, 3));//Kod drzave.
                    $TD3Doc->line1->l3 = utf8_encode(substr($imageObj->text[0], 5));//Prezime i imena.
    
                    $TD3Doc->line2->l1 = utf8_encode(substr($imageObj->text[1], 0, 9));//Broj dokumenta.
                    $TD3Doc->line2->l2 = utf8_encode(substr($imageObj->text[1], 9, 1));//Check cifra za broj dokumenta.
                    $TD3Doc->line2->l3 = utf8_encode(substr($imageObj->text[1], 10, 3));//Kod drzave, nacionalnost.
                    $TD3Doc->line2->l4 = utf8_encode(substr($imageObj->text[1], 13, 6));//Datum rodjenja.
                    $TD3Doc->line2->l5 = utf8_encode(substr($imageObj->text[1], 19, 1));//Check cifra za datum rodjenja.
                    $TD3Doc->line2->l6 = utf8_encode(substr($imageObj->text[1], 20, 1));//Pol. M-muski, F-zenski.
                    $TD3Doc->line2->l7 = utf8_encode(substr($imageObj->text[1], 21, 6));//Datum isteka dokumenta.
                    $TD3Doc->line2->l8 = utf8_encode(substr($imageObj->text[1], 27, 1));//Check cifra za datum isteka.
                    $TD3Doc->line2->l9 = utf8_encode(substr($imageObj->text[1], 28, strlen($imageObj->text[1])-28-2));//Opcioni podaci br.1.
                    $TD3Doc->line2->l10 = utf8_encode(substr($imageObj->text[1], strlen($imageObj->text[1])-2, 1));//Check cifra za opcione podatke.
                    $TD3Doc->line2->l11 = utf8_encode(substr($imageObj->text[1], strlen($imageObj->text[1])-1, 1));//Opsta check cifra za donju liniju.
                    
                    $pos1 = stripos($TD3Doc->line1->l3, "<<");
                    $pos2 = strrpos($TD3Doc->line1->l3, "<<");
    
                    $doc->firstName = implode(", ", array_filter(explode("<", substr($TD3Doc->line1->l3, $pos1, $pos2))));
                    $doc->lastName = implode("", explode("<", substr($TD3Doc->line1->l3, 0, $pos1)));
                    $doc->countryCode = implode("", explode("<", $TD3Doc->line1->l2));
                    $doc->docType = implode("", explode("<", substr($TD3Doc->line1->l1, 0, $pos1)));;
                    $doc->docNum = implode("", explode("<", $TD3Doc->line2->l1));
                    $doc->jmbg = implode("", explode("<", $TD3Doc->line2->l9));
                    $doc->sex = implode("", explode("<", $TD3Doc->line2->l6));
                    $doc->expDate = implode("", explode("<", $TD3Doc->line2->l7));
    
                    $currYear = date("Y");
                    $altYear = substr($currYear."", 0, 2);
                    $altDate = $altYear.substr($doc->expDate, 0, 2)."-".substr($doc->expDate, 2, 2)."-".substr($doc->expDate, 4, 2);
                    $doc->expDate = $altDate;
    
                    $imageObj->TD3Doc = $TD3Doc;
                    $imageObj->doc = $doc;
    
                }
                
                $imageObj->standards = $standards;
                $json = json_encode($imageObj,JSON_UNESCAPED_UNICODE);

                return $json;
    
            } 
            else{
    
                $imageObj->isImage = false;
                $imageObj->type = null;
                $imageObj->text = null;
                $uploadOk = 0;
    
                if($check === false){
                    $imageObj->errors .= " Not appropriate file type. Supported type is image.";
                }
    
                if($imageFileType !== "png"){
                    $imageObj->errors .= " Not appropriate image type. Supported type is png.";
                }

                return json_encode($imageObj);
    
            }
    
        }
    
        //Izlistavanje svih dostupnih jezika.
        //foreach((new TesseractOCR())->availableLanguages() as $lang) echo $lang."<br>";

    }
    
    print_r(OcrTDIdCards());

?>
