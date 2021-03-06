<?php
/**
 * Файл конфигурации
 *
 * имя таблицы => список полей, данные которых мы очищаем
 *
 * Доступные значения:
 * string - Строка
 * integer - Число от 1 до 999999
 * phone - Номер мобильного телефона
 * email - E-mail
 *
 * Если нужны значения по-умолчанию, указываем его как :значение
 *
*/

return [
    'tablename' => [
        'email' => 'email',
        'telephone' => 'phone',
        'name' => ':Test',
    ],
];