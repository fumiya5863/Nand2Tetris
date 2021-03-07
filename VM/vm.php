<?php

require_once("constants.php");
require_once("parser.php");
require_once("code_write.php");

$folderName = "SimpleAdd";
$asmFileName = $folderName . ".asm";
$filePaths = glob($folderName."/*.vm");

$codeWrite = new CodeWrite($asmFileName);

foreach($filePaths as $filePath) {
    $parser = new Parser($filePath);
    $codeWrite->setFileName(pathinfo(basename($filePath), PATHINFO_FILENAME));
    while($parser->hasMoreCommands()) {
        if (!$parser->advance()) {
            continue;
        }
        $notCommandReturn = $parser->commandType() !== C_RETURN;
        $targetCommand = in_array($parser->commandType(), [C_PUSH, C_POP, C_FUNCTION, C_CALL]);
        
        if ($notCommandReturn && $targetCommand) {
            $command = $parser->commandType() === C_PUSH ? PUSH : POP;
            $codeWrite->writePushPop($command, $parser->arg1(), $parser->arg2());
        } elseif($notCommandReturn) {
            $codeWrite->writeArithmetic($parser->arg1());
        }
    }
}

$codeWrite->close();