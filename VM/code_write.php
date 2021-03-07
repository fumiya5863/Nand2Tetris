<?php

/*
|--------------------------------------------------------------------------
| アセンブリ変換
|--------------------------------------------------------------------------
*/

class CodeWrite {
    
    /**
     * ファイルポインタ
     *
     * @var void
     */
    private $filePoint;
    
    /**
     * ファイルの名前
     *
     * @var string
     */
    private $vmFileName;

    /**
     * 算術コマンド
     *
     * @var array
     */
    private $arithmeticOperations = [
        ADD => "D=D+M",
        SUB => "D=M-D",
        NEG => "M=-M",
        EQ => "JEQ",
        GT => "JGT",
        LT => "JLT",
        M_AND => "D=D&M",
        M_OR => "D=D|M",
        M_NOT => "M=!M"
    ];

    /**
     * メモリセグメント
     *
     * @var array
     */
    private $memorySegment = [
        LOCAL => "LCL",
        ARGUMENT => "ARG",
        THIS => "this",
        THAT => "that",
        TEMP => 5,
        POINTER => 3
    ];

    /**
     * ラベル番号
     *
     * @var integer
     */
    private $labelNum = 0;
    
    /**
     * ファイルの書き込みスタート
     *
     * @param string $asmFileName
     */
    public function __construct($asmFileName)
    {
        $this->filePoint = fopen($asmFileName, "a");
    }

    /**
     * ファイルの名前を設定
     *
     * @param string $vmFileName
     * @return void
     */
    public function setFileName($vmFileName)
    {
        $this->vmFileName = $vmFileName;
    }

    /**
     * 算術コマンドをアセンブリファイルに書き込み
     *
     * @param string $ArithmeticCommand
     * @return void
     */
    public function writeArithmetic($ArithmeticCommand)
    {
        if (in_array($ArithmeticCommand, [ADD, SUB, M_AND, M_OR])) {
            $this->writeBinaryOperation($ArithmeticCommand);
        } elseif(in_array($ArithmeticCommand, [EQ, GT, LT])) {
            $this->writeCompOperation($ArithmeticCommand);
        } elseif(in_array($ArithmeticCommand, [NEG, M_NOT])) {
            $this->writeUnaryOperation($ArithmeticCommand);
        }
    }

    /**
     * メモリアクセスコマンドをアセンブリファイルに書き込み
     *
     * @param string $memoryCommand
     * @param string $segment
     * @param string $index
     * @return void
     */
    public function writePushPop($memoryCommand, $segment, $index)
    {
        if ($memoryCommand === PUSH) {
            $this->pushRegister($segment, $index);
        } elseif($memoryCommand === POP) {
            $this->popRegister($segment, $index);
        }
    }

    /**
     * 書き込みファイルを閉じる
     *
     * @return void
     */
    public function close()
    {
        fclose($this->filePoint);
    }

    /**
     * 算術コマンド(2値の+,-or,and)
     *
     * @param string $ArithmeticCommand
     * @return void
     */
    private function writeBinaryOperation($ArithmeticCommand)
    {
        $this->writePopRegister();
        $this->writeAsmCode("D=M");
        $this->writePopRegister();
        $this->writeAsmCode($this->arithmeticOperations[$ArithmeticCommand]);
        $this->writePushRegister();   
    }

    /**
     * 算術コマンド(2値の比較)
     *
     * @param string $ArithmeticCommand
     * @return void
     */
    private function writeCompOperation($ArithmeticCommand)
    {
        $this->writePopRegister();
        $this->writeAsmCode("D=M");
        $this->writePopRegister();
        $l1 = $this->getNewLabel();
        $l2 = $this->getNewLabel();
        $this->writeAsmCode([
            "D=M-D",
            "@{$l1}",
            "D;{$this->arithmeticOperations[$ArithmeticCommand]}",
            "D=0",
            "@{$l2}",
            "0;JMP",
            "({$l1})",
            "({$l2})"
        ]);
        $this->writePushRegister();
    }

    /**
     * 算術コマンド(1値の!, not)
     *
     * @param string $ArithmeticCommand
     * @return void
     */
    private function writeUnaryOperation($ArithmeticCommand)
    {
        $this->writeAsmCode([
            "@SP",
            "A=M-1"
        ]);
        $this->writeAsmCode($this->arithmeticOperations[$ArithmeticCommand]);
    }

    /**
     * ラベルの値を1進める
     *
     * @return void
     */
    private function getNewLabel()
    {
        $this->labelNum += 1;
        return "LABEL" . $this->labelNum;
    }

    /**
     * メモリアクセスコマンド処理(push) 
     *
     * @param string $segment
     * @param string $index
     * @return void
     */
    private function pushRegister($segment, $index)
    {
        if ($segment === CONSTANT) {
            $this->writeAsmCode([
                "@{$index}",
                "D=A"
            ]);
            $this->writePushRegister();
        } elseif(in_array($segment, [LOCAL, ARGUMENT, THIS, THAT])) {
            $this->writePushFormVirtualSegment($segment, $index);
        } elseif(in_array($segment, [TEMP, POINTER])) {
            $this->writePushFormStaticSegment($segment, $index);
        }
        if ($segment === M_STATIC) {
            $this->writeAsmCode([
                "@".$this->vmFileName.".".$index
            ]);
            $this->writeAsmCode("D=M");
            $this->writePushRegister();
        }
    }

    /**
     * メモリアクセスコマンド処理(pop)
     *
     * @param string $segment
     * @param string $index
     * @return void
     */
    private function popRegister($segment, $index)
    {
        if(in_array($segment, [LOCAL, ARGUMENT, THIS, THAT])) {
            $this->writePopFromVirtualSegment($segment, $index);
        } elseif(in_array($segment, [TEMP, POINTER])) {
            $this->writePopFromStaticSegment($segment, $index);
        }
        if ($segment === M_STATIC) {
            $this->writePopRegister();
            $this->writeAsmCode([
                "D=M",
                "@".$this->vmFileName.".".$index
            ]);
            $this->writeAsmCode("D=M");
        }
    }

    /**
     * メモリアクセスコマンド(local, argument, this, that)
     *
     * @param string $segment
     * @param string $index
     * @return void
     */
    private function writePushFormVirtualSegment($segment, $index)
    {
        $registerName = $this->memorySegment[$segment];
        $this->writeAsmCode([
            "@".$registerName,
            "A=M"
        ]);

        for($i = 0; $i < $index; $i++) {
            $this->writeAsmCode("A=A+1");
        }
        $this->writeAsmCode("D=M");
        
        $this->writePushRegister();
    }

    /**
     * メモリアクセスコマンド(local, argument, this, that)
     *
     * @param string $segment
     * @param string $index
     * @return void
     */
    private function writePopFromVirtualSegment($segment, $index)
    {
        $registerName = $this->memorySegment[$segment];
        
        $this->writePopRegister();
        $this->writeAsmCode([
            "D=M",
            "@".$registerName,
            "A=M"
        ]);

        for($i = 0; $i < $index; $i++) {
            $this->writeAsmCode("A=A+1");
        }
        $this->writeAsmCode("M=D");
    }

    /**
     * メモリアクセスコマンド(tmp, pointer)
     *
     * @param string $segment
     * @param string $index
     * @return void
     */
    private function writePushFormStaticSegment($segment, $index)
    {
        $baseAddress = $this->memorySegment[$segment];
        $this->writeAsmCode([
            "@".$baseAddress
        ]);
        for ($i = 0; $i < $index; $i++) {
            $this->writeAsmCode("A=A+1");
        }
        $this->writeAsmCode("D=M");
        $this->writePushRegister();
    }

    /**
     * メモリアクセスコマンド(tmp, pointer)
     *
     * @param string $segment
     * @param string $index
     * @return void
     */
    private function writePopFromStaticSegment($segment, $index)
    {
        $baseAddress = $this->memorySegment[$segment];
        $this->writePopRegister();
        $this->writeAsmCode([
            "D=M",
            "@".$baseAddress
        ]);
        for ($i = 0; $i < $index; $i++) {
            $this->writeAsmCode("A=A+1");
        }
        $this->writeCode("M=D");
    }

    /**
     * SPを1増やす(スタックにpush)
     *
     * @return void
     */
    private function writePushRegister()
    {
        $this->writeAsmCode([
            "@SP",
            "A=M",
            "M=D",
            "@SP",
            "M=M+1"
        ]);
    }
    
    /**
     * SPを1減らす(スタックにpop)
     *
     * @return void
     */
    private function writePopRegister()
    {
        $this->writeAsmCode([
            "@SP",
            "M=M-1",
            "A=M"
        ]);
    }

    /**
     * ファイルに書き込み
     *
     * @param array|string $codes
     * @return void
     */
    private function writeAsmCode($codes)
    {
        if (is_array($codes)) {
            $code = implode("\n", $codes);
        } else {
            $code = $codes;
        }
        fwrite($this->filePoint, $code . "\n");
    }
    
}