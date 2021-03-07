<?php

// 算術コマンド・論理コマンド
define("ADD", "add");
define("SUB", "sub");
define("NEG", "neg");
define("EQ", "eq");
define("GT", "gt");
define("LT", "lt");
define("M_AND", "and");
define("M_OR", "or");
define("M_NOT", "not");
// コマンドタイプ
define("C_ARITHMETIC", "C_ARITHMETIC");
define("C_PUSH", "C_PUSH");
define("C_POP", "C_POP");
define("C_LABEL", "C_LABEL");
define("C_GOTO", "C_GOTO");
define("C_IF", "C_IF");
define("C_FUNCTION", "C_FUNCTION");
define("C_RETURN", "C_RETURN");
define("C_CALL", "C_CALL");

// メモリアクセスコマンド
define("PUSH", "push");
define("POP", "pop");
// セグメント
define("ARGUMENT", "argument");
define("LOCAL", "local");
define("M_STATIC", "static");
define("CONSTANT", "constant");
define("THIS", "this");
define("THAT", "that");
define("POINTER", "pointer");
define("TEMP", "temp");