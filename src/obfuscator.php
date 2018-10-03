#!/usr/bin/php
<?php
const ARG_DUMP = 1,
      ARG_DUMP_NEW = 2,
      ARG_CONF = 3;

if ($argc != 4 ) {
    echo 'Укажите аргумены!' . PHP_EOL;
    echo 'Пример команды:' . PHP_EOL;
    echo '`php obfuscator.php dump.sql newdump.sql conf.php`' . PHP_EOL;
    echo 'dump.sql newdump.sql conf.php`' . PHP_EOL;

    return;
}

$conf = require_once($argv[ARG_CONF]);

if (count($conf) <= 0) {
    echo 'Файл конфигурации пуст!';
    return;
}


$old_file = fopen($argv[ARG_DUMP], "r") or exit("Невозможно открыть {$argv[ARG_DUMP]}");
$new_file = fopen($argv[ARG_DUMP_NEW], "w") or exit("Невозможно создать {$argv[ARG_DUMP_NEW]}");

$insert = false;
while(!feof($old_file))
{
    $line = fgets($old_file);

    if (preg_match('/INSERT+\sINTO/', $line)) {
        $insert = true;

        preg_match_all('/INSERT+\sINTO+\s`(.*)`+\s\((.*)\)/', $line, $table_parsed);
        $table_name = $table_parsed[1][0];
        $fields = str_replace('`', '', $table_parsed[2][0]);
        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $keys = array_intersect($fields, array_flip($conf[$table_name]));

        fwrite($new_file, $line);
        continue;
    }

    if ($insert) {
        if (preg_match('/VALUES/', $line)) {
            fwrite($new_file, $line);
            continue;
        }

        preg_match_all('/\((.*)\)/', $line, $values_parsed);
        $values_parsed = $values_parsed[1][0];
        $values_parsed = explode(',', $values_parsed);

        foreach ($keys as $key => $name) {
            $values_parsed[$key] = get_value($conf[$table_name][$name]);
        }

        $_line = '(' . implode(',', $values_parsed) . ')';

        if (preg_match('/;/', $line)) {
            $insert = false;
            $line = $_line . ';' .  PHP_EOL;
        } else {
            $line = $_line . ',' .  PHP_EOL;
        }
    }

    fwrite($new_file, $line);
}

function get_value($type) {
    $type = explode(':', $type);

    //Если есть значение по-умолчанию
    if (count($type) > 1) {
        return $type[1];
    }

    switch ($type) {
        case 'string':
            $result = string_value();
            break;

        case 'email':
            $result = email_value();
            break;

        case 'integer':
            $result = int_value();
            break;

        case 'phone':
            $result = phone_value();
            break;

        default:
            $result = string_value();
    }

    return $result;
}

function string_value() {
    return "'Тест'";
}

function email_value() {
    return "'test@test.ru'";
}

function phone_value() {
    return "'+7" . rand(900, 999) . rand(1000000, 9999999) . "'";
}

function int_value() {
    return rand(1, 999999);
}

fclose($old_file);
fclose($new_file);

