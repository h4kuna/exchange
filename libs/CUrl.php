<?php
/*
    * curl_multi_exec — Run the sub-connections of the current cURL handle
*/

namespace Exchange;

use Nette;




class CUrl extends Nette\Object
{
    const OPT = 'CURLOPT_';
    const INFO = 'CURLINFO_';

    /**
     * Get information regarding a specific transfer, function curl_getinfo
     * @var array

    static protected $info    =array(
        'CURLINFO_EFFECTIVE_URL', //Last effective URL
        'CURLINFO_HTTP_CODE', //Last received HTTP code
        'CURLINFO_FILETIME', //Remote time of the retrieved document, if -1 is returned the time of the document is unknown
        'CURLINFO_TOTAL_TIME', //Total transaction time in seconds for last transfer
        'CURLINFO_NAMELOOKUP_TIME', //Time in seconds until name resolving was complete
        'CURLINFO_CONNECT_TIME', //Time in seconds it took to establish the connection
        'CURLINFO_PRETRANSFER_TIME', //Time in seconds from start until just before file transfer begins
        'CURLINFO_STARTTRANSFER_TIME', //Time in seconds until the first byte is about to be transferred
        'CURLINFO_REDIRECT_TIME', //Time in seconds of all redirection steps before final transaction was started
        'CURLINFO_SIZE_UPLOAD', //Total number of bytes uploaded
        'CURLINFO_SIZE_DOWNLOAD', //Total number of bytes downloaded
        'CURLINFO_SPEED_DOWNLOAD', //Average download speed
        'CURLINFO_SPEED_UPLOAD', //Average upload speed
        'CURLINFO_HEADER_SIZE', //Total size of all headers received
        'CURLINFO_HEADER_OUT', //The request string sent
        'CURLINFO_REQUEST_SIZE', //Total size of issued requests, currently only for HTTP requests
        'CURLINFO_SSL_VERIFYRESULT', //Result of SSL certification verification requested by setting CURLOPT_SSL_VERIFYPEER
        'CURLINFO_CONTENT_LENGTH_DOWNLOAD', //content-length of download, read from Content-Length: field
        'CURLINFO_CONTENT_LENGTH_UPLOAD', //Specified size of upload
        'CURLINFO_CONTENT_TYPE', //Content-Type: of downloaded object, NULL indicates server did not send valid Content-Type: header
    );*/

    /**
     * curl handle
     * @var handle
     */
    protected $handle = false;

    /**
     *
     * @param $url
     * @return void
     */
    public function __construct($url=false, array $options=null)
    {
        $this->handle = curl_init();
        if($options === null)
            $options = array(CURLOPT_HEADER=>false, CURLOPT_RETURNTRANSFER=>true);

        if($url !== false)
            $options += array(CURLOPT_URL=>$url);

        $this->setOptions($options);
    }

    public function __set($name, $value)
    {
        $val = \strtoupper($name);
        if(\defined(self::OPT . $val))
        {
            return $this->setOption(constant(self::OPT . $val), $value);
        }

        if('HEADER_OUT' === $val)
        {
            return $this->setOption(CURLINFO_HEADER_OUT, $value);
        }

        return parent::__set($name, $value);
    }

    public function &__get($name)
    {
        $val = \strtoupper($name);
        if(\defined(self::INFO . $val))
        {
            $a = $this->getInfo(constant(self::INFO . $val));
            return $a;
        }
        return parent::__get($name);
    }
/*
    public function __sleep()
    {
        $handle = \serialize($this->handle);
        return array($handle);
    }

    public function __wakeUp()
    {

    }
*/
    /**
     * nastavi jednu volbu
     * @param $const
     * @param $value
     * @return bool
     */
    public function setOption($const, $value)
    {
        return curl_setopt($this->handle, $const, $value);
    }

    /**
     * nastavi vicero voleb
     * @param $options
     * @return int
     */
    public function setOptions(array $options)
    {
        return curl_setopt_array($this->handle, $options);
    }

    /**
     * @deprecated
     * @return string|false
     */
    public function getResult()
    {
        $a = $this->exec();
        if(!$a)
            $this->getErrors();

        return $a;
    }

    /**
     *
     * @return string|false
     */
    public function exec()
    {
        return curl_exec($this->handle);
    }

    /** @return CUrl */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     *
     * @param int $opt
     * @return sring
     */
    public function getInfo($opt=true)
    {
        if($opt === true)
            return curl_getinfo($this->handle);

        return curl_getinfo($this->handle, $opt);
    }

    /**
     * vrati kopii ukazatele na sesion
     * @return pointer
     */
    public function getCopy()
    {
        return curl_copy_handle($this->handle);
    }

    /**
     * vypise chybu
     * @return void
     */
    public function getErrors()
    {
        throw new CUrlException($this->getError(), $this->getErrorNumber());
    }

    /**
     * vrati cislo chyby
     * @return int
     */
    public function getErrorNumber()
    {
        return curl_errno($this->handle);
    }

    /**
     * vrati popis chyby
     * @return string
     */
    public function getError()
    {
        return curl_error($this->handle);
    }

    public static function getVersion($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }

    protected function closeInternal()
    {
        return curl_close($this->handle);
    }

    /**
     * uzavreni spojeni
     * @return unknown_type
     */
    public function close()
    {
        if(!is_resource($this->handle))
            return true;

        $this->closeInternal();
        $this->handle   =false;
        return null;
    }

    /**
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
}

/**
 * TODO mozna pridelat pocitadlo
 * @author Milan
 *
 */

class CUrlMulti extends CUrl
{
    //private $counter    =0;

    public function __construct()
    {
        $this->handle    =curl_multi_init();
    }

    public function add(CUrl $object)
    {
        return curl_multi_add_handle($this->handle, $object->getHandle());
    }

    public function remove(CUrl $object)
    {
        return curl_multi_remove_handle($this->handle, $object->getHandle());
    }

    public function getContent(CUrl $object)
    {
        return curl_multi_getcontent( $object->getHandle() );
    }

    /**
     *
     * @param float
     * @return int
     */
    public function select($timeout = 1.0)
    {
        return curl_multi_select($this->handle, $timeout);
    }

    /**
     *
     * @param $message
     * @return string
     */
    public function infoRead(&$message = NULL)
    {
        return curl_multi_info_read($this->handle, $message);
    }

    public function exec($active=null)
    {
        return curl_multi_exec($this->handle, $active);
    }

    /**
     * nefunguje jak ma...
     * (non-PHPdoc)
     * @see CUrl#getResult()
     */
    public function getResult($active = null)
    {
        do {
            $mrc = curl_multi_exec($this->handle, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->handle) != -1) {
                do {
                    $mrc = curl_multi_exec($this->handle, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
    }

    protected function closeInternal()
    {
        return curl_multi_close($this->handle);
    }
}


class CUrlManager
{
    protected $regProfile   =array();

    protected $defProfile   ='fopen';

    /**
     *
     * @var CUrlMulti
     */
    protected $multi    =false;

    private $curl   =array();

    public function __construct($profile=null)
    {
        if(is_string($profile))
            $this->defProfile   =$profile;
    }

    /**
     * registrace profilu pro stahovani pres CUrl
     * @param $name
     * @param $callBack
     * @return void
     */
    public function registerProgile($name, $callBack)
    {
        if(isset($this->regProfile[$name]))
            throw new RuntimeException('Use another name because this "'. $name .'" exists.');

        $this->regProfile[$name]    =$callBack;
    }

    /**
     *
     * @param $name
     * @param $url
     * @return CUrl
     */
    public function __set($name, $url)
    {
        $this->$name    =new CUrl($url);
        $this->curl[]   =$name;
    }

    /**
     * @param string $arg1, $arg2, $arg3...
     * @return bool
     */
    public function addMulti()
    {
        $this->multi  =new CUrlMulti();

        $num    =func_get_args();

        if(is_array($num[0]))
            $num =$num[0];

        foreach($num as $val)
        {
            list($val, $profile) = explode('|', $val . '|' . $this->defProfile);
            if(!isset($this->$val))
                throw new IOException('This property $'. $val .' does`t exists.');

            $this->$val->setOptions( call_user_func( $this->makeProfile($profile )) );
            $this->multi->add($this->$val);
        }

        return true;
    }

    /**
     * vytahne z registru profil a vrati jej
     */
    protected function makeProfile($profile)
    {
        if(isset($this->regProfile[$profile]))
            return $this->regProfile[$profile];

        if(!method_exists(__CLASS__, $profile))
            throw new BadMethodCallException(__CLASS__ .'::'. $profile .'()');

        return array(__CLASS__, $profile);
    }

    /**
     * odstrani vsechna spojeni
     * @return void
     */
    public function remove()
    {
        foreach($this->curl as $val)
            $this->multi->remove($this->$val);
    }

    /**
     * @param string ... $arg1, $arg2, $arg3
     * @return bool
     */
    public function removeSelect()
    {
        $num    =func_get_args();
        foreach($num as $val)
        {
            if(!isset($this->$name))
                throw new RuntimeException('This property $'. $name .' does`t exists.');
            $this->multi->remove($this->$name);
        }

        return true;
    }

    public function getResult()
    {
        if($this->multi === false)
        {
            if(empty($this->curl))
               throw new RuntimeException('Lets Initialize any url as $CUrlManagerObject->anyName=\'http://example.com\'.');

            $this->addMulti($this->curl);
        }

        return $this->multi->getResult();
    }

    public function __destruct()
    {
        $this->remove();
        $this->multi->close();
    }

    //---------------PROFILE----------------------------------------------------
    public static function fopen()
    {
        return array(CURLOPT_HEADER=> 0, CURLOPT_RETURNTRANSFER=> 1, CURLOPT_FOLLOWLOCATION=> 1);
    }

    public static function header()
    {
        return array(CURLOPT_HEADER=> 0);
    }
}


class CUrlException extends \RuntimeException
{}
