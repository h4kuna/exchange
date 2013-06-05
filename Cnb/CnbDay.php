<?php

namespace h4kuna;

class CnbDay extends Download implements ICnb {

    protected $links = array(self::CNB_DAY);

    /**
     * download resource
     * @return array
     */
    public function downloading() {
        $data = explode("\n", Math::stroke2point(trim($this->getData())));
        $data[0] = explode(' #', $data[0]);
        $data[1] = self::CNB_CZK;
        return $this->save($data);
    }

    public function addAnother() {
        $this->links[] = self::CNB_DAY2;
    }

    protected function save($data) {
        $code = array($data[0]);
        unset($data[0]);
        foreach ($data as $val) {
            $ex = explode(self::PIPE, $val);
            if (count($ex) != 5 || $ex[self::TO] <= 0 || isset($code[$ex[self::TO]])) {
                continue;
            }

            $obj = $code[$ex[self::CODE]] = new Currency($ex[self::CODE], $ex[self::HOME], $ex[self::TO]);
            $obj->country = $ex[self::COUNTRY];
            $obj->name = $ex[self::NAME];
        }
        return $code;
    }

    /**
     * data downloaded by CUrl
     * @return string
     */
    protected function curl() {
        $curl = $this->getCurl();
        $cnb = NULL;
        foreach ($this->links as $key => $link) {
            $curl->setopt(CURLOPT_URL, $this->fillDate($link));

            if ($curl->errno() > 0) {
                if ($key == 0)
                    throw new ExchangeException('Let\'s check internet connection.');
                continue;
            }

            $cnb .= $curl->exec();
        }
        return $cnb;
    }

    /**
     * data downloaded by file_get_contents
     * @return string
     */
    protected function fopen() {
        $cnb = NULL;
        foreach ($this->links as $key => $link) {
            $data = file_get_contents($this->fillDate($link));
            if ($data === FALSE) {
                if ($key == 0)
                    throw new ExchangeException('Let\'s check internet connection.');
                continue;
            }

            $cnb .= $data;
        }
        return $cnb;
    }

    /**
     * apply date for download
     * @return void
     */
    private function fillDate($link) {
        if ($this->date) {
            $date = $this->date->format('d.m.Y');
            $link .= self::CNB_PARAM . $date;
        }
        return $link;
    }

}
