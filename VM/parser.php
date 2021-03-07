<?php

/*
|--------------------------------------------------------------------------
| vmのファイルをパーサー
|--------------------------------------------------------------------------
*/

class Parser {
    
    /**
     * ファイルポインタ
     *
     * @var mixed
     */
    private $filePoint;

    /**
     * ファイルライン
     *
     * @var string
     */
    private $fileLine;

    /**
     * 整形コマンド
     *
     * @var string
     */
    private $formatCommand;

    /**
     * 整形コマンド(配列)
     *
     * @var array
     */
    public $formatDivideCommand;

    /**
     * コマンド種類
     *
     * @var array
     */
    private $commandTypeValue = [
        ADD => C_ARITHMETIC,
        SUB => C_ARITHMETIC,
        NEG => C_ARITHMETIC,
        EQ => C_ARITHMETIC,
        GT => C_ARITHMETIC,
        LT => C_ARITHMETIC,
        M_AND => C_ARITHMETIC,
        M_OR => C_ARITHMETIC,
        M_NOT => C_ARITHMETIC,
        PUSH => C_PUSH,
        POP => C_POP,
    ];
    
    public function __construct($fileName)
    {
        $this->filePoint = fopen($fileName, "r");
    }

    /**
     * ファイルの次のラインが存在するかどうか
     *
     * @return bool
     */
    public function hasMoreCommands(): bool
    {
        $this->fileLine = fgets($this->filePoint);
        if (!$this->fileLine) {
            fclose($this->filePoint);
            return false;
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

        if ($this->formatCommand === "") {
            return false;
        }

        $this->formatDivideCommand = explode(" ", $this->formatCommand);
        return true;
    }

    /**
     * コマンドの種類を取得
     *
     * @return string
     */
    public function commandType(): string
    {
        return $this->commandTypeValue[$this->formatDivideCommand[0]];
    }

    /**
     * 現在のコマンドの最初の引数を取得
     *
     * @return string
     */
    public function arg1(): string
    {
        if ($this->commandTypeValue[$this->formatDivideCommand[0]] === C_ARITHMETIC) {
            return $this->formatDivideCommand[0];
        } else {
            return $this->formatDivideCommand[1];
        }
    }

    /**
     * 現在のコマンドの第2引数を取得
     *
     * @return string
     */
    public function arg2(): string
    {
        return $this->formatDivideCommand[2];
    }
    
}