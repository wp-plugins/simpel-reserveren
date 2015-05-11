<?php

function setParam($key, $value, $lang = null, $url = '') {
    $url = str_replace(site_url(), '', $url);
    $params = array();
    if (is_array($key)) {
        foreach ($key as $i => $k) {
            $params[$k] = $value[$i];
        }
    } else {
        $params[$key] = $value;
    }
    $params = array_merge($_GET, $params);
    foreach ($params as $key => $value) {
        if ($key == "route" || $key == 'lang')
            continue;
        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                if (empty($subvalue))
                    continue;
                $url .= (strpos($url, "?") === false ? "?" : "&") . $key . "[]=" . $subvalue;
            }
        } else {
            if (empty($value))
                continue;
            $url .= (strpos($url, "?") === false ? "?" : "&") . $key . "=" . $value;
        }
    }
    return $url;
}

function truncate_string($string, $maxlength, $extension, $extra = "striptags", $return_second = 0) {
    if ($extra == "striptags") {
        $string = strip_tags($string);
    }
    $cutmarker = "**cut_here**";

    if (strlen($string) > $maxlength) {
        $string = wordwrap($string, $maxlength, $cutmarker);
        $string = explode($cutmarker, $string);

        if ($return_second) {
            $result = array($string[0]);
            array_shift($string);
            $result[] = implode(' ', $string);
            return $result;
        }
        $string = $string[0] . $extension;
    }
    return $string;
}

/**
 * Functie voor het vertalen van Camping/Vakantiepark/Hotel/Bootverhuur
 */
function _sr($text)
{
    switch(SIMPEL_KLANT_TYPE) {
        case 'vakantiepark':
            $translations = array(
                'camping'       => __('Holiday park', 'simpelreserveren'),
                'campings'      => __('Holiday parks', 'simpelreserveren'),
                'accommodatie'  => __('Accommodation', 'simpelreserveren'),
                'accommodaties' => __('Accommodations', 'simpelreserveren'),
            );
            break;
        
        case 'hotel':
            $translations = array(
                'camping'       => __('Hotel', 'simpelreserveren'),
                'campings'      => __('Hotels', 'simpelreserveren'),
                'accommodatie'  => __('Room', 'simpelreserveren'),
                'accommodaties' => __('Rooms', 'simpelreserveren'),
            );
            break;
        
        case 'bootverhuur':
            $translations = array(
                'camping'       => __('Marina', 'simpelreserveren'),
                'campings'      => __('Marinas', 'simpelreserveren'),
                'accommodatie'  => __('Boat', 'simpelreserveren'),
                'accommodaties' => __('Boats', 'simpelreserveren'),
                'type'          => __('Type', 'simpelreserveren'),
            );
            break;

        default:
            $translations = array(
                'type'          => __('Accommodation', 'simpelreserveren'),
            );
            
    }
    if(isset($translations)) {
        if(isset($translations[strtolower($text)])) {
            $text = $translations[strtolower($text)];
        }
    }
    return $text;
}

$icons = array('&#xE001;', '&#x2316;', '&#x1F50E;', '&#xE002;', '&#xE003;', '&#xE004;', '&#xE010;', '&#x1F440;', '&#x1F4CE;', '&#x1F517;', '&#xE070;', '&#x270E;', '&#xE071;', '&#x2710;', '&#x1F4DD;', '&#x270F;', '&#x1F512;', '&#x1F513;', '&#x1F511;', '&#x232B;', '&#x1F6AB;', '&#x1F6AD;', '&#xE0D0;', '&#x25CE;', '&#x1F195;', '&#xE100;', '&#xE102;', '&#xE103;', '&#x1F516;', '&#x2691;', '&#x1F44D;', '&#x1F44E;', '&#x2665;', '&#xE1A0;', '&#x2661;', '&#x22C6;', '&#xE1A1;', '&#xE1C1;', '&#x1F380;', '&#x1F3AF;', '&#xE200;', '&#xE201;', '&#xE202;', '&#xE206;', '&#x2712;', '&#xE220;', '&#xE221;', '&#xE225;', '&#xE226;', '&#xE2B3;', '&#xE2B5;', '&#x2941;', '&#x1F4DE;', '&#xE300;', '&#xE302;', '&#x1F4E2;', '&#xE310;', '&#xE320;', '&#x21A9;', '&#xE350;', '&#x2709;', '&#x1F4E5;', '&#x1F4E4;', '&#xE352;', '&#x1F4AC;', '&#x1F464;', '&#x1F467;', '&#x1F465;', '&#xE400;', '&#xE401;', '&#xE402;', '&#xE404;', '&#xE406;', '&#xE407;', '&#xE500;', '&#xE501;', '&#xE502;', '&#xE504;', '&#xE505;', '&#xE507;', '&#xE510;', '&#x1F381;', '&#x1F34F;', '&#xE530;', '&#x1F3EC;', '&#xE531;', '&#xE972;', '&#x1F4B3;', '&#xE540;', '&#xE541;', '&#x1F3E7;', '&#xE542;', '&#x1F4B5;', '&#xE543;', '&#xE544;', '&#xE545;', '&#x1F4B0;', '&#xE546;', '&#xE551;', '&#x0025;', '&#x1F3E6;', '&#x2696;', '&#xE570;', '&#xE571;', '&#x1F4CA;', '&#xE572;', '&#xE573;', '&#xE574;', '&#xE575;', '&#x1F4C8;', '&#x1F4C9;', '&#xE576;', '&#xE578;', '&#xE579;', '&#xE582;', '&#x1F4E6;', '&#xE5D8;', '&#xE5E0;', '&#xE5E1;', '&#xE5E2;', '&#xE5E3;', '&#xE5E4;', '&#xE5E5;', '&#xE5E6;', '&#xE5E7;', '&#xE5E8;', '&#x2302;', '&#x1F3E2;', '&#x1F3E8;', '&#x1F30E;', '&#x1F310;', '&#xE600;', '&#xE602;', '&#xE610;', '&#xE611;', '&#xE612;', '&#xE613;', '&#xE670;', '&#xE671;', '&#xE672;', '&#xE673;', '&#xE674;', '&#xE680;', '&#xE681;', '&#xE6D0;', '&#x1F4CD;', '&#x1F4CC;', '&#xE6D1;', '&#xE6D2;', '&#xE710;', '&#x1F4BE;', '&#xE720;', '&#xE7A0;', '&#xE7B0;', '&#x266B;', '&#x266A;', '&#x1F3A4;', '&#x1F508;', '&#x1F509;', '&#x1F50A;', '&#x1F4FB;', '&#xE801;', '&#xE800;', '&#xE810;', '&#x1F4BF;', '&#x1F4F7;', '&#x1F304;', '&#x1F4F9;', '&#xE8A1;', '&#x25B6;', '&#xE8A0;', '&#x25A0;', '&#x25CF;', '&#x23EA;', '&#x23E9;', '&#x23EE;', '&#x23ED;', '&#x23CF;', '&#x1F501;', '&#x21BA;', '&#x1F500;', '&#xE902;', '&#xE903;', '&#x1F4D5;', '&#x1F4DA;', '&#x1F4D6;', '&#xE962;', '&#xE963;', '&#xE966;', '&#xE967;', '&#x1F4D3;', '&#x1F4F0;', '&#xE973;', '&#xE9A1;', '&#xE9A2;', '&#xE9A3;', '&#xE9B0;', '&#x1F4BB;', '&#xEA00;', '&#xEA01;', '&#xEA02;', '&#x1F4F1;', '&#xEA03;', '&#xEA04;', '&#xEA05;', '&#xE968;', '&#xE969;', '&#x1F50B;', '&#xEA10;', '&#xEA11;', '&#xEA12;', '&#xEA13;', '&#xEA14;', '&#xEA23;', '&#xEA24;', '&#xEA25;', '&#xEA26;', '&#xEA27;', '&#xEA28;', '&#xEA29;', '&#xEA2A;', '&#xEA2B;', '&#xEA2C;', '&#xEA30;', '&#xEA31;', '&#xEA32;', '&#xEA33;', '&#xEA34;', '&#xEA35;', '&#x1F4A1;', '&#xEA83;', '&#xEA85;', '&#xEA88;', '&#xEAB1;', '&#xEAB2;', '&#x1F525;', '&#xEAB3;', '&#x1F388;', '&#x1F384;', '&#xEA86;', '&#x265E;', '&#x2680;', '&#x2681;', '&#x2682;', '&#x2683;', '&#x2684;', '&#x2685;', '&#xEB00;', '&#xEB01;', '&#xEB02;', '&#xEB03;', '&#xEB40;', '&#xEB41;', '&#xEB42;', '&#xEB43;', '&#xEB80;', '&#xEB81;', '&#x21BB;', '&#xEB82;', '&#xEB83;', '&#xEB84;', '&#xEB85;', '&#xEB87;', '&#x1F4C4;', '&#xEC01;', '&#xEC02;', '&#xEC04;', '&#xEC06;', '&#xEC07;', '&#xEC08;', '&#xEC09;', '&#xEC0A;', '&#xEC11;', '&#xEC15;', '&#xEC17;', '&#xEC19;', '&#xEC30;', '&#xEC31;', '&#xEC32;', '&#xEC33;', '&#xEC34;', '&#xEC35;', '&#xEC36;', '&#x1F4C1;', '&#x1F4C2;', '&#xEC76;', '&#xEC77;', '&#xEC80;', '&#xEC81;', '&#xEC83;', '&#xEC87;', '&#xEC88;', '&#x201C;', '&#xED00;', '&#xED01;', '&#xED11;', '&#x2399;', '&#x1F4E0;', '&#xED50;', '&#xEDA0;', '&#xEE00;', '&#x21AA;', '&#x2922;', '&#xEE01;', '&#x2753;', '&#x2139;', '&#x26A0;', '&#x26D4;', '&#xEE02;', '&#xEE03;', '&#xEE04;', '&#xEE05;', '&#x002B;', '&#x002D;', '&#x2713;', '&#x2421;', '&#x1F43B;', '&#x1F426;', '&#xEF20;', '&#xEF21;', '&#x1F333;', '&#x1F332;', '&#x1F334;', '&#x1F342;', '&#x1F331;', '&#xEF70;', '&#x2699;', '&#xF000;', '&#xF004;', '&#x1F514;', '&#x1F515;', '&#x2301;', '&#x1F527;', '&#xF036;', '&#x23F2;', '&#x231A;', '&#x23F1;', '&#x23F0;', '&#x1F4C5;', '&#xF070;', '&#xF071;', '&#xF072;', '&#xF073;', '&#xF103;', '&#x1F354;', '&#x1F355;', '&#x1F41F;', '&#x1F364;', '&#xF105;', '&#xF106;', '&#x1F35A;', '&#x1F35C;', '&#x1F35D;', '&#xF110;', '&#xF111;', '&#x1F368;', '&#x2615;', '&#x1F37A;', '&#xF122;', '&#x1F377;', '&#x1F378;', '&#x1F375;', '&#xF127;', '&#xF128;', '&#xF129;', '&#xF130;', '&#xF131;', '&#xF132;', '&#xF133;', '&#xF136;', '&#xF140;', '&#xF141;', '&#x1F373;', '&#xF142;', '&#x1F374;', '&#xF150;', '&#x1F52A;', '&#xF151;', '&#xF162;', '&#xF163;', '&#xF164;', '&#xF165;', '&#xF166;', '&#xF170;', '&#xF171;', '&#xF172;', '&#x1F45C;', '&#x1F4BC;', '&#xF1A0;', '&#xF200;', '&#xF201;', '&#xF210;', '&#x2601;', '&#x1F4A7;', '&#x2600;', '&#x26C5;', '&#x2614;', '&#x26C8;', '&#x2602;', '&#x1F308;', '&#xF211;', '&#xF212;', '&#xF213;', '&#x2744;', '&#xF280;', '&#xF281;', '&#x1F50C;', '&#xF282;', '&#x1F698;', '&#x1F696;', '&#x1F682;', '&#x1F686;', '&#x1F687;', '&#x1F68D;', '&#x1F69A;', '&#x1F690;', '&#x1F69C;', '&#xF323;', '&#x2708;', '&#xF325;', '&#xF324;', '&#x1F681;', '&#x1F6B2;', '&#xF303;', '&#x1F6A2;', '&#x26F5;', '&#xF305;', '&#x1F6A1;', '&#x1F680;', '&#xF313;', '&#xF314;', '&#x26FD;', '&#xF315;', '&#xF316;', '&#x1F6A6;', '&#xF320;', '&#xF321;', '&#xF322;', '&#x267F;', '&#xF380;', '&#xF400;', '&#xF401;', '&#x26F8;', '&#x1F3B1;', '&#xF404;', '&#x1F3BE;', '&#xF402;', '&#xF403;', '&#xF405;', '&#xF406;', '&#x1F3C8;', '&#x26BD;', '&#xF410;', '&#xE412;', '&#x1F6BF;', '&#xF414;', '&#xF415;', '&#xF416;', '&#xF417;', '&#xF418;', '&#xE420;', '&#xE421;', '&#xE422;', '&#xF423;', '&#xF424;', '&#x26E8;', '&#xF4B0;', '&#x1F691;', '&#xF4B2;', '&#xF4B3;', '&#xF4B4;', '&#x1F489;', '&#x1F48A;', '&#xF4B5;', '&#xF4B6;', '&#xF4B7;', '&#xF4C2;', '&#xF4D0;', '&#xF4D1;', '&#xF4E0;', '&#xF4E1;', '&#x2B06;', '&#x2B08;', '&#x27A1;', '&#x2B0A;', '&#x2B07;', '&#x2B0B;', '&#x2B05;', '&#x2B09;', '&#xF500;', '&#x25BB;', '&#xF501;', '&#x25C5;', '&#x25B4;', '&#x25B9;', '&#x25BE;', '&#x25C3;', '&#x2B0C;', '&#xF503;', '&#xF505;', '&#x2397;', '&#x2398;', '&#xF600;', '&#xF601;');
