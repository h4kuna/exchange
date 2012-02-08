<?php

namespace Exchange25;
use DibiConnection, Utility\NonObject;

require_once 'ICnbDb.php';

class CnbDbCreate extends NonObject implements ICnbDb
{
    public static function renderSqliteDb()
    {
        return array(
'CREATE TABLE ['. self::T_CNB_HISTORY .'] (
    ['. self::C_ID_HISTORY .'] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    ['. self::C_DATE .'] DATE UNIQUE
);
',
'CREATE TABLE ['. self::T_CNB_RATE .'] (
    ['. self::C_ID_RATE .'] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    ['. self::C_OID_CURRENCY .'] INTEGER NOT NULL,
    ['. self::C_OID_HISTORY .'] INTEGER NOT NULL,
    ['. self::C_TO .'] DECIMAL(14,7)  NOT NULL,
    ['. self::C_RATE .'] DECIMAL(14,7) DEFAULT (0),
    ['. self::C_STATUS .'] DECIMAL(5,2) DEFAULT (0),
    UNIQUE ( ['. self::C_OID_CURRENCY .'], ['. self::C_OID_HISTORY .'])
);',
'CREATE TABLE ['. self::T_CNB .'] (
    ['. self::C_ID_CURRENCY .'] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    ['. self::C_FORMAT .'] TEXT,
    ['. self::C_CODE .'] TEXT UNIQUE NOT NULL,
    ['. self::C_DECIMAL .'] INTEGER NOT NULL DEFAULT (2),
    ['. self::C_DECPOINT .'] TEXT NOT NULL DEFAULT (","),
    ['. self::C_THOUSANDS .'] TEXT NOT NULL DEFAULT (" "),
    ['. self::C_SYMBOL .'] TEXT,
    ['. self::C_FROM .'] DECIMAL(5,0) NOT NULL DEFAULT (1),
    ['. self::C_COUNTRY .'] TEXT,
    ['. self::C_NAME .'] TEXT
);',
'CREATE TRIGGER update_rate AFTER INSERT ON ['. self::T_CNB_RATE .']
BEGIN
  UPDATE ['. self::T_CNB_RATE .'] SET ['. self::C_RATE .'] =
      ((SELECT ['. self::C_FROM .'] FROM ['. self::T_CNB .']
      WHERE new.'. self::C_OID_CURRENCY .'='. self::C_ID_CURRENCY .') / ['. self::C_TO .'] )
  WHERE '. self::C_OID_CURRENCY .' = new.'. self::C_OID_CURRENCY .';
  UPDATE ['. self::T_CNB_RATE .'] SET ['. self::C_STATUS .'] = ((['. self::C_RATE .'] /
        (SELECT ['. self::C_RATE .']
        FROM ['. self::T_CNB_RATE .']
        WHERE ['. self::C_OID_CURRENCY .'] = new.'. self::C_OID_CURRENCY .' AND ['. self::C_OID_HISTORY .'] < new.'. self::C_OID_HISTORY .'
        GROUP BY ['. self::C_OID_CURRENCY .']
        LIMIT 1)) * 100)
   WHERE ['. self::C_ID_RATE .'] = new.'. self::C_ID_RATE .';
END;');
    }


    public static function createDb(DibiConnection $db)
    {
        if(@!filesize($db->getConfig('database'))) //@ file doesn't exists
        {
            $db->begin();
            foreach (self::renderSqliteDb() as $sql)
                $db->query($sql);
            $db->commit();
        }
    }
}
