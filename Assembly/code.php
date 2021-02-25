<?php

/*
|--------------------------------------------------------------------------
| バイナリコード変換
|--------------------------------------------------------------------------
*/

class Code {

    /**
     * C命令のバイナリコードを取得
     *
     * @param string $dest
     * @param string $comp
     * @param string $jump
     * @return string
     */
    public function getBinaryCode($dest, $comp, $jump): string
    {
        $binaryCode = "111";
        return $binaryCode . Code::comp($comp) . Code::dest($dest) . Code::jump($jump);
    }

    /**
     * compニーモニックをバイナリコードに変換
     *
     * @param string $comp
     * @return string
     */
    private static function comp($comp): string
    {
        return COMP[$comp];
    }

    /**
     * destニーモニックをバイナリコードに変換
     *
     * @param  string $dest
     * @return string
     */
    private static function dest($dest): string
    {
        return DEST[$dest];
    }

    /**
     * jumpニーモニックをバイナリコードに変換
     *
     * @param string $jump
     * @return string
     */
    private static function jump($jump): string
    {
        return JUMP[$jump];
    }
}