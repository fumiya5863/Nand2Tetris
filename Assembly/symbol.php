<?php

/*
|--------------------------------------------------------------------------
| シンボル
|--------------------------------------------------------------------------
*/

class Symbol {
    
    /**
     * シンボルテーブル
     *
     * @var array
     */
    public $symbolTable;

    /**
     * RAMアドレスの変数スタート値
     *
     * @var int
     */
    public $varRamAddress = 16;

    public function __construct()
    {
        $this->symbolTable = [
            "SP" => 0,
            "LCL" => 1,
            "ARG" => 2,
            "THIS" => 3,
            "THAT" => 4,
            "R0" => 0,
            "R1" => 1,
            "R2" => 2,
            "R3" => 3,
            "R4" => 4,
            "R5" => 5,
            "R6" => 6,
            "R7" => 7,
            "R8" => 8,
            "R9" => 9,
            "R10" => 10,
            "R11" => 11,
            "R12" => 12,
            "R13" => 13,
            "R14" => 14,
            "R15" => 15,
            "SCREEN" => 16384,
            "KBD" => 24576
        ];
    }

    /**
     * シンボルテーブルにニーモニックを追加
     *
     * @param string $symbol
     * @param int $address
     * @return void
     */
    public function addEntry($symbol, $address)
    {
        $this->symbolTable[$symbol] = $address;
    }

    /**
     * シンボルテーブルにニーモニックを追加しRAMのアドレスを進める
     *
     * @param string $symbol
     * @return void
     */
    public function countUpRamAddress($symbol)
    {
        $this->addEntry($symbol, $this->varRamAddress);
        $this->varRamAddress++;
    }

    /**
     * シンボルテーブルにニーモニックが登録されているかどうか確認
     *
     * @param string $symbol
     * @return bool
     */
    public function contains($symbol): bool
    {
        return isset($this->symbolTable[$symbol]);
    }
}