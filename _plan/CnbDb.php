<?php

namespace Exchange25;
use Dibi, DibiException, DibiDriverException;
use Utility\DateTime, Utility\Feast;

require_once 'Download.php';
require_once 'CnbDbCreate.php';

/**
 * need SqLite3 and Dibi
 *
 * @author Milan Matějček
 */
class CnbDb extends Download implements ICnbDb, ICnb
{
    //protected $refresh = 1;

    /** @var DibiConnection */
    protected $db;

    /** @var DateTime */
    protected $date = 0;

    /**
     * download sql
     * @var string
     */
    protected $sql = NULL;

    /** @var bool */
    protected $loadBoth = TRUE;

    /**
     * only for first update
     * @var bool
     */
    private $update = FALSE;

    public function __construct(ExchangePoint $parent, $date='NOW', $connection='exchange')
    {
        $this->db = dibi::getConnection($connection);
        $this->db->getDriver()->registerFunction('correction', \callback($this, 'correction'), 2);
        $this->date = new DateTime($date);
        parent::__construct($parent);
    }

//-----------------callback for sql---------------------------------------------
    /**
     * this method is registred for SqLite3
     * @param tring $code
     * @param int $rate
     * @return float
     */
    final public function correction($code, $rate)
    {
        if($rate === NULL)
            $rate = 1;
        return $this->exchange->getDefault() == $code? $rate: $rate/$this->correction;
    }

//-----------------download actual rating list----------------------------------
    /**
     * download and prepare cache
     */
    protected function loadList()
    {
        $new = $this->getFile();

        $download = FALSE;
        if(!file_exists( $new ) || filesize($this->db->getConfig('database')) < 1)
        {//stahne rocni data
            $yyyy = $this->date->format('Y');
            $download = self::CNB_YEAR . $yyyy;
            $download2 = self::CNB_YEAR2 . $yyyy;
            CnbDbCreate::createDb($this->db);
        }
        elseif((time() - @filemtime( $new ) > $this->refresh) || $this->update)
        {
            $download = self::CNB_DAY;
            $download2 = self::CNB_DAY2;
        }

        if( $download )
        {
            $cnb2 = TRUE;
            if(ini_get('allow_url_fopen') && parent::$proxyName === NULL)
            {
                $cnb = @file_get_contents($download);
                if($this->loadBoth)
                    $cnb2 = @file_get_contents($download2);
            }
            elseif(extension_loaded('curl'))
            {
                $curl = new CUrl($download);
                $this->setProxy($curl);
                $cnb = $curl->getResult();
                if($this->loadBoth)
                {
                    $curl = new CUrl($download2);
                    $this->setProxy($curl);
                    $cnb2 = $curl->getResult();
                    if(\strlen($cnb2) < 10)
                        $cnb2 = '';
                }
            }
            else
            {
                throw new ExchangeException('This library need allow_url_fopen -enable or curl extension');
            }

            if( $cnb !== FALSE && $cnb2 !== FALSE )
            {
                $this->createCache(parent::stroke2point($cnb . $cnb2));
            }
            else
            {
                //@todo vytvorit kontrolu pres db
                throw new ExchangeException('You must connect to internet. It can\'t download rating list');
            }
        }
    }


//-----------------methods whose create cache ----------------------------------
    /**
     * save source to db and setup control file
     * @param string $cnb
     * @param string $file
     */
    protected function createCache($cnb)
    {
        if($this->isDateLine($cnb))
        {//uloží roční data
            $this->createYearCache($cnb);
            file_put_contents($this->file, '');
            $this->update = TRUE;
            $this->loadList();
        }
        else
        {//uloží denní data
            $this->createDayCache($cnb);
            $this->update = FALSE;
            touch($this->file);
        }
    }

    /**
     * parser for year statistic
     * @param string $cnb
     */
    protected function createYearCache(&$cnb)
    {
        $rows = explode("\n", trim($cnb));
        $dateLine = NULL;
        foreach($rows as $row)
        {
            if($this->isDateLine($row))
            {
                $dateLine = $this->saveCodeRow($row);
            }
            else
            {
                $this->saveRateRow($row, $dateLine);
            }
        }
    }

    /**
     * parser for day statistics
     * @param string $cnb
     * @return void
     */
    protected function createDayCache(&$cnb)
    {
        $cnb = explode("\n", trim($cnb));
        $info = explode(' #', $cnb[0]);
        $cnb[0] = self::CNB_CZK;
        unset($cnb[1]);

        $idHis = $this->insertHistory($info[0]);
        $codes = $this->getAllCode(TRUE);//vrací pole [CZK]=>id
        $data = array(self::C_CODE=>0, self::C_COUNTRY=>0, self::C_NAME=>0,
                        self::C_FROM=>0, self::C_TO=>0, self::C_ID_CURRENCY=>0);

        $this->db->begin();
        foreach($cnb as $key => &$val)
        {
            $val = explode(self::PIPE, $val);

            if(count($val) != 5)
                continue;
            list($data[self::C_COUNTRY], $data[self::C_NAME], $data[self::C_FROM],
                    $data[self::C_CODE], $data[self::C_TO]) = $val;
            unset($val);

            $numFormat = $this->createFormat($data[self::C_CODE]);
            $to = $data[self::C_TO];
            unset($numFormat[self::C_RATE], $data[self::C_TO]);
            $data[self::C_ID_CURRENCY] = isset($codes[$data[self::C_CODE]])? $codes[$data[self::C_CODE]]: 0;
            $id = $this->replaceCurrency($data+$numFormat);

            $this->insertRating($data[self::C_CODE], $id, $idHis, $to);###
        }
        $this->db->commit();
    }

    /**
     * save value to table T_CNB
     * @param string $row
     * return array
     */
    protected function & saveCodeRow(&$row)
    {
        $dateLine = explode(self::PIPE, $row);
        unset($dateLine[0]);
        $insert = array(self::C_CODE=>NULL, self::C_FROM=>NULL);

        $this->db->begin();
        foreach($dateLine as $key => $val)
        {
            list($insert[self::C_FROM], $insert[self::C_CODE]) = explode(' ', $val);
            $dateLine[$key] = $this->insertCurrency($insert);
        }
        $this->db->commit();
        return $dateLine;
    }

    /**
     * save rows to table T_CNB_RATE
     * @param string $row
     * @param array $dateLine
     * @return void
     */
    protected function saveRateRow(&$row, &$dateLine)
    {
        $rateLine = explode(self::PIPE, $row);
        $id = $this->insertHistory($rateLine[0]);
        unset($rateLine[0]);
        $insert = array(self::C_OID_CURRENCY=>0, self::C_OID_HISTORY=>$id, self::C_TO=>0.0);

        $this->db->begin();
        foreach ($rateLine as $key => $val)
        {
            $insert[self::C_OID_CURRENCY] = $dateLine[$key];
            $insert[self::C_TO] = (float)$val;
            $this->insertRate($insert, $dateLine[$key]);
        }
        $this->db->commit();
    }

//-----------------helper for sql query-----------------------------------------
    /**
     * check if is line with date for year list
     * @param string $str
     */
    protected function isDateLine(&$str)
    {
        return substr($str, 0, 5) == 'Datum';
    }

    /**
     * prepare sql column for another use
     * @return string
     */
    protected function getSql()
    {
        if($this->sql === NULL)
        {
            $this->sql = '`'. \implode('`, `', \array_keys($this->getProperty())) .'`'
                       . ', correction(`'. self::C_CODE .'`, `'. self::C_RATE .'`) AS `'. self::C_RATE .'`, '.
                          'IFNULL(`'. self::C_DATE .'`, DATE(\'now\')) AS `'. self::C_DATE .'`';
        }
        return $this->sql;
    }

//-----------------inherit methods----------------------------------------------
    /**
     * load currency by code
     * @param string $code
     * @param string ...
     */
    public function loadCurrency($code)
    {
        $args = \func_get_args();
        $this->loadCurrencies($args);
    }

    public function loadCurrencies(array & $codes)
    {
        $code = \array_diff_key(array_flip($codes), $this->exchange->getArrayCopy());

        if(empty($code))
            return;
        $result = $this->db->query(
            'SELECT '. $this->getSql() .'
            FROM `'. self::T_CNB .'`
            LEFT JOIN ['. self::T_CNB_RATE .'] ON ['. self::C_OID_CURRENCY .'] = ['. self::C_ID_CURRENCY .']
            LEFT JOIN ['. self::T_CNB_HISTORY .'] ON ['. self::C_OID_HISTORY .'] = ['. self::C_ID_HISTORY .']
                AND (`'. self::C_DATE .'` <= \''. $this->date .'\' OR `'. self::C_DATE .'` IS NULL)
            WHERE `'. self::C_CODE .'` IN %in
            GROUP BY `'. self::C_CODE .'`
            ORDER BY `'. self::C_DATE .'` DESC',
            \array_keys($code));
        foreach ($result as $v)
        {
            $this->exchange->offsetSet($v[self::C_CODE], (array)$v);
        }

    }

    /**
     * @param $codeSort make nothing
     * @return array
     */
    public function & getAllCode($codeAsId=FALSE)
    {
        $codes = $this->db->query(
            'SELECT %n, %n
            FROM %n
            ORDER BY %n ASC',
            self::C_CODE, self::C_ID_CURRENCY,
            self::T_CNB,
            self::C_CODE);
        $codes = ($codeAsId)? $codes->fetchPairs(self::C_CODE, self::C_ID_CURRENCY):
                $codes->fetchPairs(self::C_ID_CURRENCY, self::C_CODE);
        return $codes;
    }

    /**
     * setUp mandatory column
     * @return array
     */
    final public function getProperty()
    {
        return parent::getProperty() + array(self::C_CODE=>0);
    }

//-----------------sql-----------------
    /**
     * create from 22.03.2010 to 2010-03-22
     * @param string $czechDate 1.1.2011, 01.01.2011
     * @return int id of history
     */
    private function insertHistory($czechDate)
    {
        $czech = Feast::czechDate2Sql($czechDate);
        try {
            $this->db->query('INSERT INTO %n (%n) VALUES (%s)',
                self::T_CNB_HISTORY, self::C_DATE, $czech);
            $idHis = $this->db->getInsertId();
        }catch (DibiDriverException $e)
        {
            if($e->getCode() != 19)
            {
                $this->db->rollback();
                throw $e;
            }
            $idHis = $this->db->query('SELECT %n FROM %n WHERE %n=%s  LIMIT 1',
                     self::C_ID_HISTORY ,self::T_CNB_HISTORY, self::C_DATE, $czech)
                              ->fetchSingle();
        }
        return $idHis;
    }

    /**
     *
     * @param array $insert
     * @return int id
     */
    private function insertCurrency(array $insert)
    {
        try
        {
            $this->db->query('INSERT INTO %n %v', self::T_CNB, $insert);
            return $this->db->getInsertId();
        }
        catch (DibiException $e)
        {
            if($e->getCode() != 19)
            {
                $this->db->rollback();
                throw $e;
            }
            return $this->db->query('SELECT %n FROM %n WHERE %and LIMIT 1',
                            self::C_ID_CURRENCY, self::T_CNB, $insert)->fetchSingle();
        }
    }

    private function insertRate(array $insert, $id)
    {
        if($insert[self::C_TO] <= 0)
        {
            $this->db->query('DELETE FROM %n WHERE %and', self::T_CNB, array(self::C_ID_CURRENCY=>$id));
            return;
        }
        $this->db->query('INSERT OR IGNORE INTO %n %v', self::T_CNB_RATE, $insert);
    }

    private function insertRating($code, $id, $idHis, $to)
    {
        if($code !== self::CZK)
        {
            try
            {
                $this->db->query('INSERT INTO %n %v', self::T_CNB_RATE,
                    array(self::C_OID_CURRENCY => $id,
                          self::C_OID_HISTORY => $idHis,
                          self::C_TO => $to));
            }
            catch (DibiDriverException $e)
            {
                if($e->getCode() != 19)
                {
                    $this->db->rollback();
                    throw $e;
                }
            }
        }
    }

    private function replaceCurrency($data)
    {
        $id = $data[self::C_ID_CURRENCY];
        unset($data[self::C_ID_CURRENCY]);
        if($id == 0)
        {
            $this->db->query('INSERT INTO %n %v', self::T_CNB, $data);
            return $this->db->getInsertId();
        }


        $this->db->query('UPDATE %n SET %a WHERE %n=%i', self::T_CNB, $data,
                        self::C_ID_CURRENCY, $id);
        return $id;

    }

}
