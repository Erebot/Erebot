<?php

class TvRetriever
{
    const TARGET_URL    = 'http://television.telerama.fr/tele/grille.php';

    protected $ID_mappings;
    static protected $instance;

    protected function __construct()
    {
        $this->updateIds();
    }

    public static function getInstance()
    {
        if (self::$instance === NULL) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function updateIds()
    {
        $source     = file_get_contents(self::TARGET_URL, 0);
        $internal   = libxml_use_internal_errors(TRUE);

        $domdoc     = new DOMDocument();
        $domdoc->validateOnParse        = FALSE;
        $domdoc->preserveWhitespace     = FALSE;
        $domdoc->strictErrorChecking    = FALSE;
        $domdoc->substituteEntities     = FALSE;
        $domdoc->resolveExternals       = FALSE;
        $domdoc->recover                = TRUE;
        $domdoc->loadHTML($source);

        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        $select     = $domdoc->getElementsByTagName('select')->item(0);
        if ($select === NULL)
            return;

        $this->ID_mappings = array();
        $select->normalize();

        foreach ($select->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE ||
                $child->nodeName != 'option')
                continue;

            $valueNode  = $child->attributes->getNamedItem('value');
            if ($valueNode === NULL)
                continue;

            $value      = $valueNode->nodeValue;
            if (!ctype_digit($value))
                continue;

            $value      = (int) $value;
            $channel    = strtolower($child->nodeValue);
            $this->ID_mappings[$channel] = $value;
            $this->ID_mappings[str_replace(' ', '', $channel)] = $value;
        }
    }

    public function getSupportedChannels()
    {
        return array_keys($this->ID_mappings);
    }

    public function getIdFromChannel($channel)
    {
        $channel = strtolower(trim($channel));
        if (!isset($this->ID_mappings[$channel]))
            return NULL;
        return $this->ID_mappings[$channel];
    }

    public function getChannelsData($timestamp, $ids)
    {
        if (!is_array($ids))
            $ids = array($ids);

        $post_data = 'xajax=chargerProgramme'.
                    '&xajaxargs[]='.date('Y-m-d H:i:s', $timestamp).
                    '&xajaxargs[]='.implode(',',
                        array_map('rawurlencode', $ids)).
                    '&xajaxr='.time();

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $post_data,
            )
        );

        $context    = stream_context_create($options);
        $source     = file_get_contents(self::TARGET_URL, 0, $context);
        $sxml       = simplexml_load_string($source);
        foreach ($sxml->cmd as $cmd) {
            if (!isset($cmd['n']) || (string) $cmd['n'] != 'as')
                continue;

            $source = (string) $cmd;
            break;
        }

        $internal   = libxml_use_internal_errors(TRUE);

        $domdoc     = new DOMDocument();
        $domdoc->validateOnParse        = FALSE;
        $domdoc->preserveWhitespace     = FALSE;
        $domdoc->strictErrorChecking    = FALSE;
        $domdoc->substituteEntities     = FALSE;
        $domdoc->resolveExternals       = FALSE;
        $domdoc->recover                = TRUE;
        $source =   '<html><head><meta http-equiv="Content-Type" '.
                    'content="text/html; charset=utf-8"/></head>'.
                    '<body>'.$source.'</body></html>';
        $domdoc->loadHTML($source);

        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        $divs   = $domdoc->getElementsByTagName('div');
        $infos  = array();
        foreach ($divs as $div) {
            $idNode = $div->attributes->getNamedItem('id');
            if ($idNode === NULL ||
                strncmp($idNode->nodeValue, 'data_', 5))
                continue;

            $json = trim($div->nodeValue);
            $data = json_decode($json, TRUE);
            if ($data === FALSE)
                continue;

            $channel = $data['Chaine_Nom'];
            if (strtotime($data['Date_Debut']) <= $timestamp &&
                $timestamp <= strtotime($data['Date_Fin']))
                $infos[$channel] = $data;
        }
        return $infos;
    }
}

?>
