<?php

/*
|--------------------------------------------------------------------------
| パーサーモジュール
|--------------------------------------------------------------------------
*/

class Parser {

    /**
     * ファイルポインタ
     *
     * @var mixed
     */
    private $fpRead;
    
    /**
     * 入力ファイルの行
     *
     * @var string
     */
    private $fileLine;

    /**
     * ファイルから受けっとた行を整形した値
     *
     * @var string
     */
    private $formatCommand;
    
    /**
     * 入力ファイルのポインタを受け取る
     *
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        $this->fpRead = fopen($fileName, "r");
    }

    /**
     * 次のコマンドがあるか確認
     *
     * @return boolean
     */
    public function hasMoreCommands(): bool
    {
        $this->fileLine = fgets($this->fpRead);
        if (!$this->fileLine) {
            return false;
            fclose($this->fpRead);
        }
        return true;
    }

    /**
     * 整形したコマンドの有無
     *
     * @return bool
     */
    public function advance(): bool
    {
        $this->formatCommand = substr($this->fileLine, 0, strcspn($this->fileLine, "//"));
        $this->formatCommand = rtrim($this->formatCommand);
        $this->formatCommand = str_replace(" ", "", $this->formatCommand);

        if ($this->formatCommand === "") {
            return false;
        }
        return true;
    }

    /**
     * 入力ファイルから受け取った値のコマンドの種類を返却
     *
     * @return string
     */
    public function commandType(): string
    {   
        if ($this->formatCommand[0] === "@") {
            $key = 0;
        } elseif(strpos($this->formatCommand, "=") !== false || strpos($this->formatCommand, ";")) {
            $key = 1;
        } else {
            $key = 2;
        }

        return COMMAND_TYPE[$key];
    }

    /**
     * シンボルを返却
     *
     * @return string
     */
    public function symbol(): string
    {
        if($this->formatCommand[0] === "@") {
           return str_replace("@", "", $this->formatCommand); 
        }
        return str_replace(["(", ")"], "", $this->formatCommand);
    }

    /**
     * destニーモニック返却
     *
     * @return string
     */
    public function dest(): string
    {
        if (strpos($this->formatCommand, "=")  === false) {
            return "null";
        }
        $formatCommand = $this->formatCommand;
        return explode("=", $formatCommand)[0];
    }

    /**
     * compニーモニックを返却
     *
     * @return string
     */
    public function comp(): string
    {
        $formatCommand = $this->formatCommand;
        if (strpos($formatCommand, "=") !== false && strpos($formatCommand, ";") !== false) {
            $tmp = explode("=", $formatCommand);
            $tmp = explode(";", $tmp[1]);
            $formatCommand = $tmp[0];   
        } elseif(strpos($formatCommand, "=") !== false) {
            $tmp = explode("=", $formatCommand);
            $formatCommand = $tmp[1];
        } elseif(strpos($formatCommand, ";") !== false) {
            $tmp = explode(";", $formatCommand);
            $formatCommand = $tmp[0];
        }

        return $formatCommand;
    }

    /**
     * jumpニーモニックを返却
     *
     * @return string
     */
    public function jump(): string
    {
        if (strpos($this->formatCommand, ";") === false) {
            return "null";
        }

        $formatCommand = $this->formatCommand;
        return explode(";", $formatCommand)[1];
    }
}