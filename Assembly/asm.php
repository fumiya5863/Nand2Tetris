<?php

/*
|--------------------------------------------------------------------------
| Hackコンピュータのアセンブリを機械語に変換するアセンブラ(PHPバージョン)
|--------------------------------------------------------------------------
*/

require_once("constants.php");
require_once("parser.php");
require_once("code.php");
require_once("symbol.php");

$assembly = "Add";

$parser = new Parser($assembly . ".asm");
$symbol = new Symbol();
$array = [];
$romAddress = 0;

// 読み込み処理
while($parser->hasMoreCommands()) {
    $binaryCode = "";
    if (!$parser->advance()) {
        continue;
    }

    if($parser->commandType() === COMMAND_TYPE[1]) {
        $dest = $parser->dest();
        $comp = $parser->comp();
        $jump = $parser->jump();
        $binaryCode = Code::getBinaryCode($dest, $comp, $jump);

        $array[] = $binaryCode;
        $romAddress++;
    } elseif($parser->commandType() === COMMAND_TYPE[0]) {
        if (is_numeric($parser->symbol())) {
            $tmp= decbin($parser->symbol());
            $binaryCode = sprintf("%016d", $tmp);
            $array[] = $binaryCode;
        } else {
            $array[] = $parser->symbol();
        }
        $romAddress++;
    } else {
        $symbol->addEntry($parser->symbol(), $romAddress);
    }
}

// 書き込み処理
$fpWrite = fopen($assembly . ".hack", "a");
foreach($array as $value) {
    $binaryCode = $value;
    if (!is_numeric($binaryCode)) {
        if ($symbol->contains($binaryCode)) {
            $tmp = decbin($symbol->symbolTable[$binaryCode]);
            $binaryCode = sprintf("%016d", $tmp);
        } else {
            $symbolValue = $binaryCode;
            $tmp = decbin($symbol->varRamAddress);
            $binaryCode = sprintf("%016d", $tmp);
            
            $symbol->countUpRamAddress($symbolValue);
        }
    }
    fwrite($fpWrite, $binaryCode . "\n");
}
fclose($fpWrite);